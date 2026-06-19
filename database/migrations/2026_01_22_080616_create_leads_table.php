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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
             $table->string('unique_query_id')->unique();
            $table->string('query_type', 10)->nullable(); // B, W, P, BIZ
            $table->dateTime('query_time')->nullable();

            // Buyer Details
            $table->string('name')->nullable();
            $table->string('mobile', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('company')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode', 15)->nullable();
            $table->string('country_iso', 5)->nullable();

            // Inquiry Details
            $table->string('subject')->nullable();
            $table->string('product')->nullable();
            $table->string('mcat')->nullable();
            $table->longText('message')->nullable();

            // Call Related
            $table->integer('call_duration')->default(0);
            $table->string('receiver_mobile', 30)->nullable();

            // CRM / ERP Fields
            $table->string('source')->default('indiamart');
            $table->enum('lead_status', [
                'new',
                'contacted',
                'follow_up',
                'quotation_sent',
                'converted',
                'lost'
            ])->default('new');

            $table->unsignedBigInteger('assigned_to')->nullable(); // sales user id
            $table->timestamp('last_follow_up_at')->nullable();
            $table->timestamp('next_follow_up_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
