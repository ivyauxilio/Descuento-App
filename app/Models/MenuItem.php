<?php
// app/Models/MenuItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'menu_item_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'menu_item_id',
        'merchant_id',
        'name',
        'description',
        'price',
        'image_url',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id', 'merchant_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'menu_item_id', 'menu_item_id');
    }

    public function freePromotions()
    {
        return $this->hasMany(Promotion::class, 'free_menu_item_id', 'menu_item_id');
    }

    public function requiredPromotions()
    {
        return $this->hasMany(Promotion::class, 'required_menu_item_id', 'menu_item_id');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }
}