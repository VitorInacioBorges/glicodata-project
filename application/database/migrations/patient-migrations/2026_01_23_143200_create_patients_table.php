<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ubs_id')
                ->constrained('ubs')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('name');
            $table->boolean('sex');
            $table->string('cpf', 14)->unique();
            $table->string('address');
            $table->string('phone', 30);
            $table->date('birth');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('ubs_id');
            $table->unique(['id', 'ubs_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
