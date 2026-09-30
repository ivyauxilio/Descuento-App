<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Notification extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'broadcast_id',
        'type',
        'title',
        'body',
        'image_url',
        'action_url',
        'action_label',
        'data',
        'priority',
        'read_at',
        'expires_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($notification) {
            if (empty($notification->uuid)) {
                $notification->uuid = (string) Str::uuid();
            }
        });
    }

    // ============================================
    // RELATIONSHIPS
    // ============================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ============================================
    // SCOPES
    // ============================================

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeLatestFirst($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function broadcast()
  {
      return $this->belongsTo(NotificationBroadcast::class, 'broadcast_id');
  }

    // ============================================
    // HELPERS
    // ============================================

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function markAsRead(): bool
    {
        if ($this->isRead()) {
            return false;
        }

        $this->update(['read_at' => now()]);
        return true;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    // ============================================
    // STATIC CREATORS (convenience)
    // ============================================

    public static function send(
        int $userId,
        string $type,
        string $title,
        ?string $body = null,
        array $options = []
    ): self {
        return static::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'image_url' => $options['image_url'] ?? null,
            'action_url' => $options['action_url'] ?? null,
            'action_label' => $options['action_label'] ?? null,
            'data' => $options['data'] ?? null,
            'priority' => $options['priority'] ?? 'normal',
            'expires_at' => $options['expires_at'] ?? null,
        ]);
    }

    /**
     * Send a broadcast to a specific user.
     */
    public static function sendBroadcast(User $user, NotificationBroadcast $broadcast): self
    {
        return static::create([
            'user_id' => $user->id,
            'broadcast_id' => $broadcast->broadcast_id,
            'type' => $broadcast->type,
            'title' => $broadcast->title,
            'body' => $broadcast->body,
            'image_url' => $broadcast->image_url,
            'action_url' => $broadcast->action_url,
            'action_label' => $broadcast->action_label,
            'data' => $broadcast->data,
            'priority' => $broadcast->priority,
            'expires_at' => $broadcast->expires_at,
        ]);
    }
}