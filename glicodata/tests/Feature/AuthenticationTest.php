<?php

namespace Tests\Feature;

use App\Enums\AccountType;
use App\Models\AdministratorModel;
use App\Models\UbsModel;
use App\Services\AuthServices\AuthenticationService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'StrongPassword!123';

    public function test_ubs_can_receive_a_hashed_sanctum_token_valid_for_24_hours(): void
    {
        CarbonImmutable::setTestNow('2026-08-18 12:00:00');
        $ubs = UbsModel::factory()->create(['password' => self::PASSWORD]);

        $response = $this->postJson('/api/auth/login', [
            'account_type' => 'ubs',
            'identifier' => $ubs->cnes,
            'password' => self::PASSWORD,
            'device_name' => 'tablet-recepcao',
        ])->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('account_type', 'ubs')
            ->assertJsonPath('identity.id', $ubs->id)
            ->assertJsonMissingPath('identity.password');

        $plainTextToken = (string) $response->json('access_token');
        [, $secret] = explode('|', $plainTextToken, 2);
        $storedToken = DB::table('personal_access_tokens')->first();

        $this->assertNotSame($plainTextToken, $storedToken->token);
        $this->assertSame(hash('sha256', $secret), $storedToken->token);
        $this->assertSame('2026-08-19T12:00:00.000000Z', $response->json('expires_at'));
        $this->assertTrue(Hash::check(self::PASSWORD, $ubs->password));
        $this->assertNotSame(self::PASSWORD, $ubs->password);

        CarbonImmutable::setTestNow();
    }

    public function test_invalid_inactive_and_unknown_accounts_share_the_same_error(): void
    {
        $ubs = UbsModel::factory()->inactive()->create(['password' => self::PASSWORD]);

        foreach ([
            [$ubs->cnes, self::PASSWORD],
            [$ubs->cnes, 'WrongPassword!123'],
            ['9999999', self::PASSWORD],
        ] as [$identifier, $password]) {
            $this->postJson('/api/auth/login', [
                'account_type' => 'ubs',
                'identifier' => $identifier,
                'password' => $password,
                'device_name' => 'test',
            ])->assertUnprocessable()
                ->assertJsonValidationErrors(['identifier'])
                ->assertJsonPath('errors.identifier.0', 'As credenciais informadas são inválidas.');
        }
    }

    public function test_login_is_rate_limited(): void
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->postJson('/api/auth/login', [
                'account_type' => 'ubs',
                'identifier' => '9999999',
                'password' => 'WrongPassword!123',
                'device_name' => 'test',
            ])->assertUnprocessable();
        }

        $this->postJson('/api/auth/login', [
            'account_type' => 'ubs',
            'identifier' => '9999999',
            'password' => 'WrongPassword!123',
            'device_name' => 'test',
        ])->assertTooManyRequests();
    }

    public function test_only_the_twenty_most_recent_tokens_are_kept(): void
    {
        $ubs = UbsModel::factory()->create();
        $service = app(AuthenticationService::class);
        $firstTokenId = null;

        for ($index = 1; $index <= 21; $index++) {
            CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-08-18 12:00:00')->addSecond($index));
            $issued = $service->issueToken($ubs, AccountType::Ubs, "device-{$index}");
            $firstTokenId ??= $issued['token']->accessToken->id;
        }

        $this->assertSame(20, $ubs->tokens()->count());
        $this->assertFalse($ubs->tokens()->whereKey($firstTokenId)->exists());

        CarbonImmutable::setTestNow();
    }

    public function test_expired_token_is_rejected(): void
    {
        CarbonImmutable::setTestNow('2026-08-18 12:00:00');
        $ubs = UbsModel::factory()->create(['password' => self::PASSWORD]);
        $token = $this->loginToken('ubs', $ubs->cnes);

        CarbonImmutable::setTestNow('2026-08-19 12:00:01');

        $this->withToken($token)->getJson('/api/auth/me')->assertUnauthorized();

        CarbonImmutable::setTestNow();
    }

    public function test_password_change_revokes_every_token(): void
    {
        $ubs = UbsModel::factory()->create(['password' => self::PASSWORD]);
        $firstToken = $this->loginToken('ubs', $ubs->cnes, 'first');
        $this->loginToken('ubs', $ubs->cnes, 'second');

        $this->withToken($firstToken)->putJson('/api/auth/password', [
            'current_password' => self::PASSWORD,
            'password' => 'AnotherStrong!456',
            'password_confirmation' => 'AnotherStrong!456',
        ])->assertNoContent();

        $this->assertSame(0, $ubs->tokens()->count());
        $this->assertTrue(Hash::check('AnotherStrong!456', $ubs->refresh()->password));
        auth('sanctum')->forgetUser();
        $this->withToken($firstToken)->getJson('/api/auth/me')->assertUnauthorized();
    }

    public function test_administrator_can_manage_ubs_but_cannot_access_clinical_routes(): void
    {
        UbsModel::factory()->create();
        $administrator = AdministratorModel::factory()->create(['password' => self::PASSWORD]);
        $token = $this->loginToken('admin', $administrator->admin_code);

        $this->withToken($token)->getJson('/api/ubs')->assertOk();
        $this->withToken($token)->getJson('/api/patients')->assertForbidden();
    }

    public function test_administrator_update_is_audited_as_an_administrator(): void
    {
        $ubs = UbsModel::factory()->create();
        $administrator = AdministratorModel::factory()->create(['password' => self::PASSWORD]);
        $token = $this->loginToken('admin', $administrator->admin_code);

        $this->withToken($token)->patchJson("/api/ubs/{$ubs->id}", [
            'is_active' => false,
        ])->assertOk()->assertJsonPath('is_active', false);

        $this->assertDatabaseHas('audit_events', [
            'actor_ubs_id' => null,
            'actor_administrator_id' => $administrator->id,
            'owner_ubs_id' => $ubs->id,
            'action' => 'update',
        ]);
    }

    public function test_ubs_cannot_read_another_ubs_record(): void
    {
        $authenticatedUbs = UbsModel::factory()->create(['password' => self::PASSWORD]);
        $otherUbs = UbsModel::factory()->create();
        $token = $this->loginToken('ubs', $authenticatedUbs->cnes);

        $this->withToken($token)->getJson("/api/ubs/{$otherUbs->id}")->assertForbidden();
    }

    private function loginToken(string $accountType, string $identifier, string $deviceName = 'test-device'): string
    {
        return (string) $this->postJson('/api/auth/login', [
            'account_type' => $accountType,
            'identifier' => $identifier,
            'password' => self::PASSWORD,
            'device_name' => $deviceName,
        ])->assertOk()->json('access_token');
    }
}
