<?php
// app/Models/Merchant.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Merchant extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $primaryKey = 'merchant_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'merchant_id',
        'owner_id',
        'category_id',
        'province_id',
        'approved_by',
        'business_name',
        'branch_name',
        'email',
        'street_address',
        'city',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id', 'province_id');
    }


    public function promotions()
    {
        return $this->hasMany(Promotion::class, 'merchant_id', 'merchant_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'merchant_id', 'merchant_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'merchant_id', 'merchant_id');
    }

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class, 'merchant_id', 'merchant_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'merchant_id', 'merchant_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    /**
     * Scope for pending merchants.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
    /**
     * Search scope.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('business_name', 'like', "%{$search}%")
              ->orWhere('branch_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('city', 'like', "%{$search}%");
        });
    }

    /**
     * Get the full business name with branch.
     */

     public function getFullBusinessNameAttribute()
    {
        return $this->branch_name 
            ? $this->business_name . ' - ' . $this->branch_name
            : $this->business_name;
    }

    /**
     * Get the full address.
     */
    public function getFullAddressAttribute()
    {
        return $this->street_address . ', ' . $this->city;
    }

     public function getStatusColorAttribute()
    {
        return [
            'pending' => 'warning',
            'approved' => 'info',
            'active' => 'success',
            'rejected' => 'danger',
            'suspended' => 'secondary',
        ][$this->status] ?? 'secondary';
    }

}