<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
               // First, modify existing columns
        Schema::table('qr_code_usages', function (Blueprint $table) {
            // Make qr_code nullable for online orders
            $table->string('qr_code')->nullable()->change();
            
            // Add order_id for linking to orders
            $table->unsignedBigInteger('order_id')->nullable()->after('promotion_id');
            
            // Add product_id for item-specific promotion usage
            $table->unsignedBigInteger('product_id')->nullable()->after('order_id');
            
            // Add redemption context
            $table->enum('redemption_context', [
                'qr_scan', 
                'online_order', 
                'in_store', 
                'bulk_redemption'
            ])->default('qr_scan')->after('redemption_method');
            
            // Add source of redemption
            $table->enum('redemption_source', [
                'merchant_app',
                'customer_app',
                'web_store',
                'pos'
            ])->default('customer_app')->after('redemption_context');
            
            // Add discount details
            $table->decimal('discount_percentage', 5, 2)->nullable()->after('discount_applied');
            $table->decimal('min_order_amount', 10, 2)->nullable()->after('discount_percentage');
            $table->decimal('max_discount', 10, 2)->nullable()->after('min_order_amount');
            
            // Add order items reference (JSON for quick access)
            $table->json('applied_to_items')->nullable()->after('metadata');
            
            // Add expiry tracking
            $table->timestamp('expires_at')->nullable()->after('applied_to_items');
            $table->timestamp('redeemed_at')->nullable()->after('expires_at');
        });

        // Add indexes for better performance
        Schema::table('qr_code_usages', function (Blueprint $table) {
            $table->index(['order_id', 'promotion_id']);
            $table->index(['redemption_context', 'status']);
            $table->index('redemption_source');
            $table->index('redeemed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('qr_code_usages', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropForeign(['product_id']);
            
            $table->dropColumn([
                'order_id',
                'product_id',
                'redemption_context',
                'redemption_source',
                'discount_percentage',
                'min_order_amount',
                'max_discount',
                'applied_to_items',
                'expires_at',
                'redeemed_at'
            ]);
            
            // Revert qr_code back to not nullable
            $table->string('qr_code')->nullable(false)->change();
        });
    }
};