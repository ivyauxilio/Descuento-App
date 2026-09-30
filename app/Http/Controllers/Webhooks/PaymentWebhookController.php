<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\PhysicalCard;
use App\Models\Order;
use App\Services\PurchaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    protected PurchaseService $purchases;

    public function __construct(PurchaseService $purchases)
    {
        $this->purchases = $purchases;
    }

    /**
     * Generic handler — route by payment metadata
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::info('Payment webhook received', $payload);

        // Example: your gateway sends reference & metadata
        $reference = $payload['reference'] ?? null;
        $amount = $payload['amount'] ?? 0;
        $type = $payload['type'] ?? null; // 'card' or 'order'
        $targetId = $payload['metadata']['target_id'] ?? null;

        if (!$reference || !$targetId) {
            return response()->json(['status' => 'ignored'], 200);
        }

        if ($type === 'card') {
            $card = PhysicalCard::find($targetId);
            if ($card && $card->user) {
                $card->update([
                    'status' => 'active',
                    'issued_at' => now(),
                    'payment_verified_at' => now(),
                    'payment_reference' => $reference,
                ]);

                // ✅ QUALIFY
                $this->purchases->handleCardPurchase($card, (float) $amount);
            }
        }

        if ($type === 'order') {
            $order = Order::find($targetId);
            if ($order) {
                $order->update([
                    'status' => 'paid',
                    'payment_status' => 'paid',
                    'payment_verified_at' => now(),
                    'payment_reference' => $reference,
                ]);

                // ✅ QUALIFY
                $this->purchases->handleOrderPurchase($order);
            }
        }

        return response()->json(['status' => 'ok'], 200);
    }
}