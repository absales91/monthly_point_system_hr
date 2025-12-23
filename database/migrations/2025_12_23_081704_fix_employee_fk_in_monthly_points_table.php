<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('monthly_points', function (Blueprint $table) {
            // Drop wrong foreign key
            $table->dropForeign(['employee_id']);
        });

        Schema::table('monthly_points', function (Blueprint $table) {
            // Add correct foreign key
            $table->foreign('employee_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('monthly_points', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);

            $table->foreign('employee_id')
                  ->references('id')
                  ->on('employees')
                  ->onDelete('cascade');
        });
    }
};

