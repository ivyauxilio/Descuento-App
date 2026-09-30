<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('user_id')->nullable(); // null = broadcast to all

            // Type determines icon, color, and deep-link behavior on the app
            $table->string('type'); // referral_reward, withdrawal_approved, promotion, order, etc.

            // Content
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('image_url')->nullable();

            // Deep link target
            $table->string('action_url')->nullable();
            $table->string('action_label')->nullable();

            // Flexible payload (promotion_id, order_id, referral_id, etc.)
            $table->json('data')->nullable();

            // Priority for sorting
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');

            // Read state
            $table->timestamp('read_at')->nullable();

            // Optional: expiry
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('type');
            $table->index('priority');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};