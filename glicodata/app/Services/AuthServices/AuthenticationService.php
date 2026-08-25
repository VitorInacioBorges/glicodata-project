<?php

namespace App\Services\AuthServices;

use App\Enums\AccountType;
use App\Models\AdministratorModel;
use App\Models\UbsModel;
use App\Models\UserModel;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\NewAccessToken;

class AuthenticationService
{
    public const TOKEN_LIMIT = 20;

    public const TOKEN_EXPIRATION_HOURS = 24;

    private static ?string $dummyPasswordHash = null;

    public function authenticate(
        AccountType $accountType,
        string $identifier,
        #[\SensitiveParameter] string $password,
    ): UbsModel|UserModel|AdministratorModel|null {
        $account = $this->findAccount($accountType, $identifier);
        $hash = $account?->password ?? $this->dummyPasswordHash();
        $passwordMatches = Hash::check($password, $hash);

        if (! $account instanceof UbsModel && ! $account instanceof UserModel && ! $account instanceof AdministratorModel) {
            return null;
        }

        if (! $passwordMatches || ! $account->is_active || ($account instanceof UserModel && ! $account->hasActiveAccountContext())) {
            return null;
        }

        if (Hash::needsRehash($account->password)) {
            $account->forceFill(['password' => Hash::make($password)])->saveQuietly();
        }

        return $account;
    }

    /**
     * @return array{token: NewAccessToken, expires_at: CarbonImmutable}
     */
    public function issueToken(
        UbsModel|UserModel|AdministratorModel $account,
        AccountType $accountType,
        string $deviceName,
    ): array {
        $now = CarbonImmutable::now();
        $expiresAt = $now->addHours(self::TOKEN_EXPIRATION_HOURS);

        $token = DB::transaction(function () use ($account, $accountType, $deviceName, $expiresAt, $now): NewAccessToken {
            $account->newQuery()->whereKey($account->getKey())->lockForUpdate()->firstOrFail();

            $account->tokens()
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', $now)
                ->delete();

            $token = $account->createToken(
                $deviceName,
                [$accountType->ability()],
                $expiresAt,
            );

            $excessTokenCount = max(0, $account->tokens()->count() - self::TOKEN_LIMIT);
            $obsoleteTokenIds = $account->tokens()
                ->orderBy('created_at')
                ->orderBy('id')
                ->limit($excessTokenCount)
                ->pluck('id');

            if ($obsoleteTokenIds->isNotEmpty()) {
                $account->tokens()->whereKey($obsoleteTokenIds)->delete();
            }

            return $token;
        });

        return ['token' => $token, 'expires_at' => $expiresAt];
    }

    public function replacePassword(
        UbsModel|UserModel|AdministratorModel $account,
        #[\SensitiveParameter] string $password,
    ): void {
        DB::transaction(function () use ($account, $password): void {
            $lockedAccount = $account->newQuery()
                ->whereKey($account->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $lockedAccount->forceFill(['password' => $password])->save();
            $lockedAccount->tokens()->delete();
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function identity(UbsModel|UserModel|AdministratorModel $account): array
    {
        return [
            'id' => (string) $account->id,
            'cnes' => $account instanceof UbsModel ? $account->cnes : null,
            'ubs_id' => $account instanceof UserModel ? $account->ubs_id : null,
            'name' => $account->name,
            'email' => $account->email,
            'is_active' => (bool) $account->is_active,
            'role' => $account instanceof UserModel ? $account->role->value : null,
            'council_type' => $account instanceof UserModel ? $account->council_type?->value : null,
            'council_number' => $account instanceof UserModel ? $account->council_number : null,
            'council_uf' => $account instanceof UserModel ? $account->council_uf : null,
            'specialty' => $account instanceof UserModel ? $account->specialty : null,
        ];
    }

    private function findAccount(AccountType $accountType, string $identifier): UbsModel|UserModel|AdministratorModel|null
    {
        $model = match ($accountType) {
            AccountType::Ubs => new UbsModel,
            AccountType::User => new UserModel,
            AccountType::Administrator => new AdministratorModel,
        };

        $identifier = trim($identifier);

        return $model->newQuery()
            ->when(
                $accountType === AccountType::Ubs,
                fn ($query) => $query->where('cnes', $identifier),
                fn ($query) => $query->whereRaw('LOWER(email) = ?', [Str::lower($identifier)]),
            )
            ->first();
    }

    private function dummyPasswordHash(): string
    {
        return self::$dummyPasswordHash ??= Hash::make(Str::random(64));
    }
}
