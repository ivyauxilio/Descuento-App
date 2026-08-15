<?php
// app/Models/Promotion.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
        'qr_code',
        'usage_limit',
        'used_count',
        'last_used_at',
        'usage_limit_per_user',
        'total_usage_limit',
        'priority',
        'is_stackable',
        'poster_image',        
        'poster_thumbnail',   
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
        'used_count' => 'integer',
        'usage_limit' => 'integer',
        'usage_limit_per_user' => 'integer',
        'total_usage_limit' => 'integer',
        'last_used_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->promotion_id)) {
                $model->promotion_id = (string) Str::uuid();
            }
            if (empty($model->qr_code)) {
                $model->qr_code = $model->generateQrCode();
            }
        });
        
        static::updating(function ($model) {
            // Auto-update status to expired if end_date is in the past
            if ($model->end_date && $model->end_date < now() && $model->status !== 'expired') {
                $model->status = 'expired';
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
    public function qrUsages()
    {
        return $this->hasMany(QrCodeUsage::class, 'promotion_id', 'promotion_id');
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

        // Helper Methods
    public function isActive(): bool
    {
        return $this->status === 'active' &&
               ($this->start_date <= now()) &&
               ($this->end_date === null || $this->end_date >= now());
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

        /**
     * Generate unique QR code.
     */
    public function generateQrCode(): string
    {
        $prefix = 'PROMO';
        $random = strtoupper(Str::random(8));
        $timestamp = now()->timestamp;
        return $prefix . '-' . $timestamp . '-' . $random;
    }

        /**
     * Generate QR code image.
     */
    public function generateQrCodeImage(): string
    {
        $qrData = json_encode([
            'promotion_id' => $this->promotion_id,
            'qr_code' => $this->qr_code,
            'title' => $this->title,
            'type' => $this->promo_type,
            'value' => $this->value,
            'merchant_id' => $this->merchant_id,
        ]);

        return QrCode::size(300)
            ->format('svg')
            ->errorCorrection('H')
            ->generate($qrData);
    }

       /**
     * Check if QR code is valid.
     */
    public function isValidQrCode(): bool
    {
        // Check status
        if ($this->status !== 'active') {
            return false;
        }

        // Check date range
        if ($this->start_date > now()) {
            return false;
        }

        if ($this->end_date && $this->end_date < now()) {
            return false;
        }

        // Check usage limit
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    public function isValid(): bool
    {
        // Check status
        if ($this->status !== 'active') {
            return false;
        }

        // Check date range
        if ($this->start_date && $this->start_date > now()) {
            return false;
        }

        if ($this->end_date && $this->end_date < now()) {
            return false;
        }

        // Check usage limit
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Check if user can use this promotion.
     */
    public function canUserUse($userId): bool
    {
        if (!$this->isValidQrCode()) {
            return false;
        }

        if ($this->usage_limit_per_user) {
            $userUsage = QrCodeUsage::where('promotion_id', $this->promotion_id)
                ->where('user_id', $userId)
                ->count();

            if ($userUsage >= $this->usage_limit_per_user) {
                return false;
            }
        }

        return true;
    }

        /**
     * Check if user can redeem this promotion.
     */
    public function canUserRedeem($userId): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        // Check user usage limit
        if ($this->usage_limit_per_user) {
            $userUsage = QrCodeUsage::where('promotion_id', $this->promotion_id)
                ->where('user_id', $userId)
                ->count();

            if ($userUsage >= $this->usage_limit_per_user) {
                return false;
            }
        }

        return true;
    }

    public function redeem($userId, $merchantId): array
    {
        if (!$this->canUserRedeem($userId)) {
            return [
                'success' => false,
                'message' => 'This promotion is not available for redemption.',
            ];
        }

        try {
            // Create usage record
            $usage = QrCodeUsage::create([
                'usage_id' => (string) Str::uuid(),
                'promotion_id' => $this->promotion_id,
                'merchant_id' => $merchantId,
                'user_id' => $userId,
                'qr_code' => $this->qr_code,
                'discount_applied' => $this->value,
                'scanned_at' => now(),
            ]);

            // Update promotion stats
            $this->increment('used_count');
            $this->last_used_at = now();
            $this->save();

            return [
                'success' => true,
                'data' => [
                    'usage_id' => $usage->usage_id,
                    'used_count' => $this->used_count,
                    'remaining' => $this->total_usage_limit ? $this->usage_limit - $this->used_count : null,
                    'last_used_at' => $this->last_used_at,
                    'redeemed_at' => $usage->scanned_at,
                ],
                'message' => 'Promotion redeemed successfully!',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to redeem promotion: ' . $e->getMessage(),
            ];
        }
    }
    /**
     * Increment usage count.
     */
    public function incrementUsage(): void
    {
        $this->increment('used_count');
        $this->last_used_at = now();
        $this->save();
    }

    public function scopeHasAvailableQr($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('usage_limit')
              ->orWhereRaw('used_count < usage_limit');
        });
    }

        // Helper Methods
    public function getUsagePercentage(): float
    {
        if (!$this->usage_limit) {
            return 0;
        }

        return round(($this->used_count / $this->usage_limit) * 100, 2);
    }

    public function getRemainingUses(): int
    {
        if (!$this->usage_limit) {
            return 9999;
        }

        return max(0, $this->usage_limit - $this->used_count);
    }

        /**
     * Get the poster image URL.
     */
    public function getPosterImageUrlAttribute()
    {
        if ($this->poster_image) {
            return asset('storage/' . $this->poster_image);
        }
        return null;
    }

    /**
     * Get the poster thumbnail URL.
     */
    public function getPosterThumbnailUrlAttribute()
    {
        if ($this->poster_thumbnail) {
            return asset('storage/' . $this->poster_thumbnail);
        }
        return null;
    }
}