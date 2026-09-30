<?php
// app/Models/NotificationBroadcast.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NotificationBroadcast extends Model
{
    protected $primaryKey = 'broadcast_id';
    public $incrementing = true;

    protected $fillable = [
        'broadcast_uuid',
        'sent_by',
        'type',
        'title',
        'body',
        'image_url',
        'action_url',
        'action_label',
        'data',
        'priority',
        'audience',
        'custom_user_ids',
        'scheduled_at',
        'expires_at',
        'recipients_count',
        'delivered_count',
        'read_count',
        'status',
        'sent_at',
        'notes',
    ];

    protected $casts = [
        'data' => 'array',
        'custom_user_ids' => 'array',
        'scheduled_at' => 'datetime',
        'expires_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($broadcast) {
            if (empty($broadcast->broadcast_uuid)) {
                $broadcast->broadcast_uuid = (string) Str::uuid();
            }
        });
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'broadcast_id');
    }

    public function getReadRateAttribute(): float
    {
        if ($this->recipients_count === 0) {
            return 0;
        }
        return round(($this->read_count / $this->recipients_count) * 100, 1);
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'draft' => ['label' => 'Draft', 'color' => 'secondary'],
            'scheduled' => ['label' => 'Scheduled', 'color' => 'warning'],
            'sending' => ['label' => 'Sending', 'color' => 'info'],
            'sent' => ['label' => 'Sent', 'color' => 'success'],
            'failed' => ['label' => 'Failed', 'color' => 'danger'],
            default => ['label' => 'Unknown', 'color' => 'secondary'],
        };
    }

    /**
     * Resolve the audience into a User query.
     */
    public function getAudienceQuery()
    {
        $query = User::query();

        switch ($this->audience) {
            case 'customers':
                $query->where('role', 'customer');
                break;
            case 'merchants':
                $query->where('role', 'merchant');
                break;
            case 'active':
                $query->where('status', 'active');
                break;
            case 'inactive':
                $query->where('status', '!=', 'active');
                break;
            case 'has_wallet':
                $query->whereHas('wallet');
                break;
            case 'has_referrals':
                $query->has('referralsMade');
                break;
            case 'custom':
                $ids = $this->custom_user_ids ?? [];
                if (!empty($ids)) {
                    $query->whereIn('id', $ids);
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;
            case 'all':
            default:
                // No filter
                break;
        }

        return $query;
    }
}