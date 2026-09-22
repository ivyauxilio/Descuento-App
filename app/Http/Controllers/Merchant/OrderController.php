<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\QRCodeUsage;
use App\Models\PhysicalCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Create a new order with promotions and QR code integration
     */
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,product_id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.customizations' => 'nullable|array',
            'promotion_code' => 'nullable|string|exists:promotions,promo_code',
            'qr_code' => 'nullable|string|exists:qr_code_usages,qr_code',
            'use_qr_discount' => 'nullable|boolean',
            'use_points' => 'nullable|boolean',
            'order_type' => 'required|in:delivery,pickup,dine_in',
            'payment_method' => 'required|in:cash,card,gcash,points,bank_transfer',
            'delivery_address' => 'required_if:order_type,delivery|array',
            'delivery_address.street' => 'required_if:order_type,delivery|string',
            'delivery_address.city' => 'required_if:order_type,delivery|string',
            'delivery_address.province' => 'required_if:order_type,delivery|string',
            'delivery_contact' => 'nullable|string|max:20',
            'delivery_contact_name' => 'nullable|string|max:100',
            'delivery_instructions' => 'nullable|string',
            'notes' => 'nullable|string',
            'physical_card_id' => 'nullable|exists:physical_cards,card_id',
            'pickup_time' => 'nullable|date|after:now',
        ]);

        try {
            DB::beginTransaction();

            $merchant = Auth::user()->merchant;
            $customer = Auth::user();

            if (!$merchant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Merchant not found.'
                ], 404);
            }

            // ============================================
            // 1. CALCULATE ORDER TOTALS
            // ============================================
            $subtotal = 0;
            $taxAmount = 0;
            $orderItemsData = [];
            $products = [];

            foreach ($request->items as $item) {
                $product = Product::where('merchant_id', $merchant->merchant_id)
                    ->where('product_id', $item['product_id'])
                    ->first();

                if (!$product) {
                    return response()->json([
                        'success' => false,
                        'message' => "Product not found: {$item['product_id']}"
                    ], 404);
                }

                // Check stock
                if ($product->stock_quantity < $item['quantity']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for: {$product->name}. Available: {$product->stock_quantity}"
                    ], 422);
                }

                // Calculate price with any product-specific discounts
                $unitPrice = $product->discounted_price ?? $product->price;
                $itemTotal = $unitPrice * $item['quantity'];
                $subtotal += $itemTotal;

                $products[] = $product;
                $orderItemsData[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'subtotal' => $itemTotal,
                    'customizations' => $item['customizations'] ?? null,
                ];
            }

            // ============================================
            // 2. CALCULATE TAX
            // ============================================
            $taxRate = 0.12; // 12% VAT
            $taxAmount = $subtotal * $taxRate;

            // ============================================
            // 3. HANDLE PROMOTION / QR CODE DISCOUNT
            // ============================================
            $discountAmount = 0;
            $promotionId = null;
            $qrUsage = null;
            $promotion = null;

            // Check if using QR code discount
            if ($request->use_qr_discount && $request->qr_code) {
                $qrUsage = QRCodeUsage::where('qr_code', $request->qr_code)
                    ->where('merchant_id', $merchant->merchant_id)
                    ->where('user_id', $customer->id)
                    ->where('status', 'pending')
                    ->where('redemption_context', 'qr_scan')
                    ->first();

                if ($qrUsage && $qrUsage->isValid()) {
                    $promotionId = $qrUsage->promotion_id;
                    $promotion = $qrUsage->promotion;
                    
                    // Calculate discount based on promotion type
                    if ($promotion) {
                        if ($promotion->promo_type === 'percentage') {
                            $discountAmount = $subtotal * ($promotion->value / 100);
                            // Apply max discount if set
                            if ($qrUsage->max_discount) {
                                $discountAmount = min($discountAmount, $qrUsage->max_discount);
                            }
                        } elseif ($promotion->promo_type === 'fixed') {
                            $discountAmount = min($promotion->value, $subtotal);
                        } elseif ($promotion->promo_type === 'bogo') {
                            // Calculate BOGO discount
                            $bogoDiscount = 0;
                            foreach ($orderItemsData as $item) {
                                $freeItems = floor($item['quantity'] / 2);
                                $bogoDiscount += $item['unit_price'] * $freeItems;
                            }
                            $discountAmount = $bogoDiscount;
                        }
                    }
                }
            }

            // Check for promo code (if no QR discount or fallback)
            if (!$promotionId && $request->promotion_code) {
                $promotion = Promotion::where('promo_code', $request->promotion_code)
                    ->where('merchant_id', $merchant->merchant_id)
                    ->where('is_active', true)
                    ->first();

                if ($promotion && $promotion->isValid()) {
                    $promotionId = $promotion->promotion_id;
                    
                    // Apply promotion discount
                    if ($promotion->promo_type === 'percentage') {
                        $discountAmount = $subtotal * ($promotion->value / 100);
                    } elseif ($promotion->promo_type === 'fixed') {
                        $discountAmount = min($promotion->value, $subtotal);
                    } elseif ($promotion->promo_type === 'bogo') {
                        $bogoDiscount = 0;
                        foreach ($orderItemsData as $item) {
                            $freeItems = floor($item['quantity'] / 2);
                            $bogoDiscount += $item['unit_price'] * $freeItems;
                        }
                        $discountAmount = $bogoDiscount;
                    }
                }
            }

            // ============================================
            // 4. CALCULATE DELIVERY FEE
            // ============================================
            $deliveryFee = 0;
            if ($request->order_type === 'delivery') {
                // You can implement dynamic delivery fee based on distance
                $deliveryFee = 50; // Fixed fee or calculate based on location
            }

            // ============================================
            // 5. CALCULATE POINTS
            // ============================================
            $pointsEarned = 0;
            $pointsRedeemed = 0;
            $pointsRedeemedValue = 0;

            // Calculate points earned (1 point per ₱100 spent)
            $amountAfterDiscount = $subtotal - $discountAmount;
            $pointsEarned = floor($amountAfterDiscount / 100);

            // Apply points redemption if requested
            if ($request->use_points && $customer->points_balance > 0) {
                // 1 point = ₱1 value (adjust as needed)
                $maxPointsToRedeem = min(
                    $customer->points_balance,
                    floor($amountAfterDiscount / 1) // Max points that can be used
                );
                $pointsRedeemed = $maxPointsToRedeem;
                $pointsRedeemedValue = $maxPointsToRedeem; // 1 point = ₱1
            }

            // ============================================
            // 6. CALCULATE GRAND TOTAL
            // ============================================
            $totalAmount = $subtotal - $discountAmount;
            $grandTotal = $totalAmount + $taxAmount + $deliveryFee - $pointsRedeemedValue;

            // ============================================
            // 7. CREATE ORDER
            // ============================================
            $order = Order::create([
                'order_id' => (string) \Illuminate\Support\Str::uuid(),
                'order_number' => 'ORD-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(8)),
                'customer_id' => $customer->id,
                'merchant_id' => $merchant->merchant_id,
                'promotion_id' => $promotionId,
                'physical_card_id' => $request->physical_card_id ?? null,
                'order_type' => $request->order_type,
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'delivery_fee' => $deliveryFee,
                'total_amount' => $totalAmount,
                'points_earned' => $pointsEarned,
                'points_redeemed' => $pointsRedeemed,
                'points_redeemed_value' => $pointsRedeemedValue,
                'grand_total' => $grandTotal,
                'notes' => $request->notes,
                'delivery_instructions' => $request->delivery_instructions,
                'delivery_address' => $request->delivery_address,
                'delivery_contact' => $request->delivery_contact,
                'delivery_contact_name' => $request->delivery_contact_name,
                'pickup_time' => $request->pickup_time,
                'metadata' => [
                    'customer_notes' => $request->customer_notes ?? null,
                    'source' => 'mobile_app',
                    'device_id' => $request->device_id ?? null,
                ],
            ]);

            // ============================================
            // 8. CREATE ORDER ITEMS
            // ============================================
            foreach ($orderItemsData as $itemData) {
                $product = $itemData['product'];
                $quantity = $itemData['quantity'];
                $unitPrice = $itemData['unit_price'];
                $subtotalItem = $itemData['subtotal'];
                $customizations = $itemData['customizations'];

                // Calculate item-level discount (for BOGO or item-specific discounts)
                $itemDiscount = 0;
                $pointsEarnedItem = 0;

                // Check if product has points
                if ($product->points && $product->points->is_active) {
                    $pointsEarnedItem = $product->points->points_per_item * $quantity;
                }

                // For BOGO promotions, calculate item discount
                if ($promotion && $promotion->promo_type === 'bogo' && $promotionId) {
                    $freeItems = floor($quantity / 2);
                    $itemDiscount = $unitPrice * $freeItems;
                }

                $orderItem = OrderItem::create([
                    'order_item_id' => (string) \Illuminate\Support\Str::uuid(),
                    'order_id' => $order->order_id,
                    'product_id' => $product->product_id,
                    'menu_item_id' => null, // Keep for compatibility
                    'promotion_id' => $promotionId,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotalItem,
                    'discount_applied' => $itemDiscount,
                    'points_earned' => $pointsEarnedItem,
                    'customizations' => $customizations,
                    'addons' => null,
                    'unit' => $product->unit,
                    'unit_value' => $product->unit_value,
                    'weight' => $product->weight,
                    'special_instructions' => $request->special_instructions ?? null,
                    'is_redeemed_with_points' => false,
                ]);

                // Update product stock
                $product->stock_quantity -= $quantity;
                $product->in_stock = $product->stock_quantity > 0;
                $product->save();
            }

            // ============================================
            // 9. CREATE QR CODE USAGE RECORD
            // ============================================
            if ($promotionId) {
                $qrUsageData = [
                    'promotion_id' => $promotionId,
                    'merchant_id' => $merchant->merchant_id,
                    'order_id' => $order->order_id,
                    'user_id' => $customer->id,
                    'discount_applied' => $discountAmount,
                    'discount_percentage' => $promotion && $promotion->promo_type === 'percentage' ? $promotion->value : null,
                    'min_order_amount' => $promotion->min_order_amount ?? null,
                    'max_discount' => $promotion->max_discount ?? null,
                    'redemption_context' => 'online_order',
                    'redemption_source' => 'customer_app',
                    'redemption_method' => $request->use_qr_discount ? 'app_qr' : 'manual',
                    'status' => 'completed',
                    'redeemed_at' => now(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'device_id' => $request->device_id ?? null,
                    'applied_to_items' => $request->items,
                    'metadata' => [
                        'order_number' => $order->order_number,
                        'subtotal' => $subtotal,
                        'grand_total' => $grandTotal,
                    ],
                ];

                // If QR code was used, update the existing record
                if ($qrUsage) {
                    $qrUsage->update([
                        'order_id' => $order->order_id,
                        'status' => 'completed',
                        'redeemed_at' => now(),
                        'discount_applied' => $discountAmount,
                        'redemption_context' => 'online_order',
                        'applied_to_items' => $request->items,
                    ]);
                } else {
                    // Create new QR usage record
                    QRCodeUsage::create($qrUsageData);
                }

                // Update promotion usage count
                if ($promotion) {
                    $promotion->used_count += 1;
                    $promotion->save();
                }
            }

            // ============================================
            // 10. UPDATE CUSTOMER POINTS
            // ============================================
            if ($pointsRedeemed > 0) {
                $customer->points_balance -= $pointsRedeemed;
            }
            
            // Add earned points (will be credited when order is completed)
            // We'll add points when the order status changes to 'completed'
            
            // If order is already completed (e.g., for pickup), add points immediately
            if ($request->status === 'completed') {
                $customer->points_balance += $pointsEarned;
            }
            
            $customer->save();

            // ============================================
            // 11. UPDATE PHYSICAL CARD IF USED
            // ============================================
            if ($request->physical_card_id) {
                $physicalCard = PhysicalCard::where('card_id', $request->physical_card_id)
                    ->where('merchant_id', $merchant->merchant_id)
                    ->first();
                    
                if ($physicalCard) {
                    // Update card usage
                    $physicalCard->last_used_at = now();
                    $physicalCard->save();
                }
            }

            DB::commit();

            // Load relationships for response
            $order->load(['items', 'items.product', 'promotion']);

            return response()->json([
                'success' => true,
                'data' => [
                    'order' => $order,
                    'order_items' => $order->items,
                    'points_earned' => $pointsEarned,
                    'points_redeemed' => $pointsRedeemed,
                    'discount_applied' => $discountAmount,
                    'tax_amount' => $taxAmount,
                    'delivery_fee' => $deliveryFee,
                    'grand_total' => $grandTotal,
                    'promotion_applied' => $promotion ? [
                        'id' => $promotion->promotion_id,
                        'code' => $promotion->promo_code ?? null,
                        'type' => $promotion->promo_type,
                        'value' => $promotion->value,
                    ] : null,
                ],
                'message' => 'Order created successfully! 🎉'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            // Rollback QR usage if order fails
            if (isset($qrUsage) && $qrUsage) {
                $qrUsage->status = 'pending';
                $qrUsage->order_id = null;
                $qrUsage->save();
            }

            // Restore stock if order fails
            if (isset($orderItemsData)) {
                foreach ($orderItemsData as $itemData) {
                    $product = $itemData['product'];
                    $product->stock_quantity += $itemData['quantity'];
                    $product->in_stock = $product->stock_quantity > 0;
                    $product->save();
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, $orderId)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,preparing,ready,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $merchant = Auth::user()->merchant;

            if (!$merchant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Merchant not found.'
                ], 404);
            }

            $order = Order::where('merchant_id', $merchant->merchant_id)
                ->where('order_id', $orderId)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found.'
                ], 404);
            }

            $oldStatus = $order->status;
            $order->status = $request->status;

            // If order is completed, add points to customer
            if ($request->status === 'completed' && $oldStatus !== 'completed') {
                $customer = $order->customer;
                $customer->points_balance += $order->points_earned;
                $customer->save();

                $order->delivered_at = now();
            }

            // If order is cancelled, restore stock
            if ($request->status === 'cancelled' && $oldStatus !== 'cancelled') {
                $order->cancelled_at = now();
                $order->cancellation_reason = $request->notes;

                // Restore stock for all items
                foreach ($order->items as $item) {
                    if ($item->product_id) {
                        $product = Product::find($item->product_id);
                        if ($product) {
                            $product->stock_quantity += $item->quantity;
                            $product->in_stock = $product->stock_quantity > 0;
                            $product->save();
                        }
                    }
                }
            }

            $order->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'order' => $order,
                    'old_status' => $oldStatus,
                    'new_status' => $order->status,
                ],
                'message' => "Order status updated to: {$order->status}"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order status update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate discount based on promotion
     */
    private function calculateDiscount($subtotal, $promotion, $customDiscount = null)
    {
        if (!$promotion) {
            return 0;
        }

        $discount = 0;

        switch ($promotion->promo_type) {
            case 'percentage':
                $discount = $subtotal * ($promotion->value / 100);
                // Apply max discount if set
                if ($promotion->max_discount) {
                    $discount = min($discount, $promotion->max_discount);
                }
                break;
            case 'fixed':
                $discount = min($promotion->value, $subtotal);
                break;
            case 'bogo':
                // BOGO is handled at item level, return 0 for now
                $discount = 0;
                break;
        }

        return $discount;
    }

    /**
     * Get single order details
     */
    public function show($orderId)
    {
        $merchant = Auth::user()->merchant;

        if (!$merchant) {
            return response()->json([
                'success' => false,
                'message' => 'Merchant not found.'
            ], 404);
        }

        $order = Order::with([
            'items',
            'items.product',
            'items.product.discounts',
            'items.product.points',
            'promotion',
            'customer',
            'qrUsage',
        ])
        ->where('merchant_id', $merchant->merchant_id)
        ->where('order_id', $orderId)
        ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Get all orders for merchant
     */
    public function index(Request $request)
    {
        $merchant = Auth::user()->merchant;

        if (!$merchant) {
            return response()->json([
                'success' => false,
                'message' => 'Merchant not found.'
            ], 404);
        }

        $orders = Order::with(['items.product', 'promotion', 'customer'])
            ->where('merchant_id', $merchant->merchant_id)
            ->when($request->status, function($q) use ($request) {
                return $q->where('status', $request->status);
            })
            ->when($request->date_from, function($q) use ($request) {
                return $q->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->date_to, function($q) use ($request) {
                return $q->whereDate('created_at', '<=', $request->date_to);
            })
            ->when($request->search, function($q) use ($request) {
                return $q->where('order_number', 'LIKE', "%{$request->search}%")
                         ->orWhereHas('customer', function($query) use ($request) {
                             $query->where('firstname', 'LIKE', "%{$request->search}%")
                                   ->orWhere('lastname', 'LIKE', "%{$request->search}%")
                                   ->orWhere('email', 'LIKE', "%{$request->search}%");
                         });
            })
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    /**
     * Cancel order
     */
    public function cancel(Request $request, $orderId)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $merchant = Auth::user()->merchant;

            if (!$merchant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Merchant not found.'
                ], 404);
            }

            $order = Order::where('merchant_id', $merchant->merchant_id)
                ->where('order_id', $orderId)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found.'
                ], 404);
            }

            if (!$order->canBeCancelled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order cannot be cancelled. Current status: ' . $order->status
                ], 422);
            }

            $order->cancel($request->reason);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'order' => $order,
                ],
                'message' => 'Order cancelled successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order cancellation error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order: ' . $e->getMessage()
            ], 500);
        }
    }
}