<?php

namespace Tests\Feature;

use App\Models\AdministratorModel;
use App\Models\UbsModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CredentialCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_is_created_without_a_plaintext_password(): void
    {
        $this->artisan('glicodata:admin-create', [
            'admin_code' => 'ADMIN_TESTE',
        ])
            ->expectsQuestion('Senha', 'StrongPassword!123')
            ->expectsQuestion('Confirme a senha', 'StrongPassword!123')
            ->assertSuccessful();

        $administrator = AdministratorModel::query()->sole();
        $this->assertSame('ADMIN_TESTE', $administrator->admin_code);
        $this->assertNotSame('StrongPassword!123', $administrator->password);
        $this->assertTrue(Hash::check('StrongPassword!123', $administrator->password));
    }

    public function test_ubs_password_command_revokes_existing_tokens(): void
    {
        $ubs = UbsModel::factory()->create();
        $ubs->createToken('existing-device', ['ubs'], now()->addDay());

        $this->artisan('glicodata:ubs-password', ['cnes' => $ubs->cnes])
            ->expectsQuestion('Nova senha', 'AnotherStrong!456')
            ->expectsQuestion('Confirme a nova senha', 'AnotherStrong!456')
            ->assertSuccessful();

        $this->assertTrue(Hash::check('AnotherStrong!456', $ubs->refresh()->password));
        $this->assertSame(0, $ubs->tokens()->count());
    }
}
