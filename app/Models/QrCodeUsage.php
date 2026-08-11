<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class QrCodeUsage extends Model
{
    protected $table = 'qr_code_usages';
    protected $primaryKey = 'usage_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'usage_id',
        'promotion_id',
        'merchant_id',
        'user_id',
        'qr_code',
        'discount_applied',
        'ip_address',
        'user_agent',
        'device_id',
        'location',
        'scanned_at',
    ];

    protected $casts = [
        'discount_applied' => 'decimal:2',
        'scanned_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->usage_id)) {
                $model->usage_id = (string) Str::uuid();
            }
        });
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'promotion_id', 'promotion_id');
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id', 'merchant_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}