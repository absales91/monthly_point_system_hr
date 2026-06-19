<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendances_status', function (Blueprint $table) {
             DB::statement("
            ALTER TABLE attendances 
            MODIFY status ENUM('present','absent','half_day','leave','late','short_leave')
            DEFAULT 'absent'
        ");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       DB::statement("
            ALTER TABLE attendances 
            MODIFY status ENUM('present','absent','half_day','leave','late')
            DEFAULT 'absent'
        ");
    }
};
