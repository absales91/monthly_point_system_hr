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
         Schema::table('attendances', function (Blueprint $table) {

            // 📸 Selfie image
            $table->string('check_in_image')->nullable()->after('check_in');

            // 📍 GPS location
            $table->string('latitude', 50)->nullable()->after('check_in_image');
            $table->string('longitude', 50)->nullable()->after('latitude');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'check_in_image',
                'latitude',
                'longitude',
            ]);
        });
    }
};
