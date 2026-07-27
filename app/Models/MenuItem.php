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
        'stock_quantity',
        'low_stock_threshold',
        'stock_status',
        'sku',
        'unit',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
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
            if (empty($model->sku)) {
                $model->sku = $model->generateSku();
            }
            $model->updateStockStatus();
        });

        static::updating(function ($model) {
            if ($model->isDirty('stock_quantity')) {
                $model->updateStockStatus();
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


        // Generate SKU
    public function generateSku(): string
    {
        $prefix = strtoupper(substr($this->name, 0, 3));
        $random = strtoupper(Str::random(6));
        return $prefix . '-' . $random;
    }

    // Update stock status based on quantity
    public function updateStockStatus(): void
    {
        if ($this->stock_quantity <= 0) {
            $this->stock_status = 'out_of_stock';
        } elseif ($this->stock_quantity <= $this->low_stock_threshold) {
            $this->stock_status = 'low_stock';
        } else {
            $this->stock_status = 'in_stock';
        }
    }

    // Check if in stock
    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }

    // Check if low stock
    public function isLowStock(): bool
    {
        return $this->stock_quantity > 0 && $this->stock_quantity <= $this->low_stock_threshold;
    }

    // Add stock
    public function addStock($quantity, $reason = null, $referenceType = null, $referenceId = null)
    {
        $previous = $this->stock_quantity;
        $this->stock_quantity += $quantity;
        $this->save();

        $this->logTransaction('stock_in', $quantity, $previous, $reason, $referenceType, $referenceId);
        return $this;
    }

    // Remove stock
    public function removeStock($quantity, $reason = null, $referenceType = null, $referenceId = null)
    {
        $previous = $this->stock_quantity;
        $this->stock_quantity -= $quantity;
        if ($this->stock_quantity < 0) {
            $this->stock_quantity = 0;
        }
        $this->save();

        $this->logTransaction('stock_out', $quantity, $previous, $reason, $referenceType, $referenceId);
        return $this;
    }

    // Log transaction
    public function logTransaction($type, $quantity, $previous, $reason = null, $referenceType = null, $referenceId = null)
    {
        InventoryTransaction::create([
            'transaction_id' => Str::uuid(),
            'menu_item_id' => $this->menu_item_id,
            'type' => $type,
            'quantity' => $quantity,
            'previous_quantity' => $previous,
            'new_quantity' => $this->stock_quantity,
            'reason' => $reason,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'performed_by' => auth()->id(),
        ]);
    }

    // Relationships
    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class, 'menu_item_id', 'menu_item_id');
    }

    public function scopeLowStock($query)
    {
        return $query->where('stock_status', 'low_stock');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('stock_status', 'out_of_stock');
    }
}