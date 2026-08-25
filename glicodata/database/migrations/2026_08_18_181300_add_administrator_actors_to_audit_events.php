<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_events', function (Blueprint $table): void {
            $table->uuid('actor_ubs_id')->nullable()->change();
            $table->foreignUuid('actor_administrator_id')
                ->nullable()
                ->after('actor_ubs_id')
                ->constrained('administrators')
                ->restrictOnDelete();
            $table->foreignUuid('redacted_by_administrator_id')
                ->nullable()
                ->after('redacted_by_ubs_id')
                ->constrained('administrators')
                ->restrictOnDelete();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement(<<<'SQL'
                ALTER TABLE audit_events
                ADD CONSTRAINT audit_events_exactly_one_actor_check
                CHECK ((actor_ubs_id IS NOT NULL)::integer + (actor_administrator_id IS NOT NULL)::integer = 1)
            SQL);
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE audit_events DROP CONSTRAINT IF EXISTS audit_events_exactly_one_actor_check');
        }

        Schema::table('audit_events', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('redacted_by_administrator_id');
            $table->dropConstrainedForeignId('actor_administrator_id');
            $table->uuid('actor_ubs_id')->nullable(false)->change();
        });
    }
};
