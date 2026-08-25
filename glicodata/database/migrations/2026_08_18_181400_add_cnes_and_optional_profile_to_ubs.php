<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ubs', function (Blueprint $table): void {
            $table->char('cnes', 7)->nullable()->unique()->after('id');
            $table->uuid('district_id')->nullable()->change();
            $table->string('name')->nullable()->change();
            $table->string('bairro_ref')->nullable()->change();
            $table->string('address')->nullable()->change();
            $table->string('phone', 30)->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->boolean('is_active')->default(false)->change();
        });

        Schema::table('audit_events', function (Blueprint $table): void {
            $table->string('actor_email')->nullable()->change();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement(<<<'SQL'
                ALTER TABLE ubs
                ADD CONSTRAINT ubs_cnes_format_check
                CHECK (cnes IS NULL OR cnes ~ '^[0-9]{7}$')
            SQL);
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE ubs DROP CONSTRAINT IF EXISTS ubs_cnes_format_check');
        }

        Schema::table('ubs', function (Blueprint $table): void {
            $table->dropUnique(['cnes']);
            $table->dropColumn('cnes');
            $table->boolean('is_active')->default(true)->change();
        });
    }
};
