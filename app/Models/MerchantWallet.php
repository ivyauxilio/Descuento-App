<?php
// app/Models/MerchantWallet.php

namespace App\Models;

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
        'last_purchase_at',
        'last_used_at',
    ];

    protected $casts = [
        'credit_balance' => 'integer',
        'total_credits_purchased' => 'integer',
        'total_credits_used' => 'integer',
        'total_spent' => 'decimal:2',
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

    // ✅ Deduct credits
    public function deductCredits(int $amount, array $meta = [])
    {
        if ($this->credit_balance < $amount) {
            throw new \Exception("Insufficient credits. Available: {$this->credit_balance}, Required: {$amount}");
        }

        $before = $this->credit_balance;
        $this->credit_balance -= $amount;
        $this->total_credits_used += $amount;
        $this->last_used_at = now();
        $this->save();

        return CreditTransaction::create([
            'merchant_id' => $this->merchant_id,
            'type' => 'usage',
            'credits' => -$amount,
            'balance_before' => $before,
            'balance_after' => $this->credit_balance,
            'promotion_id' => $meta['promotion_id'] ?? null,
            'description' => $meta['description'] ?? 'Credits used',
            'metadata' => $meta['metadata'] ?? null,
        ]);
    }

    // ✅ Check if has enough credits
    public function hasEnough(int $amount): bool
    {
        return $this->credit_balance >= $amount;
    }
}