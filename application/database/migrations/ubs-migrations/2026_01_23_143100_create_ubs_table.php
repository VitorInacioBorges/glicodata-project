<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ubs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('district_id')
                ->constrained('districts')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('name');
            $table->string('bairro_ref');
            $table->string('address');
            $table->string('phone', 30);
            $table->string('email');
            $table->string('password')->nullable();
            $table->string('keycloak_id')->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();

            $table->index('district_id');
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE ubs ADD CONSTRAINT ubs_email_lowercase_check CHECK (email = LOWER(email))');
            DB::statement('CREATE UNIQUE INDEX ubs_email_lower_unique ON ubs (LOWER(email))');
        } else {
            Schema::table('ubs', function (Blueprint $table): void {
                $table->unique('email');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ubs');
    }
};
