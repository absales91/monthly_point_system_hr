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
    Schema::create('reward_wallets', function (Blueprint $table) {
        $table->id();
        $table->foreignId('employee_id')->unique()->constrained('users');
        $table->integer('available_points')->default(0);
        $table->integer('lifetime_points')->default(0);
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reward_wallets');
    }
};
