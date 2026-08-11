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
        Schema::create('qr_code_usages', function (Blueprint $table) {
            $table->uuid('usage_id')->primary();
            $table->uuid('promotion_id');
            $table->uuid('merchant_id');
            
            // IMPORTANT: Change this to match users.id type (unsignedBigInteger)
            $table->unsignedBigInteger('user_id')->nullable();
            
            $table->string('qr_code');
            $table->decimal('discount_applied', 10, 2)->default(0);
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('device_id')->nullable();
            $table->string('location')->nullable();
            $table->timestamp('scanned_at')->nullable();
            $table->timestamps();

            $table->foreign('promotion_id')
                  ->references('promotion_id')
                  ->on('promotions')
                  ->onDelete('cascade');
                  
            $table->foreign('merchant_id')
                  ->references('merchant_id')
                  ->on('merchants')
                  ->onDelete('cascade');
                  
            // Now this will work correctly
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            $table->index('promotion_id');
            $table->index('merchant_id');
            $table->index('qr_code');
            $table->index('scanned_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_code_usages');
    }
};