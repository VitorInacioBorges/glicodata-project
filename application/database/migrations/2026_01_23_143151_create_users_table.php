<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates a users table with the columns: 
     * | id | name | weight | age | risk | created_at | updated_at |
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('hashPassword');
            $table->string('email');
            $table->enum('role', ['admin', 'user']);
            $table->unsignedInteger('age');
            $table->decimal('weight', 5, 2);
            $table->decimal('risk', 5, 2);
            $table->boolean('hasRisk');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
