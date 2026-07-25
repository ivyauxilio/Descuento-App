<?php
// app/Models/Promotion.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'promotions';
    protected $primaryKey = 'promotion_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
         'promotion_id',
        'merchant_id',
        'category_id',
        'free_menu_item_id',
        'required_menu_item_id',
        'free_gift_product_id',
        'title',
        'description',
        'promo_type',
        'value',
        'buy_quantity',
        'get_quantity',
        'get_discount_percentage',
        'tiers',
        'min_order_amount',
        'min_quantity',
        'max_discount_amount',
        'start_date',
        'end_date',
        'status',
        'points_multiplier',
        'priority',
        'is_stackable',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'get_discount_percentage' => 'decimal:2',
        'buy_quantity' => 'integer',
        'get_quantity' => 'integer',
        'min_quantity' => 'integer',
        'points_multiplier' => 'integer',
        'priority' => 'integer',
        'tiers' => 'array',
        'is_stackable' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->promotion_id)) {
                $model->promotion_id = (string) Str::uuid();
            }
        });
    }

    // Relationships
    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id', 'merchant_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function freeMenuItem()
    {
        return $this->belongsTo(MenuItem::class, 'free_menu_item_id', 'menu_item_id');
    }

    public function requiredMenuItem()
    {
        return $this->belongsTo(MenuItem::class, 'required_menu_item_id', 'menu_item_id');
    }

    public function freeGiftProduct()
    {
        return $this->belongsTo(MenuItem::class, 'free_gift_product_id', 'menu_item_id');
    }

    public function redemptions()
    {
        return $this->hasMany(Redemption::class, 'promotion_id', 'promotion_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'promotion_id', 'promotion_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'promotion_id', 'promotion_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }
        // Helper Methods
    public function getTypeLabel(): string
    {
        $labels = [
            'percentage' => 'Percentage Discount',
            'fixed' => 'Fixed Amount Off',
            'bogo' => 'Buy One Get One',
            'free_gift' => 'Free Gift',
            'bundle' => 'Bundle Deal',
            'tiered' => 'Tiered Discount',
            'free_shipping' => 'Free Shipping',
            'loyalty_points' => 'Loyalty Points',
            'buy_x_get_y' => 'Buy X Get Y',
            'first_purchase' => 'First Purchase',
            'flash_sale' => 'Flash Sale',
        ];

        return $labels[$this->promo_type] ?? ucfirst($this->promo_type);
    }

    public function getTypeIcon(): string
    {
        $icons = [
            'percentage' => 'fa-percent',
            'fixed' => 'fa-tag',
            'bogo' => 'fa-gift',
            'free_gift' => 'fa-gift',
            'bundle' => 'fa-boxes',
            'tiered' => 'fa-layer-group',
            'free_shipping' => 'fa-truck',
            'loyalty_points' => 'fa-star',
            'buy_x_get_y' => 'fa-shopping-bag',
            'first_purchase' => 'fa-user-plus',
            'flash_sale' => 'fa-bolt',
        ];

        return $icons[$this->promo_type] ?? 'fa-tag';
    }

    public function getBadgeColor(): string
    {
        $colors = [
            'percentage' => 'info',
            'fixed' => 'primary',
            'bogo' => 'success',
            'free_gift' => 'warning',
            'bundle' => 'secondary',
            'tiered' => 'dark',
            'free_shipping' => 'success',
            'loyalty_points' => 'warning',
            'buy_x_get_y' => 'info',
            'first_purchase' => 'primary',
            'flash_sale' => 'danger',
        ];

        return $colors[$this->promo_type] ?? 'secondary';
    }

    public function getFormattedValue(): string
    {
        switch ($this->promo_type) {
            case 'percentage':
                return $this->value . '% OFF' . 
                       ($this->max_discount_amount ? ' (Max ₱' . number_format($this->max_discount_amount, 2) . ')' : '');
            case 'fixed':
                return '₱' . number_format($this->value, 2) . ' OFF';
            case 'bogo':
                return 'Buy 1 Get 1 Free';
            case 'free_gift':
                return 'Free Gift with Purchase';
            case 'bundle':
                return 'Bundle Deal';
            case 'tiered':
                return 'Tiered Discount';
            case 'free_shipping':
                return 'Free Shipping';
            case 'loyalty_points':
                return $this->points_multiplier . 'x Points';
            case 'buy_x_get_y':
                return 'Buy ' . $this->buy_quantity . ' Get ' . $this->get_quantity . ' at ' . $this->get_discount_percentage . '%';
            case 'first_purchase':
                return $this->value . '% Off First Order';
            case 'flash_sale':
                return $this->value . '% OFF Flash Sale';
            default:
                return '';
        }
    }

    public function getStatusBadgeColor(): string
    {
        if ($this->status === 'active' && !$this->isExpired()) {
            return 'success';
        } elseif ($this->status === 'inactive') {
            return 'secondary';
        } elseif ($this->isExpired() || $this->status === 'expired') {
            return 'danger';
        }
        return 'warning';
    }

    public function isExpired(): bool
    {
        return $this->end_date !== null && $this->end_date < now();
    }

    public function getTiersDisplay(): string
    {
        if (!$this->tiers) {
            return 'N/A';
        }

        $tiers = collect($this->tiers)->sortBy('min');
        return $tiers->map(function ($tier) {
            return '₱' . number_format($tier['min'], 0) . ' → ₱' . number_format($tier['discount'], 2) . ' off';
        })->implode(' | ');
    }

    public function getDaysLeft(): ?int
    {
        if (!$this->end_date) {
            return null;
        }
        
        $daysLeft = now()->diffInDays($this->end_date, false);
        return $daysLeft > 0 ? $daysLeft : 0;
    }
}