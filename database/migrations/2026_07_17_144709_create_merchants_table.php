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
        Schema::create('merchants', function (Blueprint $table) {
            $table->uuid('merchant_id')->primary();
            $table->unsignedBigInteger('owner_id');
            $table->uuid('category_id');
            $table->uuid('province_id');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->string('business_name');
            $table->string('branch_name')->nullable();
            $table->string('email')->unique();
            $table->string('street_address');
            $table->string('city');
            $table->enum('status', ['pending', 'approved', 'active', 'rejected', 'suspended'])->default('pending');
            $table->timestamps();
            $table->softDeletes();

            // Add foreign keys after table creation
            $table->foreign('owner_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->foreign('category_id')
                  ->references('category_id')
                  ->on('categories')
                  ->onDelete('cascade');

            $table->foreign('province_id')
                  ->references('province_id')
                  ->on('provinces')
                  ->onDelete('cascade');

            $table->foreign('approved_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchants');
    }
};