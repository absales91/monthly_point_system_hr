<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('employee_rewards', function (Blueprint $table) {
        $table->id();
        $table->foreignId('employee_id')->constrained('users');
        $table->foreignId('reward_rule_id')->constrained('reward_rules');
        $table->integer('month');
        $table->integer('year');
        $table->integer('points_used');
        $table->integer('reward_value');
        $table->enum('status', ['pending','approved','used'])->default('pending');
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_rewards');
    }
};
