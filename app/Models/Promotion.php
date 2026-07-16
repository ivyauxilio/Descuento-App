<?php
// app/Models/Promotion.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'promotion_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'promotion_id',
        'merchant_id',
        'category_id',
        'free_menu_item_id',
        'required_menu_item_id',
        'title',
        'promo_type',
        'value',
        'min_order_amount',
        'min_quantity',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

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
}