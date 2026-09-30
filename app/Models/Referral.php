<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Referral extends Model
{
    protected $primaryKey = 'referral_id';
    public $incrementing = true;

    protected $fillable = [
        'referral_uuid',
        'referrer_id',
        'referee_id',
        'referral_code',
        'reward_amount',
        'currency',
        'status',
        'purchase_reference',
        'purchase_amount',
        'purchase_verified_at',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'ip_address',
        'user_agent',
        'device_fingerprint',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'reward_amount' => 'decimal:2',
        'purchase_amount' => 'decimal:2',
        'purchase_verified_at' => 'datetime',
        'approved_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($referral) {
            if (empty($referral->referral_uuid)) {
                $referral->referral_uuid = (string) Str::uuid();
            }
        });
    }

    // ============================================
    // RELATIONSHIPS
    // ============================================

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referee()
    {
        return $this->belongsTo(User::class, 'referee_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function walletTransaction()
    {
        return $this->hasOne(CustomerWalletTransaction::class, 'referral_id');
    }

    // ============================================
    // SCOPES
    // ============================================

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeQualified($query)
    {
        return $query->where('status', 'qualified');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // ============================================
    // STATUS HELPERS
    // ============================================

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isQualified(): bool
    {
        return $this->status === 'qualified';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'pending' => ['label' => 'Pending', 'color' => 'warning'],
            'qualified' => ['label' => 'Qualified', 'color' => 'info'],
            'approved' => ['label' => 'Approved', 'color' => 'success'],
            'rejected' => ['label' => 'Rejected', 'color' => 'danger'],
            'expired' => ['label' => 'Expired', 'color' => 'secondary'],
            default => ['label' => 'Unknown', 'color' => 'secondary'],
        };
    }
}