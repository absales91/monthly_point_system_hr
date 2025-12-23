<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('employee_of_months', function (Blueprint $table) {
            $table->id();
            $table->string('month'); // YYYY-MM
            $table->foreignId('employee_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->integer('points');
            $table->timestamps();

            $table->unique(['month'], 'unique_employee_of_month');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_of_months');
    }
};
