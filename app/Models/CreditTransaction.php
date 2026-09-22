<?php
// app/Models/CreditTransaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CreditTransaction extends Model
{
    protected $primaryKey = 'transaction_id';
    public $incrementing = true;

    protected $fillable = [
        'transaction_uuid',
        'merchant_id',
        'type',
        'credits',
        'balance_before',
        'balance_after',
        'amount',
        'currency',
        'plan_id',
        'promotion_id',
        'reference_number',
        'description',
        'metadata',
        'payment_status',
        'payment_method',
        'payment_reference',
    ];

    protected $casts = [
        'credits' => 'integer',
        'balance_before' => 'integer',
        'balance_after' => 'integer',
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($t) {
            if (empty($t->transaction_uuid)) {
                $t->transaction_uuid = (string) Str::uuid();
            }
        });
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id', 'merchant_id');
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id', 'plan_id');
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'promotion_id', 'promotion_id');
    }
}