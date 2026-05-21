<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('keycloak_id')->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('district_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubs');
    }
};
