<?php

namespace App\Services\UbsServices;

use App\Models\UbsModel;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Throwable;

class KeycloakUbsAuthService
{
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
        );
    }

    public function findUbsFromSocialiteUser(SocialiteUser $user): ?UbsModel
    {
        return $this->findActiveUbs($user->getId(), $user->getEmail());
    }

    private function findActiveUbs(?string $keycloakId, ?string $email): ?UbsModel
    {
        if ($keycloakId === null && $email === null) {
            return null;
        }

        return UbsModel::query()
            ->where('is_active', true)
            ->where(function ($query) use ($keycloakId, $email): void {
                if ($keycloakId !== null) {
                    $query->where('keycloak_id', $keycloakId);
                }

                if ($email !== null) {
                    $method = $keycloakId === null ? 'where' : 'orWhere';
                    $query->{$method}('email', $email);
                }
            })
            ->first();
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
}
