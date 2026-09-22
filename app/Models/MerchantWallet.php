<?php
// app/Models/MerchantWallet.php

namespace App\Models;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;

class MerchantWallet extends Model
{
    protected $primaryKey = 'wallet_id';
    public $incrementing = true;

    protected $fillable = [
        'merchant_id',
        'credit_balance',
        'total_credits_purchased',
        'total_credits_used',
        'total_spent',
        'welcome_bonus_claimed',
        'welcome_bonus_claimed_at',
        'last_purchase_at',
        'last_used_at',
    ];

    protected $casts = [
        'credit_balance' => 'integer',
        'total_credits_purchased' => 'integer',
        'total_credits_used' => 'integer',
        'total_spent' => 'decimal:2',
        'welcome_bonus_claimed' => 'boolean',
        'welcome_bonus_claimed_at' => 'datetime',
        'last_purchase_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id', 'merchant_id');
    }

    public function transactions()
    {
        return $this->hasMany(CreditTransaction::class, 'merchant_id', 'merchant_id');
    }

    // ✅ Add credits
    public function addCredits(int $amount, array $meta = [])
    {
        $before = $this->credit_balance;
        $this->credit_balance += $amount;
        $this->total_credits_purchased += $amount;
        $this->last_purchase_at = now();
        $this->save();

        return CreditTransaction::create([
            'merchant_id' => $this->merchant_id,
            'type' => $meta['type'] ?? 'purchase',
            'credits' => $amount,
            'balance_before' => $before,
            'balance_after' => $this->credit_balance,
            'amount' => $meta['amount'] ?? 0,
            'plan_id' => $meta['plan_id'] ?? null,
            'reference_number' => $meta['reference_number'] ?? null,
            'description' => $meta['description'] ?? 'Credits added',
            'metadata' => $meta['metadata'] ?? null,
            'payment_status' => $meta['payment_status'] ?? null,
            'payment_method' => $meta['payment_method'] ?? null,
        ]);
    }

    // Deduct credits
    public function deductCredits(int $amount, array $meta = [])
    {
        if ($this->credit_balance < $amount) {
            throw new \Exception("Insufficient credits. Available: {$this->credit_balance}, Required: {$amount}");
        }

        return DB::transaction(function () use ($amount, $meta) {
            // Lock for update
            $wallet = static::where('wallet_id', $this->wallet_id)
                ->lockForUpdate()
                ->first();

            if ($wallet->credit_balance < $amount) {
                throw new \Exception("Insufficient credits.");
            }

            $before = $wallet->credit_balance;
            $wallet->credit_balance -= $amount;
            $wallet->total_credits_used += $amount;
            $wallet->last_used_at = now();
            $wallet->save();

            $transaction = CreditTransaction::create([
                'merchant_id' => $wallet->merchant_id,
                'type' => 'usage',
                'credits' => -$amount,
                'balance_before' => $before,
                'balance_after' => $wallet->credit_balance,
                'promotion_id' => $meta['promotion_id'] ?? null,
                'description' => $meta['description'] ?? 'Credits used',
                'metadata' => $meta['metadata'] ?? null,
            ]);

            $this->refresh();
            return $transaction;
        });
    }

    // Check if has enough credits
    public function hasEnough(int $amount): bool
    {
        return $this->credit_balance >= $amount;
    }
    /**
     * Claim welcome bonus (10 free credits)
     */
    public function claimWelcomeBonus(int $amount = 10): bool
    {
        if ($this->welcome_bonus_claimed) {
            return false;
        }

        $before = $this->credit_balance;
        $this->credit_balance += $amount;
        $this->total_credits_purchased += $amount;
        $this->welcome_bonus_claimed = true;
        $this->welcome_bonus_claimed_at = now();
        $this->save();

        CreditTransaction::create([
            'merchant_id' => $this->merchant_id,
            'type' => 'bonus',
            'credits' => $amount,
            'balance_before' => $before,
            'balance_after' => $this->credit_balance,
            'amount' => 0,
            'description' => "Welcome bonus: {$amount} free credits",
            'metadata' => ['reason' => 'signup_bonus'],
        ]);

        return true;
    }

    /**
     * Check if merchant has enough credits (used before creating promotions)
     */
    public function requireCredits(int $amount): array
    {
        if ($this->credit_balance < $amount) {
            return [
                'has_credits' => false,
                'required' => $amount,
                'available' => $this->credit_balance,
                'message' => "You need {$amount} credits but only have {$this->credit_balance}. Please purchase more credits.",
            ];
        }

        return [
            'has_credits' => true,
            'required' => $amount,
            'available' => $this->credit_balance,
        ];
    }
}