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
        Schema::table('promotions', function (Blueprint $table) {
            // QR Code fields
            $table->string('qr_code')->unique()->nullable();
            $table->integer('usage_limit')->default(100);
            $table->integer('used_count')->default(0);
            $table->timestamp('last_used_at')->nullable();

            $table->integer('usage_limit_per_user')->nullable();
            $table->integer('total_usage_limit')->nullable();

            // Indexes
            $table->index('merchant_id');
            $table->index('promo_type');
            $table->index('status');
            $table->index(['start_date', 'end_date']);
            $table->index('qr_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    Schema::table('promotions', function (Blueprint $table) {
        
        $table->dropIndex(['merchant_id']);
        $table->dropIndex(['promo_type']);
        $table->dropIndex(['status']);
        $table->dropIndex(['start_date', 'end_date']);
        $table->dropIndex(['qr_code']);
        
        $table->dropColumn([
            'qr_code',
            'usage_limit',
            'used_count',
            'last_used_at',
            'usage_limit_per_user',
            'total_usage_limit',
        ]);
    });
    }
};