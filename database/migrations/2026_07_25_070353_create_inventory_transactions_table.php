<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->uuid('transaction_id')->primary();
            $table->uuid('menu_item_id');
            $table->enum('type', ['stock_in', 'stock_out', 'adjustment', 'return']);
            $table->integer('quantity');
            $table->integer('previous_quantity')->default(0);
            $table->integer('new_quantity')->default(0);
            $table->text('reason')->nullable();
            $table->string('reference_type')->nullable();
            $table->string('reference_id')->nullable();
            
            // FIX: Use unsignedBigInteger to match users.id
            $table->unsignedBigInteger('performed_by');
            
            $table->timestamps();

            // Foreign keys
            $table->foreign('menu_item_id')
                  ->references('menu_item_id')
                  ->on('menu_items')
                  ->onDelete('cascade');
                  
            $table->foreign('performed_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->index('menu_item_id');
            $table->index('type');
            $table->index('performed_by');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};