<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id('plan_id');
            $table->uuid('plan_uuid')->unique();
            $table->string('name'); // "Starter Boost", "Growth Boost"
            $table->string('slug')->unique(); // "starter-boost"
            $table->text('description')->nullable();
            
            // Pricing
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('PHP');
            
            // Credits
            $table->integer('base_credits');
            $table->integer('bonus_credits')->default(0);
            $table->integer('total_credits'); // base + bonus
            
            // Display
            $table->string('icon')->nullable(); // rocket, chart, target, crown
            $table->string('badge')->nullable(); // "BEST VALUE", "MAXIMUM VALUE"
            $table->string('tagline')->nullable(); // "GREAT VALUE", "BETTER VALUE"
            $table->decimal('cost_per_credit', 10, 2); // computed
            $table->integer('star_rating')->default(3); // 3, 4, 5
            
            // Ordering & Status
            $table->integer('sort_order')->default(0);
            $table->boolean('is_popular')->default(false); // "MOST POPULAR"
            $table->boolean('is_active')->default(true);
            
            // Features (JSON array)
            $table->json('features')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscription_plans');
    }
};