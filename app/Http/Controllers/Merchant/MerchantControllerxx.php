<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\MenuItem;
use App\Models\Promotion;
use App\Models\QrCodeUsage;
use App\Models\Order;
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
        $lastMonth = now()->subMonth()->startOfMonth();

        // Calculate statistics
        $stats = [
            // Revenue Stats
            // 'total_revenue' => $this->getTotalRevenue($merchant->merchant_id),
            'revenue_today' => $this->getRevenueByDate($merchant->merchant_id, $today),
            'revenue_this_week' => $this->getRevenueByDate($merchant->merchant_id, $thisWeek),
            'revenue_this_month' => $this->getRevenueByDate($merchant->merchant_id, $thisMonth),
            'revenue_last_month' => $this->getRevenueByDate($merchant->merchant_id, $lastMonth),
            'revenue_growth' => $this->getRevenueGrowth($merchant->merchant_id),

            // Order Stats
            // 'total_orders' => $this->getTotalOrders($merchant->merchant_id),
            // 'orders_today' => $this->getOrdersByDate($merchant->merchant_id, $today),
            // 'orders_this_week' => $this->getOrdersByDate($merchant->merchant_id, $thisWeek),
            // 'orders_this_month' => $this->getOrdersByDate($merchant->merchant_id, $thisMonth),
            // 'pending_orders' => $this->getOrdersByStatus($merchant->merchant_id, 'pending'),
            // 'completed_orders' => $this->getOrdersByStatus($merchant->merchant_id, 'completed'),
            // 'cancelled_orders' => $this->getOrdersByStatus($merchant->merchant_id, 'cancelled'),

            // // Customer Stats
            // 'total_customers' => $this->getTotalCustomers($merchant->merchant_id),
            // 'new_customers_today' => $this->getNewCustomersByDate($merchant->merchant_id, $today),
            // 'new_customers_this_week' => $this->getNewCustomersByDate($merchant->merchant_id, $thisWeek),
            // 'new_customers_this_month' => $this->getNewCustomersByDate($merchant->merchant_id, $thisMonth),
            // 'returning_customers' => $this->getReturningCustomers($merchant->merchant_id),

            // Menu Stats
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
            'featured_items' => MenuItem::where('merchant_id', $merchant->merchant_id)
                ->where('is_featured', true)
                ->count(),

            // Promotion Stats
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

            // QR Code Stats
            'total_qr_scans' => QrCodeUsage::where('merchant_id', $merchant->merchant_id)->count(),
            'qr_scans_today' => QrCodeUsage::where('merchant_id', $merchant->merchant_id)
                ->whereDate('scanned_at', today())
                ->count(),
            'qr_scans_this_week' => QrCodeUsage::where('merchant_id', $merchant->merchant_id)
                ->whereDate('scanned_at', '>=', $thisWeek)
                ->count(),

            // Rating Stats
            'average_rating' => $this->getAverageRating($merchant->merchant_id),
            'total_reviews' => $this->getTotalReviews($merchant->merchant_id),

            // Business Performance
            // 'avg_order_value' => $this->getAverageOrderValue($merchant->merchant_id),
            // 'conversion_rate' => $this->getConversionRate($merchant->merchant_id),
            // 'top_selling_items' => $this->getTopSellingItems($merchant->merchant_id, 5),
            // 'recent_orders' => $this->getRecentOrders($merchant->merchant_id, 5),
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
     * Get total revenue.
     */
    private function getTotalRevenue($merchantId)
    {
        // Assuming you have an orders table with merchant_id and total
        return Order::where('merchant_id', $merchantId)
            ->where('status', 'completed')
            ->sum('total') ?? 0;
    }

    /**
     * Get revenue by date.
     */
    private function getRevenueByDate($merchantId, $date)
    {
        return Order::where('merchant_id', $merchantId)
            ->where('status', 'completed')
            ->where('created_at', '>=', $date)
            ->sum('total') ?? 0;
    }

    /**
     * Get revenue growth percentage.
     */
    private function getRevenueGrowth($merchantId)
    {
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        $currentMonthRevenue = $this->getRevenueByDate($merchantId, $thisMonth);
        $lastMonthRevenue = $this->getRevenueByDate($merchantId, $lastMonth);

        if ($lastMonthRevenue == 0) {
            return $currentMonthRevenue > 0 ? 100 : 0;
        }

        return round((($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 2);
    }

    /**
     * Get total orders.
     */
    private function getTotalOrders($merchantId)
    {
        return Order::where('merchant_id', $merchantId)->count();
    }

    /**
     * Get orders by date.
     */
    private function getOrdersByDate($merchantId, $date)
    {
        return Order::where('merchant_id', $merchantId)
            ->where('created_at', '>=', $date)
            ->count();
    }

    /**
     * Get orders by status.
     */
    private function getOrdersByStatus($merchantId, $status)
    {
        return Order::where('merchant_id', $merchantId)
            ->where('status', $status)
            ->count();
    }

    /**
     * Get total customers.
     */
    private function getTotalCustomers($merchantId)
    {
        // Assuming you have a customers table or use users with role customer
        return User::where('role', 'customer')
            ->whereHas('orders', function ($q) use ($merchantId) {
                $q->where('merchant_id', $merchantId);
            })
            ->distinct()
            ->count();
    }

    /**
     * Get new customers by date.
     */
    private function getNewCustomersByDate($merchantId, $date)
    {
        return User::where('role', 'customer')
            ->whereHas('orders', function ($q) use ($merchantId, $date) {
                $q->where('merchant_id', $merchantId)
                  ->where('created_at', '>=', $date);
            })
            ->distinct()
            ->count();
    }

    /**
     * Get returning customers.
     */
    private function getReturningCustomers($merchantId)
    {
        return User::where('role', 'customer')
            ->whereHas('orders', function ($q) use ($merchantId) {
                $q->where('merchant_id', $merchantId);
            })
            ->havingRaw('COUNT(orders.id) > 1')
            ->count();
    }

    /**
     * Get average rating.
     */
    private function getAverageRating($merchantId)
    {
        // Assuming you have a reviews table
        $rating = DB::table('reviews')
            ->where('merchant_id', $merchantId)
            ->avg('rating');

        return $rating ? round($rating, 2) : null;
    }

    /**
     * Get total reviews.
     */
    private function getTotalReviews($merchantId)
    {
        return DB::table('reviews')
            ->where('merchant_id', $merchantId)
            ->count();
    }

    /**
     * Get average order value.
     */
    private function getAverageOrderValue($merchantId)
    {
        $totalRevenue = $this->getTotalRevenue($merchantId);
        $totalOrders = $this->getTotalOrders($merchantId);

        if ($totalOrders == 0) {
            return 0;
        }

        return round($totalRevenue / $totalOrders, 2);
    }

    /**
     * Get conversion rate.
     */
    private function getConversionRate($merchantId)
    {
        // This is a simplified calculation
        $totalVisitors = 1000; // This would come from analytics
        $totalOrders = $this->getTotalOrders($merchantId);

        if ($totalVisitors == 0) {
            return 0;
        }

        return round(($totalOrders / $totalVisitors) * 100, 2);
    }

    /**
     * Get top selling items.
     */
    private function getTopSellingItems($merchantId, $limit = 5)
    {
        // Assuming you have order_items table
        return DB::table('order_items')
            ->join('menu_items', 'order_items.menu_item_id', '=', 'menu_items.menu_item_id')
            ->where('menu_items.merchant_id', $merchantId)
            ->select(
                'menu_items.name',
                DB::raw('COUNT(order_items.id) as total_orders'),
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy('menu_items.name')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent orders.
     */
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
}