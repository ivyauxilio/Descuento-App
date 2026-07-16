<?php
// app/Models/Favorite.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'favorite_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'favorite_id',
        'customer_id',
        'promotion_id',
        'saved_at',
    ];

    protected $casts = [
        'saved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id', 'user_id');
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class, 'promotion_id', 'promotion_id');
    }
}