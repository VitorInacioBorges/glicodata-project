<?php

namespace App\Services\UbsServices;

use App\Models\UbsModel;
use App\Services\AuditEventServices\AuditEventService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Throwable;

class KeycloakUbsAuthService
{
    public function __construct(
        protected AuditEventService $auditService,
    ) {}

    public function findUbsFromBearerToken(?string $token): ?UbsModel
    {
        if ($token === null || trim($token) === '' || ! $this->hasKeycloakConfig()) {
            return null;
        }

        try {
            $response = Http::acceptJson()
                ->withToken($token)
                ->timeout(5)
                ->get($this->userInfoEndpoint());
        } catch (Throwable) {
            return null;
        }

        if (! $response->ok()) {
            return null;
        }

        return $this->findActiveUbs(
            $this->stringOrNull($response->json('sub')),
            $this->stringOrNull($response->json('email')),
            $token,
        );
    }

    public function findUbsFromSocialiteUser(SocialiteUser $user): ?UbsModel
    {
        return $this->findActiveUbs(
            $user->getId(),
            $user->getEmail(),
            is_string($user->token ?? null) ? $user->token : null,
        );
    }

    private function findActiveUbs(?string $keycloakId, ?string $email, ?string $token): ?UbsModel
    {
        if ($keycloakId === null && $email === null) {
            return null;
        }

        $email = $email !== null ? strtolower($email) : null;
        $ubs = UbsModel::query()
            ->where('is_active', true)
            ->where(function ($query) use ($keycloakId, $email): void {
                if ($keycloakId !== null) {
                    $query->where('keycloak_id', $keycloakId);
                }

                if ($email !== null) {
                    $method = $keycloakId === null ? 'where' : 'orWhere';
                    $query->{$method}(function ($emailQuery) use ($email): void {
                        $emailQuery
                            ->whereNull('keycloak_id')
                            ->whereRaw('LOWER(email) = ?', [$email]);
                    });
                }
            })
            ->first();

        if ($ubs === null) {
            return null;
        }

        if ($keycloakId !== null && $ubs->keycloak_id === null) {
            $ubs = DB::transaction(function () use ($ubs, $keycloakId): ?UbsModel {
                $lockedUbs = UbsModel::query()
                    ->whereKey($ubs->id)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->first();

                if ($lockedUbs === null) {
                    return null;
                }

                if ($lockedUbs->keycloak_id !== null) {
                    return hash_equals((string) $lockedUbs->keycloak_id, $keycloakId)
                        ? $lockedUbs
                        : null;
                }

                $before = $lockedUbs->toArray();
                $lockedUbs->keycloak_id = $keycloakId;
                $lockedUbs->save();
                $lockedUbs = $lockedUbs->refresh();

                $this->auditService->record(
                    'link_keycloak',
                    $lockedUbs,
                    (string) $lockedUbs->id,
                    $before,
                    $lockedUbs->toArray(),
                    $lockedUbs,
                );

                return $lockedUbs;
            });
        }

        if ($ubs === null) {
            return null;
        }

        return $ubs->setAuditAdmin($this->hasClientRole($token, 'audit-admin'));
    }

    private function userInfoEndpoint(): string
    {
        $baseUrl = rtrim((string) config('services.keycloak.base_url'), '/');
        $realm = trim((string) config('services.keycloak.realms'), '/');

        return "{$baseUrl}/realms/{$realm}/protocol/openid-connect/userinfo";
    }

    private function hasKeycloakConfig(): bool
    {
        return trim((string) config('services.keycloak.base_url')) !== ''
            && trim((string) config('services.keycloak.realms')) !== '';
    }

    private function stringOrNull(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    private function hasClientRole(?string $token, string $role): bool
    {
        if ($token === null) {
            return false;
        }

        $segments = explode('.', $token);

        if (count($segments) !== 3) {
            return false;
        }

        $payload = base64_decode(strtr($segments[1], '-_', '+/'), true);
        $claims = $payload !== false ? json_decode($payload, true) : null;
        $clientId = (string) config('services.keycloak.client_id');
        $roles = is_array($claims) ? ($claims['resource_access'][$clientId]['roles'] ?? []) : [];

        return is_array($roles) && in_array($role, $roles, true);
    }
}
