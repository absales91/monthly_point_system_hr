<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
             $table->id();

            $table->string('title');
            $table->text('description')->nullable();

            $table->foreignId('assigned_by')->constrained('users'); // admin / manager
            $table->foreignId('assigned_to')->constrained('users'); // employee

            $table->enum('priority', ['low', 'medium', 'high'])
                  ->default('medium');

            $table->date('due_date')->nullable();

            $table->enum('status', ['pending', 'in_progress', 'completed'])
                  ->default('pending');

            $table->timestamps();

            // Indexes
            $table->index('assigned_to');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
