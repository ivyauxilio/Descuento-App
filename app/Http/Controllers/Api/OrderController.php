<?php
// app/Http/Controllers/Api/OrderController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\CustomerWallet;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * POST /api/orders — Place a new order
     */
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,product_id',
            'items.*.quantity' => 'required|integer|min:1',
            'delivery_address' => 'required_if:delivery_type,delivery|array',
            'delivery_contact_name' => 'required|string|max:100',
            'delivery_contact_phone' => 'required|string|max:20',
            'delivery_type' => 'required|in:delivery,pickup',
            'payment_method' => 'required|in:cash,gcash,maya,card',
            'promo_code' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $user = auth()->user();

            // Fetch products
            $products = Product::whereIn('product_id', collect($request->items)->pluck('product_id'))
                ->where('is_active', true)
                ->get()
                ->keyBy('product_id');

            // Validate stock + compute totals
            $subtotal = 0;
            $orderItems = [];

            foreach ($request->items as $line) {
                $product = $products[$line['product_id']] ?? null;

                if (!$product) {
                    return response()->json([
                        'success' => false,
                        'message' => "Product not found.",
                    ], 404);
                }

                if ($product->stock_quantity < $line['quantity']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for {$product->name}.",
                    ], 422);
                }

                $price = (float) ($product->discounted_price ?? $product->price);
                $lineTotal = $price * $line['quantity'];
                $subtotal += $lineTotal;

                $orderItems[] = [
                    'product' => $product,
                    'quantity' => $line['quantity'],
                    'unit_price' => $price,
                    'subtotal' => $lineTotal,
                ];
            }

            // Apply promo
            $discount = 0;
            $promotionId = null;

            if ($request->filled('promo_code')) {
                $promotion = Promotion::where('code', $request->promo_code)
                    ->where('status', 'active')
                    ->first();

                if ($promotion) {
                    if ($promotion->promo_type === 'percentage') {
                        $discount = $subtotal * ($promotion->value / 100);
                        if ($promotion->max_discount_amount) {
                            $discount = min($discount, (float) $promotion->max_discount_amount);
                        }
                    } elseif ($promotion->promo_type === 'fixed') {
                        $discount = min((float) $promotion->value, $subtotal);
                    }

                    $promotionId = $promotion->promotion_id;
                    $promotion->increment('used_count');
                }
            }

            $deliveryFee = $request->delivery_type === 'delivery' ? 49 : 0;
            $total = max(0, $subtotal + $deliveryFee - $discount);

            // Create order
            $order = Order::create([
                'order_id' => (string) Str::uuid(),
                'order_number' => 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6)),
                'customer_id' => $user->id,
                'merchant_id' => $products->first()->merchant_id,
                'promotion_id' => $promotionId,
                'order_type' => $request->delivery_type,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'delivery_fee' => $deliveryFee,
                'total_amount' => $total,
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'delivery_address' => $request->delivery_address,
                'delivery_contact_name' => $request->delivery_contact_name,
                'delivery_contact_phone' => $request->delivery_contact_phone,
                'delivery_instructions' => $request->delivery_instructions,
                'notes' => $request->notes,
            ]);

            // Create order items + deduct stock
            foreach ($orderItems as $line) {
                OrderItem::create([
                    'order_id' => $order->order_id,
                    'product_id' => $line['product']->product_id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'subtotal' => $line['subtotal'],
                ]);

                $line['product']->decrement('stock_quantity', $line['quantity']);
                $line['product']->update(['in_stock' => $line['product']->stock_quantity > 0]);
            }

            DB::commit();

            // Notify merchant
            try {
                $merchantUser = $order->merchant->owner ?? null;
                if ($merchantUser) {
                    app(NotificationService::class)->newOrderReceived($merchantUser, $order);
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to send merchant notification', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'success' => true,
                'data' => $order->load(['items.product', 'promotion']),
                'message' => 'Order placed successfully!',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Order placement failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to place order.',
            ], 500);
        }
    }

    /**
     * GET /api/orders — List user's orders
     */
    public function index(Request $request)
    {
        $orders = Order::where('customer_id', auth()->id())
            ->with(['items.product', 'promotion'])
            ->when($request->status && $request->status !== 'all', function ($q) use ($request) {
                return $q->where('status', $request->status);
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    /**
     * GET /api/orders/{id} — Single order
     */
    public function show($id)
    {
        $order = Order::where('order_id', $id)
            ->where('customer_id', auth()->id())
            ->with(['items.product', 'promotion', 'merchant'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * POST /api/orders/{id}/cancel
     */
    public function cancel(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $order = Order::where('order_id', $id)
            ->where('customer_id', auth()->id())
            ->firstOrFail();

        if (!in_array($order->status, ['pending', 'confirmed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be cancelled at this stage.',
            ], 422);
        }

        DB::transaction(function () use ($order, $request) {
            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $request->reason,
            ]);

            // Restore stock
            foreach ($order->items as $item) {
                $item->product?->increment('stock_quantity', $item->quantity);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled.',
        ]);
    }
}