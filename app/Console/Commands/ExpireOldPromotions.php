<?php
// app/Console/Commands/ExpireOldPromotions.php

namespace App\Console\Commands;

use App\Models\Promotion;
use App\Services\CreditRefundService;
use Illuminate\Console\Command;

class ExpireOldPromotions extends Command
{
    protected $signature = 'promotions:expire';
    protected $description = 'Expire old promotions and refund credits';

    public function handle(CreditRefundService $refundService)
    {
        $expired = Promotion::where('status', 'active')
            ->where('end_date', '<', now())
            ->where('credits_refunded', false)
            ->get();

        foreach ($expired as $promotion) {
            $promotion->update(['status' => 'expired']);
            
            $refundService->refundPromotion(
                $promotion, 
                'Promotion expired without full usage'
            );

            $this->info("Expired: {$promotion->title}");
        }

        $this->info("✅ Processed {$expired->count()} expired promotions.");
    }
}