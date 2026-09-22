<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class SystemSettingSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            // Payment gateway
            ['key' => 'payment.active_gateway', 'value' => 'manual', 'type' => 'string', 'group' => 'payment', 'label' => 'Active Payment Gateway', 'description' => 'Which gateway to use'],
            ['key' => 'payment.gcash_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'payment', 'label' => 'Enable GCash'],
            ['key' => 'payment.gcash_public_key', 'value' => '', 'type' => 'string', 'group' => 'payment', 'label' => 'GCash Public Key'],
            ['key' => 'payment.gcash_secret_key', 'value' => '', 'type' => 'string', 'group' => 'payment', 'label' => 'GCash Secret Key', 'is_public' => false],
            
            ['key' => 'payment.stripe_enabled', 'value' => '0', 'type' => 'boolean', 'group' => 'payment', 'label' => 'Enable Stripe'],
            ['key' => 'payment.stripe_public_key', 'value' => '', 'type' => 'string', 'group' => 'payment', 'label' => 'Stripe Public Key'],
            ['key' => 'payment.stripe_secret_key', 'value' => '', 'type' => 'string', 'group' => 'payment', 'label' => 'Stripe Secret Key', 'is_public' => false],
            
            ['key' => 'payment.paymongo_enabled', 'value' => '0', 'type' => 'boolean', 'group' => 'payment', 'label' => 'Enable PayMongo'],
            ['key' => 'payment.paymongo_public_key', 'value' => '', 'type' => 'string', 'group' => 'payment', 'label' => 'PayMongo Public Key'],
            ['key' => 'payment.paymongo_secret_key', 'value' => '', 'type' => 'string', 'group' => 'payment', 'label' => 'PayMongo Secret Key', 'is_public' => false],
            
            // Credit system
            ['key' => 'credits.welcome_bonus', 'value' => '10', 'type' => 'number', 'group' => 'credits', 'label' => 'Welcome Bonus Credits'],
            ['key' => 'credits.expire_days', 'value' => '365', 'type' => 'number', 'group' => 'credits', 'label' => 'Credits Expiry (days)'],
            ['key' => 'credits.basic_cost', 'value' => '1', 'type' => 'number', 'group' => 'credits', 'label' => 'Basic Voucher Cost'],
            ['key' => 'credits.featured_cost', 'value' => '2', 'type' => 'number', 'group' => 'credits', 'label' => 'Featured Voucher Cost'],
            ['key' => 'credits.priority_cost', 'value' => '5', 'type' => 'number', 'group' => 'credits', 'label' => 'Priority Voucher Cost'],
            ['key' => 'credits.refund_percentage', 'value' => '100', 'type' => 'number', 'group' => 'credits', 'label' => 'Refund % on Cancellation'],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }

        $this->command->info('✅ System settings seeded!');
    }
}