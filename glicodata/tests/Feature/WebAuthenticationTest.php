<?php

namespace Tests\Feature;

use App\Models\AdministratorModel;
use App\Models\UbsModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WebAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'StrongPassword!123';

    public function test_ubs_and_administrator_have_distinct_login_screens(): void
    {
        $this->get('/login/ubs')
            ->assertOk()
            ->assertSee('name="account_type" value="ubs"', false)
            ->assertSee('name="_token"', false);

        $this->get('/login/admin')
            ->assertOk()
            ->assertSee('name="account_type" value="admin"', false)
            ->assertSee('name="_token"', false);
    }

    public function test_ubs_can_login_with_a_secure_session(): void
    {
        $ubs = UbsModel::factory()->create(['password' => self::PASSWORD]);

        $this->post('/login', [
            'account_type' => 'ubs',
            'identifier' => $ubs->cnes,
            'password' => self::PASSWORD,
        ])->assertRedirect(route('ubs.lobby'));

        $this->assertAuthenticatedAs($ubs, 'ubs');
        $this->get('/ubs/lobby')->assertOk()->assertSee($ubs->name);
    }

    public function test_administrator_can_login_with_a_separate_guard(): void
    {
        $administrator = AdministratorModel::factory()->create(['password' => self::PASSWORD]);

        $this->post('/login', [
            'account_type' => 'admin',
            'identifier' => $administrator->email,
            'password' => self::PASSWORD,
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($administrator, 'admin');
        $this->assertGuest('ubs');
        $this->get('/admin')->assertOk()->assertSee($administrator->name);
    }

    public function test_inactive_ubs_cannot_login(): void
    {
        $ubs = UbsModel::factory()->inactive()->create(['password' => self::PASSWORD]);

        $this->from('/login/ubs')->post('/login', [
            'account_type' => 'ubs',
            'identifier' => $ubs->cnes,
            'password' => self::PASSWORD,
        ])->assertRedirect('/login/ubs')->assertSessionHasErrors('identifier');

        $this->assertGuest('ubs');
    }

    public function test_web_password_change_revokes_tokens_and_ends_the_session(): void
    {
        $ubs = UbsModel::factory()->create(['password' => self::PASSWORD]);
        $ubs->createToken('existing-device', ['ubs'], now()->addDay());

        $this->actingAs($ubs, 'ubs')->put('/ubs/conta/senha', [
            'current_password' => self::PASSWORD,
            'password' => 'AnotherStrong!456',
            'password_confirmation' => 'AnotherStrong!456',
        ])->assertRedirect(route('ubs.login'));

        $this->assertGuest('ubs');
        $this->assertSame(0, $ubs->tokens()->count());
        $this->assertTrue(Hash::check('AnotherStrong!456', $ubs->refresh()->password));
    }
}
