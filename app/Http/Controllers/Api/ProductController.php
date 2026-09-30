<?php
// app/Http/Controllers/Api/ProductController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    /**
     * GET /api/products
     * List all products with filters (category, search, sort, featured)
     */
    // public function index(Request $request)
    // {
    //     $request->validate([
    //         'page' => 'nullable|integer|min:1',
    //         'per_page' => 'nullable|integer|min:1|max:100',
    //         'category' => 'nullable|string|max:100',
    //         'search' => 'nullable|string|max:255',
    //         'featured' => 'nullable|boolean',
    //         'merchant_id' => 'nullable|uuid',
    //         'sort' => 'nullable|in:price_asc,price_desc,name_asc,name_desc,newest,popular',
    //     ]);

    //     try {
    //         $user = auth()->user();

    //         if (!$user) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Unauthenticated.',
    //             ], 401);
    //         }

    //         $query = Product::query()
    //             ->where('is_active', true)
    //             ->with(['merchant:merchant_id,business_name,business_logo'])
    //             ->with([
    //                 'discounts' => function ($q) {
    //                     $q->where('is_active', true)
    //                       ->where('start_date', '<=', now())
    //                       ->where(function ($sub) {
    //                           $sub->where('end_date', '>=', now())
    //                               ->orWhereNull('end_date');
    //                       });
    //                 },
    //                 'points' => function ($q) {
    //                     $q->where('is_active', true);
    //                 },
    //             ]);

    //         // Filter by merchant (optional — if the app is multi-merchant)
    //         if ($request->filled('merchant_id')) {
    //             $query->where('merchant_id', $request->merchant_id);
    //         }

    //         // Filter by category
    //         if ($request->filled('category') && $request->category !== 'all') {
    //             $query->where('category', $request->category);
    //         }

    //         // Search by name, brand, or SKU
    //         if ($request->filled('search')) {
    //             $search = $request->search;
    //             $query->where(function ($q) use ($search) {
    //                 $q->where('name', 'LIKE', "%{$search}%")
    //                   ->orWhere('brand', 'LIKE', "%{$search}%")
    //                   ->orWhere('sku', 'LIKE', "%{$search}%")
    //                   ->orWhere('description', 'LIKE', "%{$search}%");
    //             });
    //         }

    //         // Featured only
    //         if ($request->boolean('featured')) {
    //             $query->where('is_featured', true);
    //         }

    //         // Show only in-stock (default true for customer app)
    //         // Uncomment to hide out-of-stock entirely:
    //         // $query->where('stock_quantity', '>', 0);

    //         // Sorting
    //         $sort = $request->get('sort', 'newest');
    //         match ($sort) {
    //             'price_asc' => $query->orderBy('price', 'asc'),
    //             'price_desc' => $query->orderBy('price', 'desc'),
    //             'name_asc' => $query->orderBy('name', 'asc'),
    //             'name_desc' => $query->orderBy('name', 'desc'),
    //             'popular' => $query->orderBy('total_sold', 'desc'),
    //             default => $query->orderBy('created_at', 'desc'),
    //         };

    //         $perPage = (int) ($request->per_page ?? 20);
    //         $products = $query->paginate($perPage);

    //         // Transform for mobile app
    //         $items = collect($products->items())->map(function ($product) {
    //             return $this->transform($product);
    //         });

    //         return response()->json([
    //             'success' => true,
    //             'data' => [
    //                 'data' => $items,
    //                 'current_page' => $products->currentPage(),
    //                 'last_page' => $products->lastPage(),
    //                 'per_page' => $products->perPage(),
    //                 'total' => $products->total(),
    //             ],
    //         ]);

    //     } catch (\Exception $e) {
    //         Log::error('Product index error: ' . $e->getMessage());

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to load products.',
    //         ], 500);
    //     }
    // }
    public function index(Request $request)
    {
        $request->validate([
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
            'category' => 'nullable|string|max:100',
            'search' => 'nullable|string|max:255',
            'featured' => 'nullable|boolean',
            'merchant_id' => 'nullable|uuid',
            'sort' => 'nullable|in:price_asc,price_desc,name_asc,name_desc,newest,popular',
        ]);

        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            $query = Product::query()
                ->where('is_active', true)
                ->with([
                    // 'merchant:merchant_id,business_name,business_logo',
                    'merchant:merchant_id,business_name',
                    'discounts' => function ($q) {
                        $q->where('is_active', true)
                            ->where('start_date', '<=', now())
                            ->where(function ($sub) {
                                $sub->where('end_date', '>=', now())
                                    ->orWhereNull('end_date');
                            });
                    },
                    'points' => function ($q) {
                        $q->where('is_active', true);
                    },
                ]);

            // Merchant
            if ($request->filled('merchant_id')) {
                $query->where(
                    'merchant_id',
                    $request->merchant_id
                );
            }

            // Category
            if (
                $request->filled('category') &&
                $request->category !== 'all'
            ) {
                $query->where(
                    'category',
                    $request->category
                );
            }

            // Search
            if ($request->filled('search')) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('brand', 'LIKE', "%{$search}%")
                        ->orWhere('sku', 'LIKE', "%{$search}%")
                        ->orWhere(
                            'description',
                            'LIKE',
                            "%{$search}%"
                        );
                });
            }

            // Featured
            if ($request->boolean('featured')) {
                $query->where('is_featured', true);
            }

            // Sorting
            $sort = $request->get('sort', 'newest');

            switch ($sort) {
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;

                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;

                case 'name_asc':
                    $query->orderBy('name', 'asc');
                    break;

                case 'name_desc':
                    $query->orderBy('name', 'desc');
                    break;

                case 'popular':
                    $query->orderBy('total_sold', 'desc');
                    break;

                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }

            $perPage = (int) ($request->per_page ?? 20);

            $products = $query->paginate($perPage);

            $items = collect($products->items())
                ->map(function ($product) {
                    return $this->transform($product);
                })
                ->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'data' => $items,
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ],
            ]);

        } catch (\Throwable $e) {

            Log::error('Product index error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load products.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * GET /api/products/{id}
     * Show a single product with full details
     */
    public function show($id)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            $product = Product::query()
                ->where('product_id', $id)
                ->orWhere('slug', $id)
                ->where('is_active', true)
                ->with([
                    'merchant:merchant_id,business_name,street_address,city',
                    'discounts' => function ($q) {
                        $q->where('is_active', true)
                          ->where('start_date', '<=', now())
                          ->where(function ($sub) {
                              $sub->where('end_date', '>=', now())
                                  ->orWhereNull('end_date');
                          });
                    },
                    'points' => function ($q) {
                        $q->where('is_active', true);
                    },
                ])
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.',
                ], 404);
            }

            // Get related products from same category
            $related = Product::where('merchant_id', $product->merchant_id)
                ->where('product_id', '!=', $product->product_id)
                ->where('category', $product->category)
                ->where('is_active', true)
                ->inStock()
                ->limit(6)
                ->get()
                ->map(fn ($p) => $this->transform($p));

            return response()->json([
                'success' => true,
                'data' => [
                    'product' => $this->transform($product, true),
                    'related_products' => $related,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Product show error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load product.',
            ], 500);
        }
    }

    /**
     * GET /api/products/categories
     * List all unique categories (for filter chips)
     */
    public function categories(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            // Cache for 1 hour
            $cacheKey = 'product_categories_' . ($request->merchant_id ?? 'all');

            $categories = Cache::remember($cacheKey, 3600, function () use ($request) {
                $query = Product::where('is_active', true);

                if ($request->filled('merchant_id')) {
                    $query->where('merchant_id', $request->merchant_id);
                }

                return $query->select('category')
                    ->distinct()
                    ->whereNotNull('category')
                    ->where('category', '!=', '')
                    ->orderBy('category')
                    ->pluck('category')
                    ->values();
            });

            return response()->json([
                'success' => true,
                'data' => $categories,
            ]);

        } catch (\Exception $e) {
            Log::error('Categories error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load categories.',
            ], 500);
        }
    }

    /**
     * GET /api/products/featured
     * List featured products only (for the home page carousel)
     */
    public function featured(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            $query = Product::query()
                ->where('is_active', true)
                ->where('is_featured', true)
                ->inStock()
                ->with([
                    'discounts' => function ($q) {
                        $q->where('is_active', true)
                          ->where('start_date', '<=', now())
                          ->where(function ($sub) {
                              $sub->where('end_date', '>=', now())
                                  ->orWhereNull('end_date');
                          });
                    },
                ]);

            if ($request->filled('merchant_id')) {
                $query->where('merchant_id', $request->merchant_id);
            }

            $limit = (int) ($request->limit ?? 8);
            $products = $query->latest()->limit($limit)->get();

            $items = $products->map(fn ($p) => $this->transform($p));

            return response()->json([
                'success' => true,
                'data' => $items,
            ]);

        } catch (\Exception $e) {
            Log::error('Featured products error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load featured products.',
            ], 500);
        }
    }

    /**
     * GET /api/products/search
     * Dedicated search endpoint (for autocomplete)
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
            'limit' => 'nullable|integer|min:1|max:20',
        ]);

        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            $q = $request->q;
            $limit = (int) ($request->limit ?? 10);

            $products = Product::query()
                ->where('is_active', true)
                ->where(function ($query) use ($q) {
                    $query->where('name', 'LIKE', "%{$q}%")
                          ->orWhere('brand', 'LIKE', "%{$q}%")
                          ->orWhere('category', 'LIKE', "%{$q}%");
                })
                ->inStock()
                ->limit($limit)
                ->get()
                ->map(fn ($p) => $this->transform($p));

            return response()->json([
                'success' => true,
                'data' => $products,
            ]);

        } catch (\Exception $e) {
            Log::error('Search error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Search failed.',
            ], 500);
        }
    }

    /**
     * Transform a product into the mobile-app-friendly payload.
     */
    private function transform(Product $product, bool $detail = false): array
    {
        $price = (float) $product->price;
        $activeDiscount = $product->discounts->first();

        $discountedPrice = $price;
        $discountLabel = null;
        $discountPercentage = 0;

        if ($activeDiscount) {
            if ($activeDiscount->type === 'percentage') {
                $discountedPrice = $price - ($price * $activeDiscount->value / 100);
                $discountPercentage = (float) $activeDiscount->value;
                $discountLabel = "{$activeDiscount->value}% OFF";
            } elseif ($activeDiscount->type === 'fixed') {
                $discountedPrice = max(0, $price - (float) $activeDiscount->value);
                $discountLabel = "₱{$activeDiscount->value} OFF";
            } elseif ($activeDiscount->type === 'bogo') {
                $discountLabel = 'BUY 1 GET 1';
            }
        }

        // Points
        $pointsPerItem = $product->points?->points_per_item ?? 0;

        // Stock
        $stock = (int) $product->stock_quantity;
        $stockStatus = match (true) {
            $stock <= 0 => 'out_of_stock',
            $stock <= 5 => 'low_stock',
            default => 'in_stock',
        };

        $data = [
            'product_id' => $product->product_id,
            'slug' => $product->slug,
            'name' => $product->name,
            'description' => $detail ? $product->description : null,
            'category' => $product->category,
            'sub_category' => $product->sub_category,
            'brand' => $product->brand,
            'unit' => $product->unit,
            'unit_value' => $product->unit_value,
            'weight' => $product->weight,
            'country_of_origin' => $product->country_of_origin,
            'nutritional_info' => $detail ? $product->nutritional_info : null,

            // Pricing
            'price' => round($price, 2),
            'discounted_price' => round($discountedPrice, 2),
            'has_discount' => $activeDiscount !== null,
            'discount_label' => $discountLabel,
            'discount_percentage' => $discountPercentage,
            'savings' => round($price - $discountedPrice, 2),

            // Media
            'image_url' => $product->image_url,
            'images' => $product->images ?? [],

            // Stock
            'stock_quantity' => $stock,
            'stock_status' => $stockStatus,
            'is_low_stock' => $stockStatus === 'low_stock',
            'is_out_of_stock' => $stockStatus === 'out_of_stock',

            // Points
            'points_per_item' => $pointsPerItem,

            // Flags
            'is_featured' => (bool) $product->is_featured,
            'is_active' => (bool) $product->is_active,

            // Merchant (minimal)
            'merchant' => $product->merchant ? [
                'merchant_id' => $product->merchant->merchant_id,
                'business_name' => $product->merchant->business_name,
                // 'business_logo' => $product->merchant->business_logo,
            ] : null,
        ];

        // Detail-only fields
        if ($detail) {
            $data['created_at'] = $product->created_at?->toIso8601String();
            $data['updated_at'] = $product->updated_at?->toIso8601String();
            $data['attributes'] = $product->attributes ?? [];
            $data['barcode'] = $product->barcode;

            if ($product->merchant) {
                $data['merchant']['business_address'] = $product->merchant->business_address ?? null;
                $data['merchant']['city'] = $product->merchant->city ?? null;
            }
        }

        return $data;
    }
}