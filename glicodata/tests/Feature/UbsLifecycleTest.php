<?php

namespace Tests\Feature;

use App\Models\AdministratorModel;
use App\Models\AuditEventModel;
use App\Models\DistrictModel;
use App\Models\UbsModel;
use App\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UbsLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'StrongPassword!123';

    public function test_fresh_database_and_default_seeder_do_not_create_ubs(): void
    {
        $this->seed();

        $this->assertSame(5, DistrictModel::query()->count());
        $this->assertSame(0, UbsModel::query()->count());
    }

    public function test_public_registration_creates_a_hashed_pending_ubs_and_safe_audit_event(): void
    {
        $this->post('/cadastro/ubs', [
            'cnes' => '0123456',
            'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
        ])->assertRedirect(route('ubs.login'))
            ->assertSessionHas('status');

        $ubs = UbsModel::query()->where('cnes', '0123456')->firstOrFail();
        $event = AuditEventModel::query()->where('subject_id', $ubs->id)->firstOrFail();

        $this->assertFalse($ubs->is_active);
        $this->assertTrue(Hash::check(self::PASSWORD, $ubs->password));
        $this->assertNotSame(self::PASSWORD, $ubs->password);
        $this->assertSame($ubs->id, $event->actor_ubs_id);
        $this->assertSame($ubs->id, $event->owner_ubs_id);
        $this->assertSame('register', $event->action);
        $this->assertArrayNotHasKey('password', $event->after_payload);

        $this->from('/login/ubs')->post('/login', [
            'account_type' => 'ubs',
            'identifier' => '0123456',
            'password' => self::PASSWORD,
        ])->assertRedirect('/login/ubs')->assertSessionHasErrors('identifier');
    }

    public function test_public_registration_validates_cnes_uniqueness_and_password_confirmation(): void
    {
        UbsModel::factory()->create(['cnes' => '1234567']);

        $this->post('/cadastro/ubs', [
            'cnes' => '1234567',
            'password' => self::PASSWORD,
            'password_confirmation' => 'DifferentPassword!123',
        ])->assertSessionHasErrors(['cnes', 'password']);

        $this->post('/cadastro/ubs', [
            'cnes' => '123',
            'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
        ])->assertSessionHasErrors('cnes');
    }

    public function test_public_registration_is_rate_limited_by_ip(): void
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $cnes = sprintf('6%06d', $attempt);

            $this->post('/cadastro/ubs', [
                'cnes' => $cnes,
                'password' => self::PASSWORD,
                'password_confirmation' => self::PASSWORD,
            ])->assertRedirect(route('ubs.login'));
        }

        $this->post('/cadastro/ubs', [
            'cnes' => '6999999',
            'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
        ])->assertTooManyRequests();
    }

    public function test_administrator_can_create_and_activate_a_ubs_through_the_api(): void
    {
        $administrator = AdministratorModel::factory()->create();
        Sanctum::actingAs($administrator, ['admin']);

        $response = $this->postJson('/api/ubs', [
            'cnes' => '2345678',
            'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
        ])->assertCreated()
            ->assertJsonPath('cnes', '2345678')
            ->assertJsonPath('is_active', false)
            ->assertJsonMissingPath('password');

        $ubsId = (string) $response->json('id');

        $this->assertDatabaseHas('audit_events', [
            'actor_administrator_id' => $administrator->id,
            'owner_ubs_id' => $ubsId,
            'action' => 'create',
        ]);

        $this->patchJson("/api/ubs/{$ubsId}", [
            'is_active' => true,
        ])->assertOk()->assertJsonPath('is_active', true);

        auth('sanctum')->forgetUser();

        $this->postJson('/api/auth/login', [
            'account_type' => 'ubs',
            'identifier' => '2345678',
            'password' => self::PASSWORD,
            'device_name' => 'recepcao',
        ])->assertOk()->assertJsonPath('identity.cnes', '2345678');
    }

    public function test_only_administrators_can_create_ubs_through_the_api(): void
    {
        $payload = [
            'cnes' => '3345678',
            'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
        ];

        $this->postJson('/api/ubs', $payload)->assertUnauthorized();

        Sanctum::actingAs(UbsModel::factory()->create(), ['ubs']);
        $this->postJson('/api/ubs', $payload)->assertForbidden();
    }

    public function test_ubs_can_update_only_its_own_profile_and_not_identity_fields(): void
    {
        $ubs = UbsModel::factory()->create();
        $otherUbs = UbsModel::factory()->create();
        Sanctum::actingAs($ubs, ['ubs']);

        $this->patchJson("/api/ubs/{$ubs->id}", [
            'name' => 'UBS Perfil Atualizado',
            'email' => null,
        ])->assertOk()->assertJsonPath('name', 'UBS Perfil Atualizado');

        $this->patchJson("/api/ubs/{$ubs->id}", [
            'cnes' => '3456789',
            'is_active' => false,
        ])->assertUnprocessable()->assertJsonValidationErrors(['cnes', 'is_active']);

        $this->patchJson("/api/ubs/{$otherUbs->id}", [
            'name' => 'Tentativa indevida',
        ])->assertForbidden();
    }

    public function test_deactivation_revokes_ubs_tokens_and_database_sessions(): void
    {
        $ubs = UbsModel::factory()->create();
        $ubs->createToken('tablet', ['ubs'], now()->addDay());
        DB::table('sessions')->insert([
            'id' => 'ubs-session',
            'user_id' => $ubs->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => 'test',
            'last_activity' => now()->timestamp,
        ]);
        $administrator = AdministratorModel::factory()->create();
        Sanctum::actingAs($administrator, ['admin']);

        $this->patchJson("/api/ubs/{$ubs->id}", [
            'is_active' => false,
        ])->assertOk();

        $this->assertSame(0, $ubs->tokens()->count());
        $this->assertDatabaseMissing('sessions', ['user_id' => $ubs->id]);
    }

    public function test_web_management_and_self_profile_pages_are_guarded_by_account_type(): void
    {
        $ubs = UbsModel::factory()->create();
        $administrator = AdministratorModel::factory()->create();

        $this->get('/cadastro/ubs')->assertOk()->assertSee('Cadastrar UBS');

        $this->actingAs($administrator, 'admin')
            ->get('/admin/ubs')
            ->assertOk()
            ->assertSee($ubs->cnes);

        $this->get("/admin/ubs/{$ubs->id}/editar")
            ->assertOk()
            ->assertSee($ubs->cnes);

        auth('admin')->logout();

        $this->actingAs($ubs, 'ubs')
            ->get('/ubs/conta/perfil')
            ->assertOk()
            ->assertSee($ubs->cnes);

        $this->get("/admin/ubs/{$ubs->id}/editar")->assertRedirect('/login/admin');
    }

    public function test_administrator_approves_and_ubs_updates_its_profile_through_blade(): void
    {
        $administrator = AdministratorModel::factory()->create();

        $this->actingAs($administrator, 'admin')->post('/cadastro/ubs', [
            'cnes' => '7788990',
            'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
        ])->assertRedirect(route('admin.ubs.index', ['status' => 'pending']));

        $ubs = UbsModel::query()->where('cnes', '7788990')->firstOrFail();

        $this->assertDatabaseHas('audit_events', [
            'actor_administrator_id' => $administrator->id,
            'owner_ubs_id' => $ubs->id,
            'action' => 'create',
        ]);

        $this
            ->put("/admin/ubs/{$ubs->id}", [
                'cnes' => $ubs->cnes,
                'is_active' => true,
                'name' => 'UBS Aprovada',
            ])->assertRedirect(route('admin.ubs.edit', $ubs));

        $this->assertDatabaseHas('ubs', [
            'id' => $ubs->id,
            'name' => 'UBS Aprovada',
            'is_active' => true,
        ]);

        auth('admin')->logout();

        $this->actingAs($ubs->refresh(), 'ubs')
            ->put('/ubs/conta/perfil', [
                'bairro_ref' => 'Centro',
                'phone' => '(42) 3901-1700',
            ])->assertRedirect(route('ubs.profile.edit'));

        $this->assertDatabaseHas('ubs', [
            'id' => $ubs->id,
            'bairro_ref' => 'Centro',
            'phone' => '(42) 3901-1700',
        ]);
    }

    public function test_cleanup_migration_removes_seeded_ubs_and_all_dependencies(): void
    {
        $seededUbs = UbsModel::factory()->create([
            'id' => '20000000-0000-4000-8000-000000000001',
            'cnes' => '4567890',
        ]);
        $retainedUbs = UbsModel::factory()->create(['cnes' => '5678901']);
        $user = UserModel::factory()->create(['ubs_id' => $seededUbs->id]);
        $patientId = (string) Str::uuid();
        $assessmentId = (string) Str::uuid();
        $now = now();

        DB::table('patients')->insert([
            'id' => $patientId,
            'ubs_id' => $seededUbs->id,
            'name' => 'Paciente de remoção',
            'sex' => true,
            'cpf' => '529.982.247-25',
            'birth' => '1990-01-01',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('assessments')->insert([
            'id' => $assessmentId,
            'patient_id' => $patientId,
            'user_id' => $user->id,
            'ubs_id' => $seededUbs->id,
            'symptoms' => 'Registro destinado ao teste de limpeza.',
            'answers' => json_encode(['test' => true], JSON_THROW_ON_ERROR),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('risks')->insert([
            'id' => (string) Str::uuid(),
            'assessment_id' => $assessmentId,
            'percentage' => 10,
            'classification' => 'low',
            'score' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('reports')->insert([
            'id' => (string) Str::uuid(),
            'assessment_id' => $assessmentId,
            'title' => 'Relatório de remoção',
            'description' => 'Registro destinado ao teste de limpeza.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('audit_events')->insert([
            'id' => (string) Str::uuid(),
            'actor_ubs_id' => $seededUbs->id,
            'actor_administrator_id' => null,
            'owner_ubs_id' => $seededUbs->id,
            'actor_name' => $seededUbs->name,
            'actor_email' => $seededUbs->email,
            'subject_type' => 'patients',
            'subject_id' => $patientId,
            'action' => 'create',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $seededUbs->createToken('seeded', ['ubs'], now()->addDay());
        DB::table('sessions')->insert([
            'id' => 'seeded-session',
            'user_id' => $seededUbs->id,
            'payload' => 'test',
            'last_activity' => now()->timestamp,
        ]);

        $migration = require database_path('migrations/2026_08_18_181500_remove_automatically_seeded_ubs.php');
        $migration->up();

        $this->assertDatabaseMissing('ubs', ['id' => $seededUbs->id]);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('patients', ['id' => $patientId]);
        $this->assertDatabaseMissing('assessments', ['id' => $assessmentId]);
        $this->assertDatabaseMissing('audit_events', ['owner_ubs_id' => $seededUbs->id]);
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $seededUbs->id]);
        $this->assertDatabaseMissing('sessions', ['user_id' => $seededUbs->id]);
        $this->assertDatabaseHas('ubs', ['id' => $retainedUbs->id, 'is_active' => true]);
        $this->assertSame(5, DistrictModel::query()->whereIn('id', [
            '10000000-0000-4000-8000-000000000001',
            '10000000-0000-4000-8000-000000000002',
            '10000000-0000-4000-8000-000000000003',
            '10000000-0000-4000-8000-000000000004',
            '10000000-0000-4000-8000-000000000005',
        ])->count());
    }
}
