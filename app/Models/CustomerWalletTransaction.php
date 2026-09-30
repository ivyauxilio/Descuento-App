<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CustomerWalletTransaction extends Model
{
    protected $primaryKey = 'transaction_id';
    public $incrementing = true;

    protected $fillable = [
        'transaction_uuid',
        'user_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'referral_id',
        'payment_method',
        'payment_reference',
        'payment_status',
        'description',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($txn) {
            if (empty($txn->transaction_uuid)) {
                $txn->transaction_uuid = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function referral()
    {
        return $this->belongsTo(Referral::class, 'referral_id');
    }

    public function getTypeBadgeAttribute(): array
    {
        return match ($this->type) {
            'referral_reward' => ['label' => 'Referral Reward', 'color' => 'success'],
            'withdrawal' => ['label' => 'Withdrawal', 'color' => 'danger'],
            'adjustment' => ['label' => 'Adjustment', 'color' => 'primary'],
            'reversal' => ['label' => 'Reversal', 'color' => 'warning'],
            'purchase' => ['label' => 'Purchase', 'color' => 'info'],
            default => ['label' => 'Unknown', 'color' => 'secondary'],
        };
    }
}