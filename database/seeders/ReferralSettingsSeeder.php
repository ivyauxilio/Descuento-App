<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class ReferralSettingsSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            // Enable/disable
            ['key' => 'referral.enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'referral',
             'label' => 'Enable Referral System'],

            // Rewards
            ['key' => 'referral.referrer_reward', 'value' => '50', 'type' => 'number', 'group' => 'referral',
             'label' => 'Referrer Reward (₱)',
             'description' => 'Amount the referrer earns when their referral activates a card'],

            ['key' => 'referral.referee_reward', 'value' => '25', 'type' => 'number', 'group' => 'referral',
             'label' => 'Referee Welcome Reward (₱)',
             'description' => 'Instant welcome credit for the new customer'],

            // Qualification
            ['key' => 'referral.qualify_on', 'value' => 'card_activation', 'type' => 'string', 'group' => 'referral',
             'label' => 'Qualify Trigger',
             'description' => 'card_activation = after QR activation, card_purchase = on purchase'],

            ['key' => 'referral.auto_approve', 'value' => '0', 'type' => 'boolean', 'group' => 'referral',
             'label' => 'Auto-Approve Referrer Reward',
             'description' => 'If ON, referrer reward is credited immediately after activation'],

            // Limits
            ['key' => 'referral.max_referrals_per_user', 'value' => '0', 'type' => 'number', 'group' => 'referral',
             'label' => 'Max Referrals Per User (0 = unlimited)'],

            ['key' => 'referral.min_withdrawal', 'value' => '500', 'type' => 'number', 'group' => 'referral',
             'label' => 'Minimum Withdrawal (₱)'],

            ['key' => 'referral.cookie_expiry_days', 'value' => '30', 'type' => 'number', 'group' => 'referral',
             'label' => 'Referral Cookie Expiry (days)'],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }

        $this->command->info('✅ Referral settings seeded.');
    }
}