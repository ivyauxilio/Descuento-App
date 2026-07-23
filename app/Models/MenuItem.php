<?php
// app/Models/MenuItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MenuItem extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'menu_items';
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
        'deleted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->menu_item_id)) {
                $model->menu_item_id = (string) Str::uuid();
            }
        });
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id', 'merchant_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'menu_item_id', 'menu_item_id');
    }

    public function promotionsAsFree()
    {
        return $this->hasMany(Promotion::class, 'free_menu_item_id', 'menu_item_id');
    }

    public function promotionsAsRequired()
    {
        return $this->hasMany(Promotion::class, 'required_menu_item_id', 'menu_item_id');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

      public function scopeInStock($query)
    {
        return $query->where('status', '!=', 'out_of_stock');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    // Helper Methods
    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function getStatusBadgeColor(): string
    {
        return [
            'available' => 'success',
            'unavailable' => 'secondary',
            'out_of_stock' => 'danger',
        ][$this->status] ?? 'secondary';
    }

    public function getStatusLabel(): string
    {
        return [
            'available' => 'Available',
            'unavailable' => 'Unavailable',
            'out_of_stock' => 'Out of Stock',
        ][$this->status] ?? $this->status;
    }

    public function getFormattedPrice(): string
    {
        return '₱' . number_format($this->price, 2);
    }
}