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
        Schema::create('monthly_points', function (Blueprint $table) {
    $table->id();
    $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
    $table->string('month');
    $table->integer('attendance');
    $table->integer('punctuality');

    $table->integer('discipline')->default(0);
    $table->integer('participation');
    $table->integer('decision_making');
    $table->integer('creativity');
    $table->integer('training');
    $table->integer('test');



    $table->integer('total');
    $table->string('rating');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_points');
    }
};
