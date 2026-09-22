<?php
// app/Models/ProductPoints.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPoints extends Model
{
    protected $primaryKey = 'points_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'product_id',
        'merchant_id',
        'points_per_item',
        'points_per_php',
        'min_spend',
        'max_points',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'points_per_item' => 'integer',
        'points_per_php' => 'integer',
        'min_spend' => 'decimal:2',
        'max_points' => 'integer',
        'is_active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'is_valid',
        'days_remaining',
    ];

    // ============================================
    // RELATIONSHIPS
    // ============================================

    /**
     * Get the product that owns the points
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    /**
     * Get the merchant that owns the points
     */
    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id', 'merchant_id');
    }

    // ============================================
    // SCOPES
    // ============================================

    /**
     * Scope a query to only include active points
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
     * Check if points is valid
     */
    public function getIsValidAttribute()
    {
        return $this->is_active &&
               $this->start_date <= now() &&
               (!$this->end_date || $this->end_date >= now());
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
     * Calculate points for purchase
     */
    public function calculatePoints($subtotal, $quantity = 1)
    {
        if (!$this->is_valid) {
            return 0;
        }

        if ($this->min_spend && $subtotal < $this->min_spend) {
            return 0;
        }

        $points = 0;

        // Points per item
        if ($this->points_per_item > 0) {
            $points += $this->points_per_item * $quantity;
        }

        // Points per peso spent
        if ($this->points_per_php > 0) {
            $points += floor($subtotal / $this->points_per_php);
        }

        // Apply max points limit
        if ($this->max_points && $points > $this->max_points) {
            $points = $this->max_points;
        }

        return $points;
    }

    /**
     * Get points value in peso
     */
    public function getPointsValue($points)
    {
        // Assuming 1 point = ₱1 (can be customized)
        return $points;
    }

    /**
     * Check if points can be earned
     */
    public function canEarnPoints($subtotal, $quantity = 1)
    {
        if (!$this->is_valid) {
            return false;
        }

        if ($this->min_spend && $subtotal < $this->min_spend) {
            return false;
        }

        return true;
    }

    /**
     * Get earned points for order
     */
    public function getEarnedPoints($subtotal, $quantity = 1)
    {
        return $this->calculatePoints($subtotal, $quantity);
    }

    /**
     * Get points label
     */
    public function getLabelAttribute()
    {
        $label = [];
        
        if ($this->points_per_item > 0) {
            $label[] = "{$this->points_per_item} pts/item";
        }
        
        if ($this->points_per_php > 0) {
            $label[] = "{$this->points_per_php} pts/₱";
        }
        
        return implode(' + ', $label);
    }
}