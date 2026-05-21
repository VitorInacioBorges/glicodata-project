<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ubs')) {
            return;
        }

        Schema::table('ubs', function (Blueprint $table) {
            if (! Schema::hasColumn('ubs', 'password')) {
                $table->string('password')->nullable()->after('email');
            }

            if (! Schema::hasColumn('ubs', 'keycloak_id')) {
                $table->string('keycloak_id')->nullable()->unique()->after('password');
            }
        });

        if (DB::getDriverName() === 'pgsql') {
            if (Schema::hasColumn('ubs', 'password')) {
                DB::statement('ALTER TABLE ubs ALTER COLUMN password DROP NOT NULL');
            }

            if (Schema::hasColumn('ubs', 'keycloak_id')) {
                DB::statement(<<<'SQL'
                    DO $$
                    BEGIN
                        IF NOT EXISTS (
                            SELECT 1
                            FROM pg_constraint
                            WHERE conname = 'ubs_keycloak_id_unique'
                        ) THEN
                            ALTER TABLE ubs
                                ADD CONSTRAINT ubs_keycloak_id_unique UNIQUE (keycloak_id);
                        END IF;
                    END
                    $$;
                    SQL);
            }

            if (Schema::hasTable('users') && Schema::hasColumn('users', 'password')) {
                DB::statement('ALTER TABLE users ALTER COLUMN password DROP NOT NULL');
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('ubs')) {
            return;
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE ubs DROP CONSTRAINT IF EXISTS ubs_keycloak_id_unique');
        }

        Schema::table('ubs', function (Blueprint $table) {
            if (Schema::hasColumn('ubs', 'keycloak_id')) {
                $table->dropColumn('keycloak_id');
            }

            if (Schema::hasColumn('ubs', 'password')) {
                $table->dropColumn('password');
            }
        });
    }
};
