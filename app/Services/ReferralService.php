<?php
// app/Services/ReferralService.php

namespace App\Services;

use App\Models\User;
use App\Models\Referral;
use App\Models\CustomerWallet;
use App\Models\CustomerWalletTransaction;
use App\Models\SystemSetting;
use App\Models\PhysicalCard;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReferralService
{
    /**
     * Track a referral when a new user signs up with a referral code.
     * 
     * - Creates the referral record (status: pending)
     * - IMMEDIATELY credits the referee with their welcome reward
     * - Adds the referrer's reward to their PENDING balance (not available yet)
     * 
     * The referrer's pending reward becomes available only after:
     *   1. The referee activates their card
     *   2. An admin approves the referral
     */
    public function trackReferral(User $newUser, string $referralCode, array $context = []): ?Referral
    {
        if (!SystemSetting::get('referral.enabled', true)) {
            return null;
        }

        $referrer = User::where('referral_code', $referralCode)->first();

        if (!$referrer) {
            Log::info('Referral code not found', ['code' => $referralCode]);
            return null;
        }

        // ✅ Guard: no self-referral
        if ($referrer->id === $newUser->id) {
            Log::warning('Self-referral blocked', ['user_id' => $newUser->id]);
            return null;
        }

        // ✅ Guard: referrer must be active
        if ($referrer->status !== 'active') {
            Log::info('Referrer not active', ['referrer_id' => $referrer->id]);
            return null;
        }

        // ✅ Guard: max referrals limit
        $maxReferrals = (int) SystemSetting::get('referral.max_referrals_per_user', 0);
        if ($maxReferrals > 0 && $referrer->referralsMade()->count() >= $maxReferrals) {
            Log::info('Referrer hit max limit', ['referrer_id' => $referrer->id]);
            return null;
        }

        // ✅ Guard: referee already referred
        if (Referral::where('referee_id', $newUser->id)->exists()) {
            return null;
        }

        // Get configurable rewards
        $referrerReward = (float) SystemSetting::get('referral.referrer_reward', 50);
        $refereeReward = (float) SystemSetting::get('referral.referee_reward', 25);

        return DB::transaction(function () use (
            $newUser, $referrer, $referralCode, $context,
            $referrerReward, $refereeReward
        ) {
            // 1. Update the new user
            $newUser->update([
                'referred_by' => $referrer->id,
                'referred_at' => now(),
            ]);

            // 2. Create the referral record (PENDING until card activation)
            $referral = Referral::create([
                'referrer_id' => $referrer->id,
                'referee_id' => $newUser->id,
                'referral_code' => $referralCode,
                'reward_amount' => $referrerReward,
                'currency' => 'PHP',
                'status' => 'pending',
                'ip_address' => $context['ip'] ?? request()->ip(),
                'user_agent' => $context['user_agent'] ?? request()->userAgent(),
                'device_fingerprint' => $context['device_id'] ?? null,
                'metadata' => [
                    'referrer_reward' => $referrerReward,
                    'referee_reward' => $refereeReward,
                    'qualified_on' => SystemSetting::get('referral.qualify_on', 'card_activation'),
                ],
            ]);

            // 3. ✅ INSTANTLY credit the referee's wallet (available balance)
            $refereeWallet = $newUser->getOrCreateWallet();
            $refereeWallet->credit($refereeReward, [
                'type' => 'referral_reward',
                'referral_id' => $referral->referral_id,
                'description' => "Welcome bonus from {$referrer->firstname}'s referral",
                'metadata' => [
                    'role' => 'referee',
                    'referrer_id' => $referrer->id,
                ],
            ]);

            // 4. ✅ Add the referrer's reward to PENDING balance
            //    (moves to available after card activation + admin approval)
            $referrerWallet = $referrer->getOrCreateWallet();
            $referrerWallet->addPending($referrerReward);

            // 5. Increment referrer total count
            $referrer->increment('total_referrals');

            Log::info('Referral tracked', [
                'referrer_id' => $referrer->id,
                'referee_id' => $newUser->id,
                'referral_id' => $referral->referral_id,
                'referrer_pending' => $referrerReward,
                'referee_credited' => $refereeReward,
            ]);

            return $referral;
        });

        if ($referral) {
            $notifications = app(NotificationService::class);

            // To the NEW USER (referee)
            $notifications->referralWelcome($newUser, $referrer, $refereeReward);

            // To the REFERRER
            $notifications->referralSignupToReferrer($referrer, $newUser);
        }

        return $referral;
    }

    /**
     * Qualify a referral after the referee ACTIVATES a physical card.
     * 
     * Called from the card activation controller.
     * Moves the referral from 'pending' → 'qualified'.
     * 
     * If auto_approve is ON, immediately approves and credits the referrer.
     */
    public function qualifyFromCardActivation(PhysicalCard $card): ?Referral
    {
        if (!$card->user) {
            return null;
        }

        // Check if there's a pending referral for this user
        $referral = Referral::where('referee_id', $card->user->id)
            ->where('status', 'pending')
            ->first();

        if (!$referral) {
            return null;
        }

        // Optional: check minimum card price
        $minAmount = (float) SystemSetting::get('referral.min_purchase_amount', 0);
        $cardPrice = (float) ($card->price ?? 299.00);

        if ($minAmount > 0 && $cardPrice < $minAmount) {
            Log::info('Card price below minimum for referral', [
                'referral_id' => $referral->referral_id,
                'card_price' => $cardPrice,
                'min' => $minAmount,
            ]);
            return null;
        }

        // Mark referral as qualified
        $referral->update([
            'status' => 'qualified',
            'purchase_reference' => 'CARD-' . $card->card_number,
            'purchase_amount' => $cardPrice,
            'purchase_verified_at' => now(),
        ]);

        Log::info('Referral qualified via card activation', [
            'referral_id' => $referral->referral_id,
            'card_id' => $card->card_id,
            'referee_id' => $card->user_id,
        ]);

        // ✅ Auto-approve if enabled
        if (SystemSetting::get('referral.auto_approve', false)) {
            $this->approveReferral($referral);
        }

        return $referral->fresh();
    }

    /**
     * Approve a referral — moves referrer's pending reward → available balance
     */
    public function approveReferral(Referral $referral, ?User $admin = null): bool
    {
        if ($referral->status === 'approved') {
            return false;
        }

        if (!in_array($referral->status, ['pending', 'qualified'])) {
            return false;
        }

        return DB::transaction(function () use ($referral, $admin) {
            $referrer = $referral->referrer;
            $wallet = $referrer->getOrCreateWallet();

            $rewardAmount = (float) $referral->reward_amount;

            // ✅ Move from pending_balance → balance
            $wallet->confirmPending($rewardAmount, [
                'type' => 'referral_reward',
                'referral_id' => $referral->referral_id,
                'description' => "Referral reward approved — {$referral->referee->firstname} activated their card",
                'metadata' => [
                    'referee_id' => $referral->referee_id,
                    'referee_email' => $referral->referee->email,
                    'approved_by' => $admin?->id ?? auth()->id(),
                ],
            ]);

            // Update wallet stats
            $wallet->increment('successful_referrals');

            // Update user lifetime earnings
            $referrer->increment('total_referral_earnings', $rewardAmount);

            // Update referral record
            $referral->update([
                'status' => 'approved',
                'approved_by' => $admin?->id ?? auth()->id(),
                'approved_at' => now(),
            ]);

            Log::info('Referral approved', [
                'referral_id' => $referral->referral_id,
                'referrer_id' => $referrer->id,
                'amount' => $rewardAmount,
            ]);

            return true;
        });
    }

    /**
     * Reject a referral — removes the pending reward from the referrer's wallet
     */
    public function rejectReferral(Referral $referral, string $reason, ?User $admin = null): bool
    {
        if ($referral->status === 'rejected') {
            return false;
        }

        return DB::transaction(function () use ($referral, $reason, $admin) {
            // If it hasn't been approved yet, remove from pending
            if (in_array($referral->status, ['pending', 'qualified'])) {
                $referrer = $referral->referrer;
                if ($referrer) {
                    $wallet = $referrer->getOrCreateWallet();
                    $wallet->decrement('pending_balance', (float) $referral->reward_amount);
                }
            }

            $referral->update([
                'status' => 'rejected',
                'rejection_reason' => $reason,
                'approved_by' => $admin?->id ?? auth()->id(),
            ]);

            // Decrement referrer total count
            if ($referral->referrer) {
                $referral->referrer->decrement('total_referrals');
            }

            Log::info('Referral rejected', [
                'referral_id' => $referral->referral_id,
                'reason' => $reason,
            ]);

            return true;
        });
    }

    /**
     * Get stats for a user's referral dashboard
     */
    public function getUserStats(User $user): array
    {
        $wallet = $user->getOrCreateWallet();
        $referrals = $user->referralsMade();

        return [
            'referral_code' => $user->referral_code,
            'referral_url' => $user->referral_url,

            // Referral counts
            'total_referrals' => $referrals->count(),
            'pending' => $referrals->where('status', 'pending')->count(),
            'qualified' => $referrals->where('status', 'qualified')->count(),
            'approved' => $referrals->where('status', 'approved')->count(),
            'rejected' => $referrals->where('status', 'rejected')->count(),

            // Earnings
            'total_earnings' => (float) $referrals->where('status', 'approved')->sum('reward_amount'),
            'pending_earnings' => (float) $wallet->pending_balance,
            'wallet_balance' => (float) $wallet->balance,
            'lifetime_earned' => (float) $wallet->total_earned,

            // Config for UI
            'referrer_reward' => (float) SystemSetting::get('referral.referrer_reward', 50),
            'referee_reward' => (float) SystemSetting::get('referral.referee_reward', 25),
            'min_withdrawal' => (float) SystemSetting::get('referral.min_withdrawal', 500),
        ];
    }
}