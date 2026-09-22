<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class QrCodeUsage extends Model
{
    protected $table = 'qr_code_usages';
    protected $primaryKey = 'usage_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'usage_id',
        'usage_id',
        'promotion_id',
        'merchant_id',
        'order_id',
        'user_id',
        'product_id',
        'qr_code',
        'card_id',
        'card_number',
        'discount_applied',
        'discount_percentage',
        'min_order_amount',
        'max_discount',
        'amount_paid',
        'points_earned',
        'ip_address',
        'user_agent',
        'device_id',
        'location',
        'scanned_at',
        'redeemed_at',
        'expires_at',
        'redemption_method',
        'redemption_context',
        'redemption_source',
        'applied_to_items',
        'redeemed_by', // Add this field to track who redeemed
        'status', // pending, completed, cancelled
        'metadata',
    ];

    protected $casts = [
        'discount_applied' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'points_earned' => 'integer',
        'metadata' => 'array',
        'applied_to_items' => 'array',
        'scanned_at' => 'datetime',
        'redeemed_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->usage_id)) {
                $model->usage_id = (string) Str::uuid();
            }
        });
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'promotion_id', 'promotion_id');
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id', 'merchant_id');
    }
     public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function redeemedBy()
    {
        return $this->belongsTo(User::class, 'redeemed_by');
    }
    public function card()
    {
        return $this->belongsTo(PhysicalCard::class, 'card_id', 'card_id');
    }

        // Scopes
    public function scopeByMerchant($query, $merchantId)
    {
        return $query->where('merchant_id', $merchantId);
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('user_id', $customerId);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByContext($query, $context)
    {
        return $query->where('redemption_context', $context);
    }

    public function scopeOnlineOrders($query)
    {
        return $query->where('redemption_context', 'online_order');
    }

    public function scopeQRScans($query)
    {
        return $query->where('redemption_context', 'qr_scan');
    }

        // Helper methods
    public function markAsCompleted()
    {
        $this->status = 'completed';
        $this->redeemed_at = now();
        $this->save();
        
        // Update promotion usage count
        if ($this->promotion) {
            $this->promotion->used_count += 1;
            $this->promotion->save();
        }
        
        return $this;
    }

    public function markAsFailed($reason = null)
    {
        $this->status = 'failed';
        $this->metadata = array_merge(
            $this->metadata ?? [],
            ['failure_reason' => $reason, 'failed_at' => now()]
        );
        $this->save();
        
        return $this;
    }

        public function isValid()
    {
        if ($this->status !== 'pending') {
            return false;
        }

        if ($this->expires_at && $this->expires_at < now()) {
            return false;
        }

        if ($this->promotion && !$this->promotion->isValid()) {
            return false;
        }

        return true;
    }

    public function getDiscountAmount()
    {
        if ($this->discount_applied) {
            return $this->discount_applied;
        }

        if ($this->promotion && $this->promotion->isValid()) {
            return $this->promotion->value;
        }

        return 0;
    }

    public function getDiscountedPrice($originalPrice)
    {
        $discount = $this->getDiscountAmount();
        
        if ($this->promotion && $this->promotion->promo_type === 'percentage') {
            return $originalPrice - ($originalPrice * $discount / 100);
        }
        
        if ($this->promotion && $this->promotion->promo_type === 'fixed') {
            return max(0, $originalPrice - $discount);
        }
        
        return $originalPrice;
    }

}