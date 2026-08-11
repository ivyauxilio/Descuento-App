<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\MenuItem;
use App\Models\Promotion;
use App\Models\QrCodeUsage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MerchantController extends Controller
{
    /**
     * Get the authenticated merchant.
     */
    private function getMerchant()
    {
        $merchant = auth()->user()->merchant;

        if (!$merchant) {
            abort(404, 'Merchant not found');
        }

        return $merchant;
    }

    /**
     * Get merchant profile.
     */
    public function profile()
    {
        $merchant = $this->getMerchant();

        return response()->json([
            'data' => $merchant->load(['owner', 'category', 'province']),
            'message' => 'Merchant profile retrieved successfully',
        ]);
    }

    /**
     * Get merchant statistics.
     */
    public function stats()
    {
        $merchant = $this->getMerchant();

        // Get date ranges
        $today = now()->startOfDay();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        // Calculate statistics
        $stats = [
            // ============================================
            // MENU STATS
            // ============================================
            'total_menu_items' => MenuItem::where('merchant_id', $merchant->merchant_id)->count(),
            'available_items' => MenuItem::where('merchant_id', $merchant->merchant_id)
                ->where('status', 'available')
                ->count(),
            'out_of_stock_items' => MenuItem::where('merchant_id', $merchant->merchant_id)
                ->where('status', 'out_of_stock')
                ->count(),
            'low_stock_items' => MenuItem::where('merchant_id', $merchant->merchant_id)
                ->where('stock_status', 'low_stock')
                ->count(),
            // 'featured_items' => MenuItem::where('merchant_id', $merchant->merchant_id)
            //     ->where('is_featured', true)
            //     ->count(),

            // ============================================
            // PROMOTION STATS
            // ============================================
            'total_promotions' => Promotion::where('merchant_id', $merchant->merchant_id)->count(),
            'active_promotions' => Promotion::where('merchant_id', $merchant->merchant_id)
                ->where('status', 'active')
                ->whereDate('start_date', '<=', now())
                ->where(function ($q) {
                    $q->whereNull('end_date')
                      ->orWhereDate('end_date', '>=', now());
                })
                ->count(),
            'expired_promotions' => Promotion::where('merchant_id', $merchant->merchant_id)
                ->where('status', 'expired')
                ->count(),

            // ============================================
            // QR CODE STATS
            // ============================================
            'total_qr_scans' => QrCodeUsage::where('merchant_id', $merchant->merchant_id)->count(),
            'qr_scans_today' => QrCodeUsage::where('merchant_id', $merchant->merchant_id)
                ->whereDate('scanned_at', today())
                ->count(),
            'qr_scans_this_week' => QrCodeUsage::where('merchant_id', $merchant->merchant_id)
                ->whereDate('scanned_at', '>=', $thisWeek)
                ->count(),

            // ============================================
            // CUSTOMER STATS (Simplified)
            // ============================================
            'total_customers' => $this->getTotalCustomers($merchant->merchant_id),
            'new_customers_today' => $this->getNewCustomersByDate($merchant->merchant_id, $today),
            'new_customers_this_week' => $this->getNewCustomersByDate($merchant->merchant_id, $thisWeek),
            'new_customers_this_month' => $this->getNewCustomersByDate($merchant->merchant_id, $thisMonth),

            // ============================================
            // RATING STATS
            // ============================================
            'average_rating' => $this->getAverageRating($merchant->merchant_id),
            'total_reviews' => $this->getTotalReviews($merchant->merchant_id),

            // ============================================
            // BUSINESS PERFORMANCE
            // ============================================
            // 'top_selling_items' => $this->getTopSellingItems($merchant->merchant_id, 5),
        ];

        return response()->json([
            'data' => $stats,
            'message' => 'Merchant statistics retrieved successfully',
        ]);
    }

    // ============================================
    // PRIVATE HELPER METHODS
    // ============================================

    /**
     * Get total customers - uses QR code scans as interaction.
     */
    private function getTotalCustomers($merchantId)
    {
        // Get unique users who have scanned QR codes for this merchant
        return QrCodeUsage::where('merchant_id', $merchantId)
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id') ?? 0;
    }

    /**
     * Get new customers by date - uses QR code scans.
     */
    private function getNewCustomersByDate($merchantId, $date)
    {
        return QrCodeUsage::where('merchant_id', $merchantId)
            ->whereNotNull('user_id')
            ->whereDate('scanned_at', '>=', $date)
            ->distinct('user_id')
            ->count('user_id') ?? 0;
    }

    /**
     * Get average rating - from reviews table (create if needed).
     */
    private function getAverageRating($merchantId)
    {
        // Check if reviews table exists
        try {
            $rating = DB::table('reviews')
                ->where('merchant_id', $merchantId)
                ->avg('rating');

            return $rating ? round($rating, 2) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get total reviews.
     */
    private function getTotalReviews($merchantId)
    {
        try {
            return DB::table('reviews')
                ->where('merchant_id', $merchantId)
                ->count() ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get top selling items - based on sold_count from menu_items.
     */
    // private function getTopSellingItems($merchantId, $limit = 5)
    // {
    //     return MenuItem::where('merchant_id', $merchantId)
    //         ->where('sold_count', '>', 0)
    //         ->orderBy('sold_count', 'desc')
    //         ->limit($limit)
    //         ->get([
    //             'menu_item_id',
    //             'name',
    //             'price',
    //             'sold_count',
    //             'rating',
    //             'image_url',
    //         ])
    //         ->map(function ($item) {
    //             return [
    //                 'menu_item_id' => $item->menu_item_id,
    //                 'name' => $item->name,
    //                 'price' => $item->price,
    //                 'sold_count' => $item->sold_count ?? 0,
    //                 'rating' => $item->rating,
    //                 'image_url' => $item->image_url,
    //             ];
    //         });
    // }

    // ============================================
    // ORDER-RELATED METHODS (DISABLED FOR NOW)
    // ============================================

    /**
     * Get total revenue.
     * DISABLED - Will be implemented when orders are added
     */
    /*
    private function getTotalRevenue($merchantId)
    {
        return Order::where('merchant_id', $merchantId)
            ->where('status', 'completed')
            ->sum('total') ?? 0;
    }
    */

    /**
     * Get total orders.
     * DISABLED - Will be implemented when orders are added
     */
    /*
    private function getTotalOrders($merchantId)
    {
        return Order::where('merchant_id', $merchantId)->count();
    }
    */

    /**
     * Get orders by status.
     * DISABLED - Will be implemented when orders are added
     */
    /*
    private function getOrdersByStatus($merchantId, $status)
    {
        return Order::where('merchant_id', $merchantId)
            ->where('status', $status)
            ->count();
    }
    */

    /**
     * Get returning customers.
     * DISABLED - Will be implemented when orders are added
     */
    /*
    private function getReturningCustomers($merchantId)
    {
        return User::where('role', 'customer')
            ->whereHas('orders', function ($q) use ($merchantId) {
                $q->where('merchant_id', $merchantId);
            })
            ->havingRaw('COUNT(orders.id) > 1')
            ->count();
    }
    */

    /**
     * Get recent orders.
     * DISABLED - Will be implemented when orders are added
     */
    /*
    private function getRecentOrders($merchantId, $limit = 5)
    {
        return Order::where('merchant_id', $merchantId)
            ->with(['user'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number ?? '#ORD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
                    'customer' => $order->user ? $order->user->name : 'Guest',
                    'total' => $order->total,
                    'status' => $order->status,
                    'items' => $order->items_count ?? 0,
                    'created_at' => $order->created_at->diffForHumans(),
                ];
            });
    }
    */

    /**
     * Get average order value.
     * DISABLED - Will be implemented when orders are added
     */
    /*
    private function getAverageOrderValue($merchantId)
    {
        $totalRevenue = $this->getTotalRevenue($merchantId);
        $totalOrders = $this->getTotalOrders($merchantId);

        if ($totalOrders == 0) {
            return 0;
        }

        return round($totalRevenue / $totalOrders, 2);
    }
    */

    /**
     * Get conversion rate.
     * DISABLED - Will be implemented when orders are added
     */
    /*
    private function getConversionRate($merchantId)
    {
        $totalVisitors = 1000;
        $totalOrders = $this->getTotalOrders($merchantId);

        if ($totalVisitors == 0) {
            return 0;
        }

        return round(($totalOrders / $totalVisitors) * 100, 2);
    }
    */
}