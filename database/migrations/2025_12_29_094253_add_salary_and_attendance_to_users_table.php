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
        Schema::table('users', function (Blueprint $table) {
             $table->decimal('basic_salary', 10, 2)->nullable();
        $table->integer('working_days')->default(26);
        $table->decimal('per_day_salary', 10, 2)->nullable();

        // ⏱ Attendance rules
        $table->time('office_in_time')->default('10:00:00');
        $table->time('office_out_time')->default('19:00:00');
        $table->integer('late_minutes_allowed')->default(15);
        $table->integer('half_day_hours')->default(4);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'basic_salary',
                'working_days',
                'per_day_salary',
                'office_in_time',
                'office_out_time',
                'late_minutes_allowed',
                'half_day_hours',
            ]);
        });
    }
};
