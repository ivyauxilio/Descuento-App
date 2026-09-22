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
       Schema::table('orders', function (Blueprint $table) {
            // Add new columns for grocery store integration
            $table->uuid('physical_card_id')->nullable()->after('promotion_id');
            $table->string('order_number')->unique()->after('order_id');
            $table->enum('order_type', ['delivery', 'pickup', 'dine_in'])->default('delivery')->after('status');
            $table->enum('payment_method', ['cash', 'card', 'gcash', 'points', 'bank_transfer'])->default('cash')->after('order_type');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending')->after('payment_method');
            $table->decimal('subtotal', 10, 2)->after('total_amount');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('subtotal');
            $table->decimal('tax_amount', 10, 2)->default(0)->after('discount_amount');
            $table->decimal('delivery_fee', 10, 2)->default(0)->after('tax_amount');
            $table->decimal('points_earned', 10, 2)->default(0)->after('delivery_fee');
            $table->decimal('points_redeemed', 10, 2)->default(0)->after('points_earned');
            $table->integer('points_redeemed_value')->default(0)->after('points_redeemed');
            $table->decimal('grand_total', 10, 2)->after('points_redeemed_value');
            $table->text('notes')->nullable()->after('grand_total');
            $table->text('delivery_instructions')->nullable()->after('notes');
            $table->json('delivery_address')->nullable()->after('delivery_instructions');
            $table->string('delivery_contact')->nullable()->after('delivery_address');
            $table->string('delivery_contact_name')->nullable()->after('delivery_contact');
            $table->datetime('pickup_time')->nullable()->after('delivery_contact_name');
            $table->datetime('delivered_at')->nullable()->after('pickup_time');
            $table->datetime('cancelled_at')->nullable()->after('delivered_at');
            $table->text('cancellation_reason')->nullable()->after('cancelled_at');
            $table->json('metadata')->nullable()->after('cancellation_reason');

            // Add foreign key for physical card
            $table->foreign('physical_card_id')
                  ->references('card_id')
                  ->on('physical_cards')
                  ->onDelete('set null');

            // Add indexes for performance
            $table->index(['merchant_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index('order_number');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['physical_card_id']);
            $table->dropColumn([
                'physical_card_id',
                'order_number',
                'order_type',
                'payment_method',
                'payment_status',
                'subtotal',
                'discount_amount',
                'tax_amount',
                'delivery_fee',
                'points_earned',
                'points_redeemed',
                'points_redeemed_value',
                'grand_total',
                'notes',
                'delivery_instructions',
                'delivery_address',
                'delivery_contact',
                'delivery_contact_name',
                'pickup_time',
                'delivered_at',
                'cancelled_at',
                'cancellation_reason',
                'metadata'
            ]);
        });
    }
};