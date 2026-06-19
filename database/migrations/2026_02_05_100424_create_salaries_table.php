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
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->string('month');            // e.g. 2026-02
            $table->decimal('gross_salary', 10, 2)->nullable();
            $table->decimal('deductions', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2)->nullable();
            $table->integer('working_days')->nullable();
            $table->integer('present_days')->nullable();
            $table->integer('half_days')->nullable();
            $table->integer('absent_days')->nullable();
            $table->integer('overtime_minutes')->default(0);
            $table->timestamps();

            $table->unique(['employee_id', 'month']); // one salary per employee per month
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salaries');
    }
};
