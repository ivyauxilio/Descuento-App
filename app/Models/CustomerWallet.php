<?php
// app/Models/CustomerWallet.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CustomerWallet extends Model
{
    protected $primaryKey = 'wallet_id';
    public $incrementing = true;

    protected $fillable = [
        'user_id',
        'balance',
        'pending_balance',
        'total_earned',
        'total_withdrawn',
        'total_referrals',
        'successful_referrals',
        'last_earning_at',
        'last_withdrawal_at',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'pending_balance' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'total_withdrawn' => 'decimal:2',
        'last_earning_at' => 'datetime',
        'last_withdrawal_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transactions()
    {
        return $this->hasMany(CustomerWalletTransaction::class, 'user_id', 'user_id');
    }

    /**
     * ✅ Add to PENDING balance (not yet withdrawable)
     */
    public function addPending(float $amount): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be positive.');
        }

        $this->increment('pending_balance', $amount);
    }

    /**
     * ✅ Credit the AVAILABLE balance (withdrawable)
     */
    public function credit(float $amount, array $meta = []): CustomerWalletTransaction
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be positive.');
        }

        return DB::transaction(function () use ($amount, $meta) {
            $wallet = static::where('wallet_id', $this->wallet_id)
                ->lockForUpdate()
                ->first();

            $before = $wallet->balance;
            $wallet->balance += $amount;
            $wallet->total_earned += $amount;
            $wallet->last_earning_at = now();
            $wallet->save();

            return CustomerWalletTransaction::create([
                'user_id' => $wallet->user_id,
                'type' => $meta['type'] ?? 'adjustment',
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $wallet->balance,
                'referral_id' => $meta['referral_id'] ?? null,
                'description' => $meta['description'] ?? 'Wallet credit',
                'metadata' => $meta['metadata'] ?? null,
            ]);
        });
    }

    /**
     * ✅ Move from PENDING → AVAILABLE (admin approval)
     */
    public function confirmPending(float $amount, array $meta = []): CustomerWalletTransaction
    {
        return DB::transaction(function () use ($amount, $meta) {
            $wallet = static::where('wallet_id', $this->wallet_id)
                ->lockForUpdate()
                ->first();

            if ($wallet->pending_balance < $amount) {
                throw new \Exception(
                    "Pending balance too low. Have: {$wallet->pending_balance}, Need: {$amount}"
                );
            }

            $before = $wallet->balance;

            $wallet->pending_balance -= $amount;
            $wallet->balance += $amount;
            $wallet->total_earned += $amount;
            $wallet->last_earning_at = now();
            $wallet->save();

            return CustomerWalletTransaction::create([
                'user_id' => $wallet->user_id,
                'type' => $meta['type'] ?? 'referral_reward',
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $wallet->balance,
                'referral_id' => $meta['referral_id'] ?? null,
                'description' => $meta['description'] ?? 'Reward approved',
                'metadata' => $meta['metadata'] ?? null,
            ]);
        });
    }

    /**
     * ✅ Reverse pending (if referral rejected)
     */
    public function reversePending(float $amount, string $reason = 'Reversed'): void
    {
        if ($amount <= 0) return;

        $this->decrement('pending_balance', min($amount, (float) $this->pending_balance));

        // Optionally log this
        CustomerWalletTransaction::create([
            'user_id' => $this->user_id,
            'type' => 'reversal',
            'amount' => 0, // Doesn't affect available balance
            'balance_before' => $this->balance,
            'balance_after' => $this->balance,
            'description' => "Pending reward reversed: {$reason}",
            'metadata' => ['reversed_amount' => $amount],
        ]);
    }
}