<?php

namespace App\Services\AuthServices;

use App\Enums\AccountType;
use App\Models\AdministratorModel;
use App\Models\UbsModel;
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
    ): UbsModel|AdministratorModel|null {
        $account = $this->findAccount($accountType, $identifier);
        $hash = $account?->password ?? $this->dummyPasswordHash();
        $passwordMatches = Hash::check($password, $hash);

        if ($account === null || ! $passwordMatches || ! $account->is_active) {
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
        UbsModel|AdministratorModel $account,
        AccountType $accountType,
        string $deviceName,
    ): array {
        $now = CarbonImmutable::now();
        $expiresAt = $now->addHours(self::TOKEN_EXPIRATION_HOURS);

        $token = DB::transaction(function () use ($account, $accountType, $deviceName, $expiresAt, $now): NewAccessToken {
            $account->newQuery()->whereKey($account->getKey())->lockForUpdate()->firstOrFail();
            $account->tokens()->whereNotNull('expires_at')->where('expires_at', '<=', $now)->delete();

            $token = $account->createToken($deviceName, [$accountType->ability()], $expiresAt);
            $excess = max(0, $account->tokens()->count() - self::TOKEN_LIMIT);
            $obsolete = $account->tokens()->orderBy('created_at')->orderBy('id')->limit($excess)->pluck('id');

            if ($obsolete->isNotEmpty()) {
                $account->tokens()->whereKey($obsolete)->delete();
            }

            return $token;
        });

        return ['token' => $token, 'expires_at' => $expiresAt];
    }

    public function replacePassword(
        UbsModel|AdministratorModel $account,
        #[\SensitiveParameter] string $password,
    ): void {
        DB::transaction(function () use ($account, $password): void {
            $locked = $account->newQuery()->whereKey($account->getKey())->lockForUpdate()->firstOrFail();
            $locked->forceFill(['password' => $password])->save();
            $locked->tokens()->delete();
        });
    }

    /** @return array<string, mixed> */
    public function identity(UbsModel|AdministratorModel $account): array
    {
        if ($account instanceof UbsModel) {
            return [
                'id' => (string) $account->id,
                'cnes' => $account->cnes,
                'name' => $account->name,
                'is_active' => (bool) $account->is_active,
            ];
        }

        return [
            'id' => (string) $account->id,
            'admin_code' => $account->admin_code,
            'is_active' => (bool) $account->is_active,
        ];
    }

    private function findAccount(AccountType $accountType, string $identifier): UbsModel|AdministratorModel|null
    {
        $identifier = trim($identifier);

        return match ($accountType) {
            AccountType::Ubs => UbsModel::query()->where('cnes', $identifier)->first(),
            AccountType::Administrator => AdministratorModel::query()
                ->whereRaw('UPPER(admin_code) = ?', [Str::upper($identifier)])
                ->first(),
        };
    }

    private function dummyPasswordHash(): string
    {
        return self::$dummyPasswordHash ??= Hash::make(Str::password(64));
    }
}
