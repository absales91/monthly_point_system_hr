<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('monthly_points', function (Blueprint $table) {
            $table->unique(['employee_id', 'month'], 'emp_month_unique');
        });
    }

    public function down()
    {
        Schema::table('monthly_points', function (Blueprint $table) {
            $table->dropUnique('emp_month_unique');
        });
    }
};
