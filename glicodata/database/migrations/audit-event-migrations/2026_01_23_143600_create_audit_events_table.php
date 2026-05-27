<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('actor_ubs_id')
                ->constrained('ubs')
                ->restrictOnDelete();
            $table->foreignUuid('owner_ubs_id')
                ->constrained('ubs')
                ->restrictOnDelete();
            $table->string('actor_name');
            $table->string('actor_email');
            $table->string('subject_type');
            $table->uuid('subject_id');
            $table->string('action', 40);
            $table->jsonb('before_payload')->nullable();
            $table->jsonb('after_payload')->nullable();
            $table->timestampTz('redacted_at')->nullable();
            $table->foreignUuid('redacted_by_ubs_id')
                ->nullable()
                ->constrained('ubs')
                ->restrictOnDelete();
            $table->text('redaction_reason')->nullable();
            $table->timestampsTz();

            $table->index(['owner_ubs_id', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_events');
    }
};
