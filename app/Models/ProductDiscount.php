<?php
// app/Models/ProductDiscount.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductDiscount extends Model
{
    protected $primaryKey = 'discount_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'product_id',
        'merchant_id',
        'type',
        'value',
        'min_quantity',
        'max_quantity',
        'start_date',
        'end_date',
        'is_active',
        'usage_limit',
        'used_count',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_quantity' => 'integer',
        'max_quantity' => 'integer',
        'is_active' => 'boolean',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'is_valid',
        'formatted_value',
        'days_remaining',
    ];

    // ============================================
    // RELATIONSHIPS
    // ============================================

    /**
     * Get the product that owns the discount
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    /**
     * Get the merchant that owns the discount
     */
    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id', 'merchant_id');
    }

    // ============================================
    // SCOPES
    // ============================================

    /**
     * Scope a query to only include active discounts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where('start_date', '<=', now())
                     ->where(function($q) {
                         $q->where('end_date', '>=', now())
                           ->orWhereNull('end_date');
                     });
    }

    /**
     * Scope a query to only include percentage discounts
     */
    public function scopePercentage($query)
    {
        return $query->where('type', 'percentage');
    }

    /**
     * Scope a query to only include fixed discounts
     */
    public function scopeFixed($query)
    {
        return $query->where('type', 'fixed');
    }

    /**
     * Scope a query to only include BOGO discounts
     */
    public function scopeBogo($query)
    {
        return $query->where('type', 'bogo');
    }

    /**
     * Scope a query to filter by product
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope a query to filter by merchant
     */
    public function scopeForMerchant($query, $merchantId)
    {
        return $query->where('merchant_id', $merchantId);
    }

    // ============================================
    // ACCESSORS & MUTATORS
    // ============================================

    /**
     * Check if discount is valid
     */
    public function getIsValidAttribute()
    {
        return $this->is_active &&
               $this->start_date <= now() &&
               (!$this->end_date || $this->end_date >= now()) &&
               (!$this->usage_limit || $this->used_count < $this->usage_limit);
    }

    /**
     * Get formatted discount value
     */
    public function getFormattedValueAttribute()
    {
        if ($this->type === 'percentage') {
            return $this->value . '%';
        } elseif ($this->type === 'fixed') {
            return '₱' . number_format($this->value, 2);
        } elseif ($this->type === 'bogo') {
            return 'Buy One Get One';
        }
        return $this->value;
    }

    /**
     * Get days remaining
     */
    public function getDaysRemainingAttribute()
    {
        if (!$this->end_date) {
            return null;
        }
        return now()->diffInDays($this->end_date, false);
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    /**
     * Apply discount to price
     */
    public function applyDiscount($price, $quantity = 1)
    {
        if (!$this->is_valid || ($this->min_quantity && $quantity < $this->min_quantity)) {
            return $price * $quantity;
        }

        $discountedPrice = $price;

        switch ($this->type) {
            case 'percentage':
                $discountedPrice = $price - ($price * $this->value / 100);
                break;
            case 'fixed':
                $discountedPrice = max(0, $price - $this->value);
                break;
            case 'bogo':
                $freeItems = floor($quantity / 2);
                $discountedPrice = ($price * ($quantity - $freeItems)) / $quantity;
                break;
        }

        return max(0, $discountedPrice) * $quantity;
    }

    /**
     * Calculate discount amount
     */
    public function getDiscountAmount($price, $quantity = 1)
    {
        $originalTotal = $price * $quantity;
        $discountedTotal = $this->applyDiscount($price, $quantity);
        return $originalTotal - $discountedTotal;
    }

    /**
     * Increment usage count
     */
    public function incrementUsage()
    {
        $this->used_count += 1;
        $this->save();
        return $this;
    }

    /**
     * Check if discount can still be used
     */
    public function hasRemainingUsage()
    {
        if (!$this->usage_limit) {
            return true;
        }
        return $this->used_count < $this->usage_limit;
    }

    /**
     * Check if discount is expired
     */
    public function isExpired()
    {
        return $this->end_date && $this->end_date < now();
    }

    /**
     * Get discount label
     */
    public function getLabelAttribute()
    {
        if ($this->type === 'percentage') {
            return "{$this->value}% OFF";
        } elseif ($this->type === 'fixed') {
            return "₱{$this->value} OFF";
        } elseif ($this->type === 'bogo') {
            return "BOGO";
        }
        return "Discount";
    }
}