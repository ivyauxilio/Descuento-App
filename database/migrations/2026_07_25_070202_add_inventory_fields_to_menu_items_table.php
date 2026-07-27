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
        Schema::table('menu_items', function (Blueprint $table) {
            $table->integer('stock_quantity')->default(0)->after('price');
            $table->integer('low_stock_threshold')->default(5)->after('stock_quantity');
            $table->enum('stock_status', ['in_stock', 'low_stock', 'out_of_stock'])->default('in_stock')->after('low_stock_threshold');
            $table->string('sku')->unique()->nullable()->after('menu_item_id');
            $table->string('unit')->default('piece')->after('stock_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn([
                'stock_quantity',
                'low_stock_threshold',
                'stock_status',
                'sku',
                'unit'
            ]);
        });
    }
};