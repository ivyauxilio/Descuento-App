<?php
// app/Services/NotificationService.php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Send a notification to a specific user.
     */
    public function sendTo(User $user, string $type, string $title, ?string $body = null, array $options = []): Notification
    {
        return Notification::send($user->id, $type, $title, $body, $options);
    }

    // ============================================
    // CONVENIENCE METHODS for common events
    // ============================================

    public function referralWelcome(User $newUser, User $referrer, float $reward): Notification
    {
        return $this->sendTo(
            $newUser,
            'referral_reward',
            '🎉 Welcome bonus received!',
            "You got ₱" . number_format($reward, 2) . " welcome credit from {$referrer->firstname}'s referral.",
            [
                'action_url' => '/wallet/transactions',
                'action_label' => 'View Wallet',
                'priority' => 'high',
                'data' => ['amount' => $reward, 'referrer_id' => $referrer->id],
            ]
        );
    }

    public function referralSignupToReferrer(User $referrer, User $newUser): Notification
    {
        return $this->sendTo(
            $referrer,
            'referral_signup',
            '👋 Someone used your referral!',
            "{$newUser->firstname} signed up using your referral code. They'll need to activate their card for you to earn ₱50.",
            [
                'action_url' => '/referrals',
                'action_label' => 'View Referrals',
                'priority' => 'normal',
                'data' => ['referee_id' => $newUser->id, 'referee_name' => $newUser->firstname],
            ]
        );
    }

    public function referralApproved(User $referrer, User $referee, float $reward): Notification
    {
        return $this->sendTo(
            $referrer,
            'referral_approved',
            '💰 Referral reward approved!',
            "You earned ₱" . number_format($reward, 2) . " because {$referee->firstname} activated their card.",
            [
                'action_url' => '/wallet/transactions',
                'action_label' => 'View Wallet',
                'priority' => 'high',
                'data' => ['amount' => $reward, 'referee_id' => $referee->id],
            ]
        );
    }

    public function withdrawalRequested(User $user, float $amount): Notification
    {
        return $this->sendTo(
            $user,
            'withdrawal_requested',
            '📤 Withdrawal request received',
            "Your request for ₱" . number_format($amount, 2) . " is pending approval. We'll notify you once processed.",
            [
                'action_url' => '/wallet/transactions',
                'action_label' => 'View Transactions',
                'priority' => 'normal',
                'data' => ['amount' => $amount],
            ]
        );
    }

    public function withdrawalApproved(User $user, float $amount): Notification
    {
        return $this->sendTo(
            $user,
            'withdrawal_approved',
            '✅ Withdrawal successful!',
            "Your ₱" . number_format($amount, 2) . " withdrawal has been sent to your account.",
            [
                'action_url' => '/wallet/transactions',
                'action_label' => 'View Transactions',
                'priority' => 'high',
                'data' => ['amount' => $amount],
            ]
        );
    }

    public function withdrawalRejected(User $user, float $amount, string $reason): Notification
    {
        return $this->sendTo(
            $user,
            'withdrawal_rejected',
            '❌ Withdrawal rejected',
            "Your ₱" . number_format($amount, 2) . " withdrawal was rejected: {$reason}. The funds have been returned to your wallet.",
            [
                'action_url' => '/wallet/transactions',
                'action_label' => 'View Wallet',
                'priority' => 'urgent',
                'data' => ['amount' => $amount, 'reason' => $reason],
            ]
        );
    }

    public function cardActivated(User $user): Notification
    {
        return $this->sendTo(
            $user,
            'card_activated',
            '🎊 Card activated!',
            'Your KlickCard is now active. Start using it to earn rewards!',
            [
                'action_url' => '/card/details',
                'action_label' => 'View Card',
                'priority' => 'high',
            ]
        );
    }

    public function newPromotion(User $user, $promotion): Notification
    {
        return $this->sendTo(
            $user,
            'promotion',
            '🎁 New promotion available!',
            $promotion->title ?? 'Check out the latest promotion.',
            [
                'action_url' => "/promotions/{$promotion->promotion_id}",
                'action_label' => 'View Promotion',
                'priority' => 'normal',
                'data' => ['promotion_id' => $promotion->promotion_id],
            ]
        );
    }

    public function newOrderReceived(User $merchantUser, $order): Notification
    {
        return $this->sendTo(
            $merchantUser,
            'new_order',
            '🛍️ New order received!',
            "Order #{$order->order_number} — ₱" . number_format($order->total_amount, 2),
            [
                'action_url' => "/merchant/orders/{$order->order_id}",
                'action_label' => 'View Order',
                'priority' => 'high',
                'data' => ['order_id' => $order->order_id],
            ]
        );
    }

      public function creditLow(User $merchantUser, int $balance): Notification
      {
          return $this->sendTo(
              $merchantUser,
              'credit_low',
              '⚠️ Low credits',
              "You only have {$balance} credits remaining. Top up to keep creating promotions.",
              [
                  'action_url' => '/merchant/subscription',
                  'action_label' => 'Buy Credits',
                  'priority' => 'high',
              ]
          );
      }

      public function planPurchased(User $merchantUser, $plan): Notification
      {
          return $this->sendTo(
              $merchantUser,
              'plan_purchased',
              '🎉 Plan activated!',
              "{$plan->name} — {$plan->total_credits} credits added to your wallet.",
              [
                  'action_url' => '/merchant/wallet',
                  'action_label' => 'View Wallet',
                  'priority' => 'normal',
              ]
          );
      }
}