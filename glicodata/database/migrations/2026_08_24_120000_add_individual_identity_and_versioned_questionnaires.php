<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_active')->default(true)->after('role');
            $table->string('council_type', 10)->nullable()->after('role');
            $table->string('council_number', 30)->nullable()->after('council_type');
            $table->char('council_uf', 2)->nullable()->after('council_number');
            $table->string('specialty')->nullable()->after('council_uf');
            $table->index(['ubs_id', 'role', 'is_active']);
            $table->unique(
                ['council_type', 'council_number', 'council_uf'],
                'users_council_registration_unique',
            );
        });

        DB::table('users')->whereNull('password')->orderBy('id')->eachById(
            static fn (object $user): int => DB::table('users')
                ->where('id', $user->id)
                ->update(['password' => Hash::make(Str::random(64))]),
            100,
            'id',
        );

        // Perfis legados não possuem credencial individual nem conselho
        // verificável. Permanecem preservados para histórico, porém inativos
        // até que um gestor complete o cadastro.
        DB::table('users')
            ->where('role', 'professional')
            ->whereNull('council_type')
            ->update(['is_active' => false]);

        Schema::table('users', function (Blueprint $table): void {
            $table->string('password')->nullable(false)->change();
        });

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
            $table->index(['ubs_id', 'status', 'created_at']);
        });

        Schema::table('audit_events', function (Blueprint $table): void {
            $table->foreignUuid('actor_user_id')
                ->nullable()
                ->after('actor_administrator_id')
                ->constrained('users')
                ->restrictOnDelete();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE audit_events DROP CONSTRAINT IF EXISTS audit_events_exactly_one_actor_check');
            DB::statement(<<<'SQL'
                ALTER TABLE audit_events
                ADD CONSTRAINT audit_events_exactly_one_actor_check
                CHECK (
                    (actor_ubs_id IS NOT NULL)::integer
                    + (actor_administrator_id IS NOT NULL)::integer
                    + (actor_user_id IS NOT NULL)::integer = 1
                )
            SQL);
            DB::statement(<<<'SQL'
                ALTER TABLE users
                ADD CONSTRAINT users_council_fields_check
                CHECK (
                    (
                        role = 'professional'
                        AND (
                            (council_type IS NOT NULL AND council_number IS NOT NULL AND council_uf IS NOT NULL AND specialty IS NOT NULL)
                            OR
                            (is_active = FALSE AND council_type IS NULL AND council_number IS NULL AND council_uf IS NULL AND specialty IS NULL)
                        )
                    )
                    OR
                    (role = 'admin' AND council_type IS NULL AND council_number IS NULL AND council_uf IS NULL AND specialty IS NULL)
                )
            SQL);
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_council_type_check CHECK (council_type IS NULL OR council_type IN ('CRM', 'COREN'))");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_council_uf_check CHECK (council_uf IS NULL OR council_uf ~ '^[A-Z]{2}$')");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE audit_events DROP CONSTRAINT IF EXISTS audit_events_exactly_one_actor_check');
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_council_fields_check');
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_council_type_check');
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_council_uf_check');
        }

        Schema::table('audit_events', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('actor_user_id');
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement(<<<'SQL'
                ALTER TABLE audit_events
                ADD CONSTRAINT audit_events_exactly_one_actor_check
                CHECK ((actor_ubs_id IS NOT NULL)::integer + (actor_administrator_id IS NOT NULL)::integer = 1)
            SQL);
        }

        Schema::table('assessments', function (Blueprint $table): void {
            $table->dropIndex(['ubs_id', 'status', 'created_at']);
            $table->dropConstrainedForeignId('questionnaire_version_id');
            $table->dropColumn(['status', 'started_at', 'completed_at']);
        });

        Schema::dropIfExists('questionnaire_versions');
        Schema::dropIfExists('questionnaires');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique('users_council_registration_unique');
            $table->dropIndex(['ubs_id', 'role', 'is_active']);
            $table->dropColumn([
                'is_active',
                'council_type',
                'council_number',
                'council_uf',
                'specialty',
            ]);
            $table->string('password')->nullable()->change();
        });
    }
};
