<?php
// app/Models/SubscriptionPlan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SubscriptionPlan extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'plan_id';
    public $incrementing = true;

    protected $fillable = [
        'plan_uuid',
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'base_credits',
        'bonus_credits',
        'total_credits',
        'icon',
        'badge',
        'tagline',
        'cost_per_credit',
        'star_rating',
        'sort_order',
        'is_popular',
        'is_active',
        'features',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_per_credit' => 'decimal:2',
        'base_credits' => 'integer',
        'bonus_credits' => 'integer',
        'total_credits' => 'integer',
        'star_rating' => 'integer',
        'sort_order' => 'integer',
        'is_popular' => 'boolean',
        'is_active' => 'boolean',
        'features' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($plan) {
            if (empty($plan->plan_uuid)) {
                $plan->plan_uuid = (string) Str::uuid();
            }
            if (empty($plan->slug)) {
                $plan->slug = Str::slug($plan->name);
            }
        });

        static::saving(function ($plan) {
            // Auto-compute total credits
            $plan->total_credits = $plan->base_credits + $plan->bonus_credits;
            
            // Auto-compute cost per credit
            if ($plan->total_credits > 0) {
                $plan->cost_per_credit = round($plan->price / $plan->total_credits, 2);
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}