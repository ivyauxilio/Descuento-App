<?php
// app/Http/Controllers/Admin/PaymentController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhysicalCard;
use App\Models\Order;
use App\Services\PurchaseService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected PurchaseService $purchases;

    public function __construct(PurchaseService $purchases)
    {
        $this->purchases = $purchases;
    }

    /**
     * Admin confirms a card payment was received
     */
    public function confirmCardPayment(Request $request, $cardId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_reference' => 'nullable|string',
        ]);

        $card = PhysicalCard::findOrFail($cardId);

        // ... existing logic (mark card as paid, etc.) ...

        $card->update([
            'status' => 'active',
            'issued_at' => now(),
            'payment_verified_at' => now(),
            'payment_reference' => $request->payment_reference,
        ]);

        // ✅ QUALIFY REFERRAL
        $this->purchases->handleCardPurchase($card, (float) $request->amount);

        return back()->with('success', 'Payment confirmed. Referral qualified.');
    }

    /**
     * Admin confirms an order payment
     */
    public function confirmOrderPayment(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        $order->update([
            'status' => 'paid',
            'payment_status' => 'paid',
            'payment_verified_at' => now(),
        ]);

        // ✅ QUALIFY REFERRAL
        $this->purchases->handleOrderPurchase($order);

        return back()->with('success', 'Order paid. Referral qualified.');
    }
}