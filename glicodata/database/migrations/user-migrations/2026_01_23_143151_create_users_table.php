<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ubs_id')
                ->constrained('ubs')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('name');
            $table->date('birth');
            $table->boolean('sex');
            $table->string('cpf', 14)->unique();
            $table->string('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email');
            $table->timestampTz('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->enum('role', ['admin', 'professional']);
            $table->rememberToken();
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('ubs_id');
            $table->unique(['id', 'ubs_id']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE users ADD CONSTRAINT users_email_lowercase_check CHECK (email = LOWER(email))');
            DB::statement('CREATE UNIQUE INDEX users_email_lower_unique ON users (LOWER(email))');
        } else {
            Schema::table('users', function (Blueprint $table): void {
                $table->unique('email');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
