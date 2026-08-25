<?php

namespace Tests\Feature;

use App\Models\DistrictModel;
use Illuminate\Support\Str;
use Tests\TestCase;

class PostgresqlHomologationSmokeTest extends TestCase
{
    public function test_real_administrator_can_create_review_and_activate_a_ubs(): void
    {
        $email = env('HOMOLOG_ADMIN_EMAIL');
        $password = env('HOMOLOG_ADMIN_PASSWORD');

        if (! is_string($email) || ! is_string($password)) {
            $this->markTestSkipped('Defina HOMOLOG_ADMIN_EMAIL e HOMOLOG_ADMIN_PASSWORD para o smoke test PostgreSQL.');
        }

        $this->assertSame('pgsql', config('database.default'));
        $this->assertStringContainsString('homolog', (string) config('database.connections.pgsql.database'));

        $token = $this->postJson('/api/auth/login', [
            'account_type' => 'admin',
            'identifier' => $email,
            'password' => $password,
            'device_name' => 'homologacao-postgresql',
        ])->assertOk()->json('access_token');

        $cnes = (string) random_int(1000000, 9999999);
        $ubsPassword = Str::password(32);
        $ubsId = $this->withToken($token)->postJson('/api/ubs', [
            'cnes' => $cnes,
            'password' => $ubsPassword,
            'password_confirmation' => $ubsPassword,
        ])->assertCreated()
            ->assertJsonPath('is_active', false)
            ->json('id');

        $districtId = DistrictModel::query()->value('id');
        $emailUbs = 'ubs.homologacao.'.Str::lower(Str::random(8)).'@example.test';
        $this->withToken($token)->putJson("/api/ubs/{$ubsId}", [
            'district_id' => $districtId,
            'name' => 'UBS de Homologação Automatizada',
            'bairro_ref' => 'Ambiente isolado',
            'address' => 'Endereço de homologação',
            'phone' => '(42) 3901-1000',
            'email' => $emailUbs,
            'is_active' => true,
        ])->assertOk()
            ->assertJsonPath('cnes', $cnes)
            ->assertJsonPath('is_active', true);

        $this->assertDatabaseHas('ubs', [
            'id' => $ubsId,
            'cnes' => $cnes,
            'name' => 'UBS de Homologação Automatizada',
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('audit_events', [
            'actor_administrator_id' => $this->app['auth']->guard('sanctum')->user()->id,
            'owner_ubs_id' => $ubsId,
            'action' => 'update',
        ]);
    }
}
