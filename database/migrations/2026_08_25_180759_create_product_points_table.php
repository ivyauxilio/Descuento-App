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
        Schema::create('product_points', function (Blueprint $table) {
            $table->id('points_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('merchant_id');
            $table->integer('points_per_item');
            $table->integer('points_per_php'); // points per peso spent
            $table->integer('min_spend')->default(0);
            $table->integer('max_points')->nullable();
            $table->datetime('start_date');
            $table->datetime('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_points');
    }
};