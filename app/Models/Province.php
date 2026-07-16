<?php
// app/Models/Province.php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Province extends Model
{
    use HasFactory,SoftDeletes, HasUuids;

    protected $primaryKey = 'province_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'province_id',
        'name',
        'region',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->province_id)) {
                $model->province_id = (string) Str::uuid();
            }
        });
    }


    public function merchants()
    {
        return $this->hasMany(Merchant::class, 'province_id', 'province_id');
    }
}