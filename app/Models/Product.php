<?php
// app/Models/Product.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'product_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'merchant_id',
        'name',
        'slug',
        'description',
        'sku',
        'barcode',
        'price',
        'original_price',
        'category',
        'sub_category',
        'brand',
        'unit',
        'unit_value',
        'stock_quantity',
        'min_stock_alert',
        'in_stock',
        'is_featured',
        'is_active',
        'image_url',
        'images',
        'attributes',
        'weight',
        'nutritional_info',
        'country_of_origin',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'unit_value' => 'decimal:2',
        'stock_quantity' => 'integer',
        'min_stock_alert' => 'integer',
        'in_stock' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'images' => 'array',
        'attributes' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = [
        'discounted_price',
        'formatted_price',
        'stock_status',
        'has_active_discount',
        'points_per_item',
    ];

    // ============================================
    // BOOT METHOD
    // ============================================
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name) . '-' . Str::random(6);
            }
            if (empty($product->sku)) {
                $product->sku = strtoupper(Str::random(8));
            }
            if (empty($product->in_stock)) {
                $product->in_stock = $product->stock_quantity > 0;
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('stock_quantity')) {
                $product->in_stock = $product->stock_quantity > 0;
            }
        });
    }

    // ============================================
    // RELATIONSHIPS
    // ============================================
    
    /**
     * Get the merchant that owns the product
     */
    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id', 'merchant_id');
    }

    /**
     * Get the discounts for the product
     */
    public function discounts()
    {
        return $this->hasMany(ProductDiscount::class, 'product_id', 'product_id');
    }

    /**
     * Get the active discount for the product
     */
    public function activeDiscount()
    {
        return $this->hasOne(ProductDiscount::class, 'product_id', 'product_id')
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where(function($q) {
                $q->where('end_date', '>=', now())
                  ->orWhereNull('end_date');
            });
    }

    /**
     * Get the points settings for the product
     */
    public function points()
    {
        return $this->hasOne(ProductPoints::class, 'product_id', 'product_id');
    }

    /**
     * Get the active points settings for the product
     */
    public function activePoints()
    {
        return $this->hasOne(ProductPoints::class, 'product_id', 'product_id')
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where(function($q) {
                $q->where('end_date', '>=', now())
                  ->orWhereNull('end_date');
            });
    }

    /**
     * Get the order items for the product
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'product_id', 'product_id');
    }

    /**
     * Get the completed orders for the product
     */
    public function completedOrders()
    {
        return $this->hasMany(OrderItem::class, 'product_id', 'product_id')
            ->whereHas('order', function($q) {
                $q->where('status', 'completed');
            });
    }

    /**
     * Get the QR code usages for the product
     */
    public function qrCodeUsages()
    {
        return $this->hasMany(QRCodeUsage::class, 'product_id', 'product_id');
    }

    // ============================================
    // SCOPES
    // ============================================

    /**
     * Scope a query to only include active products
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include featured products
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to only include in-stock products
     */
    public function scopeInStock($query)
    {
        return $query->where('in_stock', true)->where('stock_quantity', '>', 0);
    }

    /**
     * Scope a query to only include low stock products
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'min_stock_alert')
                     ->where('stock_quantity', '>', 0);
    }

    /**
     * Scope a query to only include out of stock products
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('stock_quantity', 0);
    }

    /**
     * Scope a query to filter by category
     */
    public function scopeInCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to filter by brand
     */
    public function scopeOfBrand($query, $brand)
    {
        return $query->where('brand', $brand);
    }

    /**
     * Scope a query to filter by price range
     */
    public function scopePriceRange($query, $min, $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    /**
     * Scope a query to search products
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%")
              ->orWhere('sku', 'LIKE', "%{$search}%")
              ->orWhere('barcode', 'LIKE', "%{$search}%")
              ->orWhere('brand', 'LIKE', "%{$search}%")
              ->orWhere('description', 'LIKE', "%{$search}%");
        });
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
     * Get the discounted price
     */
    public function getDiscountedPriceAttribute()
    {
        $price = $this->price;
        $discount = $this->activeDiscount;

        if ($discount) {
            if ($discount->type === 'percentage') {
                return round($price - ($price * $discount->value / 100), 2);
            } elseif ($discount->type === 'fixed') {
                return round(max(0, $price - $discount->value), 2);
            }
        }

        return $price;
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute()
    {
        return '₱' . number_format($this->price, 2);
    }

    /**
     * Get stock status
     */
    public function getStockStatusAttribute()
    {
        if ($this->stock_quantity <= 0) {
            return [
                'label' => 'Out of Stock',
                'color' => 'danger',
                'icon' => 'XCircleIcon',
            ];
        }

        if ($this->stock_quantity <= $this->min_stock_alert) {
            return [
                'label' => 'Low Stock',
                'color' => 'warning',
                'icon' => 'ExclamationTriangleIcon',
            ];
        }

        return [
            'label' => 'In Stock',
            'color' => 'success',
            'icon' => 'CheckCircleIcon',
        ];
    }

    /**
     * Check if product has active discount
     */
    public function getHasActiveDiscountAttribute()
    {
        return $this->activeDiscount !== null;
    }

    /**
     * Get points per item
     */
    public function getPointsPerItemAttribute()
    {
        return $this->points ? $this->points->points_per_item : 0;
    }

    /**
     * Get discount percentage if applicable
     */
    public function getDiscountPercentageAttribute()
    {
        $discount = $this->activeDiscount;
        if ($discount && $discount->type === 'percentage') {
            return $discount->value;
        }
        return 0;
    }

    /**
     * Get savings amount
     */
    public function getSavingsAttribute()
    {
        if ($this->has_active_discount) {
            return round($this->price - $this->discounted_price, 2);
        }
        return 0;
    }

    /**
     * Get total sold count
     */
    public function getTotalSoldAttribute()
    {
        return $this->orderItems()
            ->whereHas('order', function($q) {
                $q->where('status', 'completed');
            })
            ->sum('quantity');
    }

    /**
     * Get total revenue
     */
    public function getTotalRevenueAttribute()
    {
        return $this->orderItems()
            ->whereHas('order', function($q) {
                $q->where('status', 'completed');
            })
            ->sum('subtotal');
    }

    /**
     * Set the name attribute
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value) . '-' . Str::random(6);
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    /**
     * Check if product is in stock
     */
    public function isInStock()
    {
        return $this->in_stock && $this->stock_quantity > 0;
    }

    /**
     * Check if product has sufficient stock
     */
    public function hasSufficientStock($quantity)
    {
        return $this->stock_quantity >= $quantity;
    }

    /**
     * Deduct stock
     */
    public function deductStock($quantity)
    {
        if (!$this->hasSufficientStock($quantity)) {
            throw new \Exception("Insufficient stock for product: {$this->name}");
        }

        $this->stock_quantity -= $quantity;
        $this->in_stock = $this->stock_quantity > 0;
        $this->save();

        return $this;
    }

    /**
     * Add stock
     */
    public function addStock($quantity)
    {
        $this->stock_quantity += $quantity;
        $this->in_stock = $this->stock_quantity > 0;
        $this->save();

        return $this;
    }

    /**
     * Get final price with discount
     */
    public function getFinalPrice($quantity = 1)
    {
        $discount = $this->activeDiscount;
        
        if (!$discount) {
            return $this->price * $quantity;
        }

        if ($discount->min_quantity && $quantity < $discount->min_quantity) {
            return $this->price * $quantity;
        }

        if ($discount->type === 'percentage') {
            $unitPrice = $this->price - ($this->price * $discount->value / 100);
            return $unitPrice * $quantity;
        } elseif ($discount->type === 'fixed') {
            $unitPrice = max(0, $this->price - $discount->value);
            return $unitPrice * $quantity;
        } elseif ($discount->type === 'bogo') {
            $freeItems = floor($quantity / 2);
            return $this->price * ($quantity - $freeItems);
        }

        return $this->price * $quantity;
    }

    /**
     * Calculate points for purchase
     */
    public function calculatePoints($subtotal, $quantity = 1)
    {
        $points = $this->activePoints;
        
        if (!$points) {
            return 0;
        }

        return $points->calculatePoints($subtotal, $quantity);
    }

    /**
     * Get product images
     */
    public function getImages()
    {
        $images = $this->images ?? [];
        
        if ($this->image_url) {
            array_unshift($images, $this->image_url);
        }
        
        return $images;
    }

    /**
     * Get product URL
     */
    public function getUrlAttribute()
    {
        return route('products.show', $this->slug);
    }

    /**
     * Check if product is on sale
     */
    public function isOnSale()
    {
        return $this->has_active_discount && $this->discounted_price < $this->price;
    }

    /**
     * Get product rating (average)
     */
    public function getAverageRatingAttribute()
    {
        // This would require a reviews table
        return 0;
    }

    /**
     * Get related products
     */
    public function getRelatedProductsAttribute()
    {
        return Product::where('merchant_id', $this->merchant_id)
            ->where('product_id', '!=', $this->product_id)
            ->where('category', $this->category)
            ->where('is_active', true)
            ->inStock()
            ->limit(4)
            ->get();
    }

    // ============================================
    // JSON SERIALIZATION
    // ============================================

    /**
     * Prepare the model for JSON serialization
     */
    public function toArray()
    {
        $array = parent::toArray();
        
        $array['discounted_price'] = $this->discounted_price;
        $array['formatted_price'] = $this->formatted_price;
        $array['savings'] = $this->savings;
        $array['stock_status'] = $this->stock_status;
        $array['has_active_discount'] = $this->has_active_discount;
        $array['points_per_item'] = $this->points_per_item;
        $array['is_on_sale'] = $this->isOnSale();
        $array['discount_percentage'] = $this->discount_percentage;
        $array['total_sold'] = $this->total_sold;
        $array['total_revenue'] = $this->total_revenue;
        
        return $array;
    }
}