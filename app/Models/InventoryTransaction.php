<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InventoryTransaction extends Model
{
    protected $table = 'inventory_transactions';
    protected $primaryKey = 'transaction_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'transaction_id',
        'menu_item_id',
        'type',
        'quantity',
        'previous_quantity',
        'new_quantity',
        'reason',
        'reference_type',
        'reference_id',
        'performed_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'previous_quantity' => 'integer',
        'new_quantity' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->transaction_id)) {
                $model->transaction_id = (string) Str::uuid();
            }
        });
    }

    // Relationships
    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class, 'menu_item_id', 'menu_item_id');
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    // Scopes
    public function scopeStockIn($query)
    {
        return $query->where('type', 'stock_in');
    }

    public function scopeStockOut($query)
    {
        return $query->where('type', 'stock_out');
    }

    public function scopeAdjustment($query)
    {
        return $query->where('type', 'adjustment');
    }

    // Helpers
    public function getTypeLabel(): string
    {
        $labels = [
            'stock_in' => 'Stock In',
            'stock_out' => 'Stock Out',
            'adjustment' => 'Adjustment',
            'return' => 'Return',
        ];

        return $labels[$this->type] ?? $this->type;
    }

    public function getTypeBadgeColor(): string
    {
        $colors = [
            'stock_in' => 'success',
            'stock_out' => 'danger',
            'adjustment' => 'warning',
            'return' => 'info',
        ];

        return $colors[$this->type] ?? 'secondary';
    }
}