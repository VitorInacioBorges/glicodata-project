<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('patient_id');
            $table->uuid('user_id');
            $table->foreignUuid('ubs_id')
                ->constrained('ubs')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->text('symptoms');
            $table->jsonb('answers');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('patient_id');
            $table->index('user_id');
            $table->index('ubs_id');
            $table->foreign(['patient_id', 'ubs_id'])
                ->references(['id', 'ubs_id'])
                ->on('patients')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreign(['user_id', 'ubs_id'])
                ->references(['id', 'ubs_id'])
                ->on('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
