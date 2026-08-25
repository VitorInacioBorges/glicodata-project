<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $this->minimizeAdministrators();
        $this->minimizePatients();
        $this->replaceUsersWithProfessionals();
        $this->createVersionedQuestionnaires();
        $this->updateAssessments();
        $this->minimizeReports();
        $this->minimizeAuditEvents();
    }

    private function minimizeAdministrators(): void
    {
        Schema::table('administrators', function (Blueprint $table): void {
            $table->string('admin_code', 40)->nullable()->after('id');
        });

        DB::table('administrators')->orderBy('id')->eachById(
            static fn (object $administrator): int => DB::table('administrators')
                ->where('id', $administrator->id)
                ->update(['admin_code' => 'ADMIN-'.Str::upper(substr(str_replace('-', '', (string) $administrator->id), 0, 8))]),
            100,
            'id',
        );

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE administrators DROP CONSTRAINT IF EXISTS administrators_email_lowercase_check');
        }

        Schema::table('administrators', function (Blueprint $table): void {
            $table->string('admin_code', 40)->nullable(false)->change();
            $table->unique('admin_code');
            $table->dropUnique(['email']);
            $table->dropColumn(['name', 'email']);
        });
    }

    private function minimizePatients(): void
    {
        Schema::table('patients', function (Blueprint $table): void {
            $table->string('first_name', 80)->nullable()->after('ubs_id');
            $table->string('neighborhood', 120)->nullable()->after('sex');
            $table->string('neighborhood_normalized', 120)->nullable()->after('neighborhood');
            $table->string('street_name', 160)->nullable()->after('neighborhood_normalized');
        });

        DB::table('patients')->orderBy('id')->eachById(
            static function (object $patient): int {
                $firstName = Str::of((string) $patient->name)->trim()->explode(' ')->filter()->first() ?: 'Paciente';

                return DB::table('patients')->where('id', $patient->id)->update([
                    'first_name' => $firstName,
                    'neighborhood' => 'Não informado',
                    'neighborhood_normalized' => 'nao informado',
                    'street_name' => null,
                ]);
            },
            100,
            'id',
        );

        Schema::table('patients', function (Blueprint $table): void {
            $table->string('first_name', 80)->nullable(false)->change();
            $table->string('neighborhood', 120)->nullable(false)->change();
            $table->string('neighborhood_normalized', 120)->nullable(false)->change();
            $table->dropUnique(['cpf']);
            $table->dropColumn(['name', 'cpf', 'address', 'phone', 'birth']);
            $table->index(['ubs_id', 'neighborhood_normalized'], 'patients_ubs_neighborhood_index');
        });
    }

    private function replaceUsersWithProfessionals(): void
    {
        Schema::table('assessments', function (Blueprint $table): void {
            $table->dropForeign(['user_id', 'ubs_id']);
            $table->dropIndex(['user_id']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_email_lowercase_check');
            DB::statement('DROP INDEX IF EXISTS users_email_lower_unique');
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->string('first_name', 80)->nullable()->after('ubs_id');
            $table->string('specialty', 120)->nullable()->after('first_name');
            $table->boolean('is_active')->default(true)->after('specialty');
        });

        DB::table('users')->orderBy('id')->eachById(
            static function (object $user): int {
                $firstName = Str::of((string) $user->name)->trim()->explode(' ')->filter()->first() ?: 'Profissional';

                return DB::table('users')->where('id', $user->id)->update([
                    'first_name' => $firstName,
                    'specialty' => 'Não informada',
                ]);
            },
            100,
            'id',
        );

        Schema::table('users', function (Blueprint $table): void {
            $table->string('first_name', 80)->nullable(false)->change();
            $table->string('specialty', 120)->nullable(false)->change();
            $table->dropUnique(['cpf']);
            if (DB::getDriverName() !== 'pgsql') {
                $table->dropUnique(['email']);
            }
            $table->dropColumn([
                'name',
                'birth',
                'sex',
                'cpf',
                'address',
                'phone',
                'email',
                'email_verified_at',
                'password',
                'role',
                'remember_token',
            ]);
            $table->index(['ubs_id', 'is_active', 'first_name'], 'professionals_ubs_active_name_index');
        });

        Schema::rename('users', 'professionals');
    }

    private function createVersionedQuestionnaires(): void
    {
        Schema::create('questionnaires', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code', 80)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
        });

        Schema::create('questionnaire_versions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('questionnaire_id')
                ->constrained('questionnaires')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->unsignedInteger('version');
            $table->enum('status', ['draft', 'published', 'retired'])->default('draft');
            $table->jsonb('schema');
            $table->jsonb('risk_rules');
            $table->timestampTz('published_at')->nullable();
            $table->timestampsTz();

            $table->unique(['questionnaire_id', 'version']);
            $table->index(['status', 'published_at']);
        });
    }

    private function updateAssessments(): void
    {
        Schema::table('assessments', function (Blueprint $table): void {
            $table->renameColumn('user_id', 'professional_id');
        });

        Schema::table('assessments', function (Blueprint $table): void {
            $table->foreignUuid('questionnaire_version_id')
                ->nullable()
                ->after('ubs_id')
                ->constrained('questionnaire_versions')
                ->restrictOnUpdate()
                ->restrictOnDelete();
            $table->enum('status', ['draft', 'completed'])->default('draft')->after('answers');
            $table->timestampTz('started_at')->nullable()->after('status');
            $table->timestampTz('completed_at')->nullable()->after('started_at');
            $table->dropColumn('symptoms');
            $table->index('professional_id');
            $table->index(['ubs_id', 'status', 'created_at']);
            $table->foreign(['professional_id', 'ubs_id'])
                ->references(['id', 'ubs_id'])
                ->on('professionals')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    private function minimizeReports(): void
    {
        Schema::table('reports', function (Blueprint $table): void {
            $table->dropColumn(['title', 'comment']);
        });
    }

    private function minimizeAuditEvents(): void
    {
        Schema::table('audit_events', function (Blueprint $table): void {
            $table->jsonb('changed_fields')->nullable()->after('action');
            $table->dropColumn(['actor_name', 'actor_email', 'before_payload', 'after_payload']);
        });
    }

    public function down(): void
    {
        throw new RuntimeException('A migração de minimização de dados é irreversível por privacidade.');
    }
};
