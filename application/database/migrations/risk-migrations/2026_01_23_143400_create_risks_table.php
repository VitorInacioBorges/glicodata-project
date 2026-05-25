<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('assessment_id')
                ->unique()
                ->constrained('assessments')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->decimal('percentage', 5, 2);
            $table->enum('classification', ['low', 'moderate', 'high']);
            $table->unsignedInteger('score');
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE risks ADD CONSTRAINT risks_percentage_range CHECK (percentage BETWEEN 0 AND 100)');
            DB::statement('ALTER TABLE risks ADD CONSTRAINT risks_score_non_negative CHECK (score >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('risks');
    }
};
