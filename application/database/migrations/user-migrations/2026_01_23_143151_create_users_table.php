<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
            $table->unsignedTinyInteger('age');
            $table->boolean('sex');
            $table->string('cpf', 20)->unique();
            $table->string('address');
            $table->string('phone', 30);
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->enum('role', ['admin', 'user']);
            $table->rememberToken();
            $table->timestamps();

            $table->index('ubs_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
