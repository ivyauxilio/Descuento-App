<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notification_broadcasts', function (Blueprint $table) {
            $table->id('broadcast_id');
            $table->uuid('broadcast_uuid')->unique();

            // Who sent it
            $table->unsignedBigInteger('sent_by');

            // Content (template copied to each notification)
            $table->string('type')->default('system');
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('image_url')->nullable();
            $table->string('action_url')->nullable();
            $table->string('action_label')->nullable();
            $table->json('data')->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');

            // Targeting
            $table->enum('audience', [
                'all',             // Everyone
                'customers',       // role = customer
                'merchants',       // role = merchant
                'active',          // status = active
                'inactive',        // status = inactive
                'has_wallet',      // has CustomerWallet
                'has_referrals',   // has referrals
                'custom',          // specific user IDs
            ])->default('all');

            $table->json('custom_user_ids')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            // Stats
            $table->integer('recipients_count')->default(0);
            $table->integer('delivered_count')->default(0);
            $table->integer('read_count')->default(0);

            $table->enum('status', ['draft', 'scheduled', 'sending', 'sent', 'failed'])
                  ->default('draft');

            $table->timestamp('sent_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('sent_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->index('status');
            $table->index('audience');
            $table->index('sent_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notification_broadcasts');
    }
};