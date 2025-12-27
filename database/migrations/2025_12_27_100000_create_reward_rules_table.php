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
    Schema::create('reward_rules', function (Blueprint $table) {
        $table->id();
        $table->string('reward_type');      // paid_leave, cash
        $table->string('reward_name');
        $table->integer('point_threshold'); // 1000
        $table->integer('reward_value');    // 1 leave
        $table->integer('max_per_month')->default(1);
        $table->boolean('carry_forward')->default(true);
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reward_rules');
    }
};
