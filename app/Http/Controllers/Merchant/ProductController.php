<?php
// app/Http/Controllers/Merchant/ProductController.php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductDiscount;
use App\Models\ProductPoints;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Get all products for the merchant with filters
     */
    public function index(Request $request)
    {
        try {
            $merchant = auth()->user()->merchant;

            if (!$merchant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Merchant not found.'
                ], 404);
            }

            $products = Product::with([
                'discounts' => function($q) {
                    $q->active();
                },
                'points'
            ])
            ->where('merchant_id', $merchant->merchant_id)
            ->when($request->search, function($q) use ($request) {
                return $q->where(function($query) use ($request) {
                    $query->where('name', 'LIKE', "%{$request->search}%")
                          ->orWhere('sku', 'LIKE', "%{$request->search}%")
                          ->orWhere('barcode', 'LIKE', "%{$request->search}%")
                          ->orWhere('brand', 'LIKE', "%{$request->search}%");
                });
            })
            ->when($request->category, function($q) use ($request) {
                return $q->where('category', $request->category);
            })
            ->when($request->status, function($q) use ($request) {
                if ($request->status === 'active') {
                    return $q->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    return $q->where('is_active', false);
                }
                return $q;
            })
            ->when($request->min_price, function($q) use ($request) {
                return $q->where('price', '>=', $request->min_price);
            })
            ->when($request->max_price, function($q) use ($request) {
                return $q->where('price', '<=', $request->max_price);
            })
            ->when($request->stock_status, function($q) use ($request) {
                if ($request->stock_status === 'in_stock') {
                    return $q->where('in_stock', true)->where('stock_quantity', '>', 0);
                } elseif ($request->stock_status === 'out_of_stock') {
                    return $q->where('stock_quantity', 0);
                } elseif ($request->stock_status === 'low_stock') {
                    return $q->whereColumn('stock_quantity', '<=', 'min_stock_alert')
                             ->where('stock_quantity', '>', 0);
                }
                return $q;
            })
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc')
            ->paginate($request->per_page ?? 20);

            return response()->json([
                'success' => true,
                'data' => $products,
            ]);

        } catch (\Exception $e) {
            Log::error('Product index error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get product statistics
     */
    public function stats(Request $request)
    {
        try {
            $merchant = auth()->user()->merchant;

            if (!$merchant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Merchant not found.'
                ], 404);
            }

            $stats = [
                'total' => Product::where('merchant_id', $merchant->merchant_id)->count(),
                'in_stock' => Product::where('merchant_id', $merchant->merchant_id)
                    ->where('in_stock', true)
                    ->where('stock_quantity', '>', 0)
                    ->count(),
                'low_stock' => Product::where('merchant_id', $merchant->merchant_id)
                    ->whereColumn('stock_quantity', '<=', 'min_stock_alert')
                    ->where('stock_quantity', '>', 0)
                    ->count(),
                'out_of_stock' => Product::where('merchant_id', $merchant->merchant_id)
                    ->where('stock_quantity', 0)
                    ->count(),
                'active_discounts' => ProductDiscount::where('merchant_id', $merchant->merchant_id)
                    ->where('is_active', true)
                    ->where('start_date', '<=', now())
                    ->where(function($q) {
                        $q->where('end_date', '>=', now())
                          ->orWhereNull('end_date');
                    })
                    ->count(),
                'featured' => Product::where('merchant_id', $merchant->merchant_id)
                    ->where('is_featured', true)
                    ->count(),
                'inactive' => Product::where('merchant_id', $merchant->merchant_id)
                    ->where('is_active', false)
                    ->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);

        } catch (\Exception $e) {
            Log::error('Product stats error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load stats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all categories
     */
    public function categories(Request $request)
    {
        try {
            $merchant = auth()->user()->merchant;

            if (!$merchant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Merchant not found.'
                ], 404);
            }

            $categories = Product::where('merchant_id', $merchant->merchant_id)
                ->distinct()
                ->pluck('category')
                ->filter()
                ->values();

            return response()->json([
                'success' => true,
                'data' => $categories,
            ]);

        } catch (\Exception $e) {
            Log::error('Categories error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load categories: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new product
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'sub_category' => 'nullable|string|max:100',
            'brand' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'unit' => 'required|string|in:piece,kg,gram,liter,ml,dozen,box,pack,bottle,can',
            'unit_value' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock_alert' => 'nullable|integer|min:0',
            'weight' => 'nullable|string|max:50',
            'country_of_origin' => 'nullable|string|max:100',
            'nutritional_info' => 'nullable|string',
            'attributes' => 'nullable|array',
            'is_featured' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'discount' => 'nullable|array',
            'discount.type' => 'required_with:discount|in:percentage,fixed,bogo',
            'discount.value' => 'required_with:discount|numeric|min:0',
            'discount.min_quantity' => 'nullable|integer|min:1',
            'discount.max_quantity' => 'nullable|integer|min:1',
            'discount.start_date' => 'required_with:discount|date',
            'discount.end_date' => 'required_with:discount|date|after:discount.start_date',
            'discount.usage_limit' => 'nullable|integer|min:1',
            'points' => 'nullable|array',
            'points.points_per_item' => 'required_with:points|integer|min:0',
            'points.points_per_php' => 'nullable|integer|min:0',
            'points.min_spend' => 'nullable|numeric|min:0',
            'points.max_points' => 'nullable|integer|min:1',
            'points.start_date' => 'required_with:points|date',
            'points.end_date' => 'nullable|date|after:points.start_date',
        ]);

        try {
            DB::beginTransaction();

            $merchant = auth()->user()->merchant;

            if (!$merchant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Merchant not found.'
                ], 404);
            }

            // Prepare product data
            $data = $request->except(['image', 'discount', 'points', 'attributes']);
            $data['merchant_id'] = $merchant->merchant_id;
            $data['in_stock'] = $request->stock_quantity > 0;
            $data['slug'] = Str::slug($request->name) . '-' . Str::random(6);
            
            // Generate SKU if not provided
            if (empty($data['sku'])) {
                $data['sku'] = strtoupper(Str::random(8));
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $filename = time() . '_' . $image->getClientOriginalName();
                $path = $image->storeAs('products', $filename, 'public');
                $data['image_url'] = Storage::url($path);
            }

            // Handle attributes
            // if ($request->has('attributes')) {
            //     $data['attributes'] = json_decode($request->attributes, true) ?? $request->attributes;
            // }
            if ($request->has('attributes')) {
                $attributes = $request->attributes;
            if (is_string($attributes)) {
                $decoded = json_decode($attributes, true);
                $data['attributes'] = is_array($decoded) ? $decoded : [];
            } else {
                $data['attributes'] = $attributes ?? [];
            }
        }

            // Create product
            $product = Product::create($data);

            // Handle discount
            if ($request->has('discount') && !empty($request->discount['value'])) {
                $discountData = $request->discount;
                $discountData['product_id'] = $product->product_id;
                $discountData['merchant_id'] = $merchant->merchant_id;
                $discountData['is_active'] = true;
                
                ProductDiscount::create($discountData);
            }

            // Handle points
            if ($request->has('points') && !empty($request->points['points_per_item'])) {
                $pointsData = $request->points;
                $pointsData['product_id'] = $product->product_id;
                $pointsData['merchant_id'] = $merchant->merchant_id;
                $pointsData['is_active'] = true;
                
                ProductPoints::create($pointsData);
            }

            DB::commit();

            // Load relationships
            $product->load(['discounts', 'points']);

            return response()->json([
                'success' => true,
                'data' => $product,
                'message' => 'Product created successfully.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product creation error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a single product
     */
    public function show($id)
    {
        try {
            $merchant = auth()->user()->merchant;

            if (!$merchant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Merchant not found.'
                ], 404);
            }

            $product = Product::with([
                'discounts' => function($q) {
                    $q->orderBy('is_active', 'desc')->orderBy('created_at', 'desc');
                },
                'points',
                'orderItems' => function($q) {
                    $q->whereHas('order', function($query) {
                        $query->where('status', 'completed');
                    });
                }
            ])
            ->where('merchant_id', $merchant->merchant_id)
            ->where('product_id', $id)
            ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.'
                ], 404);
            }

             \Log::info('Product discounts:', [
                'product_id' => $product->product_id,
                'discounts_count' => $product->discounts->count(),
                'discounts' => $product->discounts->toArray(),
            ]);

            // Add additional stats
            $product->total_sold = $product->orderItems->sum('quantity');
            $product->revenue = $product->orderItems->sum('subtotal');

            return response()->json([
                'success' => true,
                'data' => $product,
            ]);

        } catch (\Exception $e) {
            Log::error('Product show error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a product
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'category' => 'sometimes|string|max:100',
            'sub_category' => 'nullable|string|max:100',
            'brand' => 'nullable|string|max:100',
            'price' => 'sometimes|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'unit' => 'sometimes|string|in:piece,kg,gram,liter,ml,dozen,box,pack,bottle,can',
            'unit_value' => 'nullable|numeric|min:0',
            'stock_quantity' => 'sometimes|integer|min:0',
            'min_stock_alert' => 'nullable|integer|min:0',
            'weight' => 'nullable|string|max:50',
            'country_of_origin' => 'nullable|string|max:100',
            'nutritional_info' => 'nullable|string',
            // 'attributes' => 'nullable|array',
            'is_featured' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'discount' => 'nullable|array',
            'discount.type' => 'required_with:discount|in:percentage,fixed,bogo',
            'discount.value' => 'required_with:discount|numeric|min:0',
            'discount.start_date' => 'required_with:discount|date',
            'discount.end_date' => 'nullable|date|after:discount.start_date',
            'points' => 'nullable|array',
            'points.points_per_item' => 'required_with:points|integer|min:0',
            'points.start_date' => 'required_with:points|date',
        ]);

        try {
            DB::beginTransaction();

            $merchant = auth()->user()->merchant;

            if (!$merchant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Merchant not found.'
                ], 404);
            }

            $product = Product::where('merchant_id', $merchant->merchant_id)
                ->where('product_id', $id)
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.'
                ], 404);
            }

            // Get data except image, discount, points
            // $data = $request->except(['image', 'discount', 'points', 'attributes', '_method']);
            
            
            if ($request->filled('is_featured')) {
                $data['is_featured'] = filter_var($request->is_featured, FILTER_VALIDATE_BOOLEAN);
            } 
            if ($request->filled('is_active')) {
                $data['is_active'] = filter_var($request->is_featured, FILTER_VALIDATE_BOOLEAN);
            } 

            // Update in_stock based on stock quantity
            if ($request->has('stock_quantity')) {
                $data['in_stock'] = $request->stock_quantity > 0;
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($product->image_url) {
                    $oldPath = str_replace('/storage', '', $product->image_url);
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }

                $image = $request->file('image');
                $filename = time() . '_' . $image->getClientOriginalName();
                $path = $image->storeAs('products', $filename, 'public');
                $data['image_url'] = $path;
            }

            // Handle attributes
            if ($request->has('attributes')) {
                $attributes = $request->attributes;
                if (is_string($attributes)) {
                    $attributes = json_decode($attributes, true);
                }
                $data['attributes'] = $attributes;
            }

             \Log::info('Updating product', ['data' => $data]);
            // Update product
            $product->update($data);

            // Handle discount
            if ($request->has('discount') && !empty($request->discount['value'])) {
                // Deactivate old discounts
                ProductDiscount::where('product_id', $product->product_id)
                    ->update(['is_active' => false]);

                $discountData = $request->discount;
                // $discountData['product_id'] = $product->product_id;
                // $discountData['merchant_id'] = $merchant->merchant_id;
                
                           // Prepare new discount data
                $newDiscount = [
                    'product_id' => $product->product_id,
                    'merchant_id' => $merchant->merchant_id,
                    'type' => $discountData['type'] ?? 'percentage',
                    'value' => $discountData['value'],
                    'min_quantity' => $discountData['min_quantity'] ?? 1,
                    'max_quantity' => !empty($discountData['max_quantity']) ? $discountData['max_quantity'] : null,
                    'start_date' => $discountData['start_date'],
                    'end_date' => $discountData['end_date'],
                    'is_active' => isset($discountData['is_active']) 
                        ? filter_var($discountData['is_active'], FILTER_VALIDATE_BOOLEAN) 
                        : true,
                    'usage_limit' => !empty($discountData['usage_limit']) ? $discountData['usage_limit'] : null,
                    'used_count' => 0,
                ];
                \Log::info('Creating discount:', $newDiscount);
                // ProductDiscount::create($discountData);
                 $createdDiscount = ProductDiscount::create($newDiscount);
            } elseif ($request->has('discount') && empty($request->discount['value'])) {
                // Deactivate discount if value is empty
                ProductDiscount::where('product_id', $product->product_id)
                    ->update(['is_active' => false]);
            }

            // Handle points
            if ($request->has('points') && !empty($request->points['points_per_item'])) {
                // Deactivate old points
                ProductPoints::where('product_id', $product->product_id)
                    ->update(['is_active' => false]);

                $pointsData = $request->points;
                $pointsData['product_id'] = $product->product_id;
                $pointsData['merchant_id'] = $merchant->merchant_id;
                
                ProductPoints::create($pointsData);
            }

            DB::commit();

            $product->load(['discounts' => function($q) {
                $q->where('is_active', true);
            }, 'points' => function($q) {
                $q->where('is_active', true);
            }]);

            return response()->json([
                'success' => true,
                'data' => $product,
                'message' => 'Product updated successfully.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product update error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update product: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Delete a product
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $merchant = auth()->user()->merchant;

            if (!$merchant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Merchant not found.'
                ], 404);
            }

            $product = Product::where('merchant_id', $merchant->merchant_id)
                ->where('product_id', $id)
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.'
                ], 404);
            }

            // Check if product has orders
            if ($product->orderItems()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete product with existing orders.'
                ], 422);
            }

            // Delete image
            if ($product->image_url) {
                $oldPath = str_replace('/storage', '', $product->image_url);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Delete related records
            $product->discounts()->delete();
            $product->points()->delete();
            $product->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product deletion error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle product status
     */
    public function toggleStatus($id)
    {
        try {
            DB::beginTransaction();

            $merchant = auth()->user()->merchant;

            if (!$merchant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Merchant not found.'
                ], 404);
            }

            $product = Product::where('merchant_id', $merchant->merchant_id)
                ->where('product_id', $id)
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.'
                ], 404);
            }

            $product->is_active = !$product->is_active;
            $product->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'product_id' => $product->product_id,
                    'is_active' => $product->is_active,
                ],
                'message' => $product->is_active ? 'Product activated.' : 'Product deactivated.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Toggle status error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured($id)
    {
        try {
            DB::beginTransaction();

            $merchant = auth()->user()->merchant;

            if (!$merchant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Merchant not found.'
                ], 404);
            }

            $product = Product::where('merchant_id', $merchant->merchant_id)
                ->where('product_id', $id)
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.'
                ], 404);
            }

            $product->is_featured = !$product->is_featured;
            $product->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'product_id' => $product->product_id,
                    'is_featured' => $product->is_featured,
                ],
                'message' => $product->is_featured ? 'Product featured.' : 'Product unfeatured.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Toggle featured error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle featured: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duplicate a product
     */
    public function duplicate($id)
    {
        try {
            DB::beginTransaction();

            $merchant = auth()->user()->merchant;

            if (!$merchant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Merchant not found.'
                ], 404);
            }

            $product = Product::where('merchant_id', $merchant->merchant_id)
                ->where('product_id', $id)
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.'
                ], 404);
            }

            // Create duplicate product
            $newProduct = $product->replicate();
            $newProduct->name = $product->name . ' (Copy)';
            $newProduct->slug = Str::slug($newProduct->name) . '-' . Str::random(6);
            $newProduct->sku = strtoupper(Str::random(8));
            $newProduct->is_active = false; // Set as inactive by default
            $newProduct->stock_quantity = 0; // Reset stock
            $newProduct->save();

            // Duplicate discount if exists
            if ($product->discounts()->where('is_active', true)->exists()) {
                $discount = $product->discounts()->where('is_active', true)->first();
                $newDiscount = $discount->replicate();
                $newDiscount->product_id = $newProduct->product_id;
                $newDiscount->is_active = false;
                $newDiscount->save();
            }

            // Duplicate points if exists
            if ($product->points && $product->points->is_active) {
                $points = $product->points;
                $newPoints = $points->replicate();
                $newPoints->product_id = $newProduct->product_id;
                $newPoints->is_active = false;
                $newPoints->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $newProduct,
                'message' => 'Product duplicated successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Duplicate product error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to duplicate product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update stock
     */
    public function bulkUpdateStock(Request $request)
    {
        $request->validate([
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,product_id',
            'products.*.stock_quantity' => 'required|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            $merchant = auth()->user()->merchant;

            if (!$merchant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Merchant not found.'
                ], 404);
            }

            $updated = 0;
            $errors = [];

            foreach ($request->products as $productData) {
                $product = Product::where('merchant_id', $merchant->merchant_id)
                    ->where('product_id', $productData['product_id'])
                    ->first();

                if ($product) {
                    $product->stock_quantity = $productData['stock_quantity'];
                    $product->in_stock = $productData['stock_quantity'] > 0;
                    $product->save();
                    $updated++;
                } else {
                    $errors[] = "Product not found: {$productData['product_id']}";
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'updated' => $updated,
                    'errors' => $errors,
                ],
                'message' => "Updated stock for {$updated} products."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk stock update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update stock: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manage product discount
     */
    public function manageDiscount(Request $request, $productId)
    {
        $request->validate([
            'type' => 'required|in:percentage,fixed,bogo',
            'value' => 'required|numeric|min:0',
            'min_quantity' => 'nullable|integer|min:1',
            'max_quantity' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'usage_limit' => 'nullable|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $merchant = auth()->user()->merchant;

            if (!$merchant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Merchant not found.'
                ], 404);
            }

            $product = Product::where('merchant_id', $merchant->merchant_id)
                ->where('product_id', $productId)
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.'
                ], 404);
            }

            // Deactivate old active discounts
            ProductDiscount::where('product_id', $productId)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            // Create new discount
            $discount = ProductDiscount::create([
                'product_id' => $productId,
                'merchant_id' => $merchant->merchant_id,
                'type' => $request->type,
                'value' => $request->value,
                'min_quantity' => $request->min_quantity ?? 1,
                'max_quantity' => $request->max_quantity,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_active' => true,
                'usage_limit' => $request->usage_limit,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $discount,
                'message' => 'Discount applied successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Discount management error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to manage discount: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manage product points
     */
    public function managePoints(Request $request, $productId)
    {
        $request->validate([
            'points_per_item' => 'required|integer|min:0',
            'points_per_php' => 'nullable|integer|min:0',
            'min_spend' => 'nullable|numeric|min:0',
            'max_points' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        try {
            DB::beginTransaction();

            $merchant = auth()->user()->merchant;

            if (!$merchant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Merchant not found.'
                ], 404);
            }

            $product = Product::where('merchant_id', $merchant->merchant_id)
                ->where('product_id', $productId)
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.'
                ], 404);
            }

            // Deactivate old points
            ProductPoints::where('product_id', $productId)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            // Create new points
            $points = ProductPoints::create([
                'product_id' => $productId,
                'merchant_id' => $merchant->merchant_id,
                'points_per_item' => $request->points_per_item,
                'points_per_php' => $request->points_per_php ?? 0,
                'min_spend' => $request->min_spend ?? 0,
                'max_points' => $request->max_points,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_active' => true,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $points,
                'message' => 'Points settings updated successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Points management error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to manage points: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get low stock products
     */
    public function lowStock(Request $request)
    {
        try {
            $merchant = auth()->user()->merchant;

            if (!$merchant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Merchant not found.'
                ], 404);
            }

            $products = Product::where('merchant_id', $merchant->merchant_id)
                ->whereColumn('stock_quantity', '<=', 'min_stock_alert')
                ->where('stock_quantity', '>', 0)
                ->orderBy('stock_quantity', 'asc')
                ->paginate($request->per_page ?? 20);

            return response()->json([
                'success' => true,
                'data' => $products,
            ]);

        } catch (\Exception $e) {
            Log::error('Low stock error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load low stock products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export products to CSV
     */
    public function export(Request $request)
    {
        try {
            $merchant = auth()->user()->merchant;

            if (!$merchant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Merchant not found.'
                ], 404);
            }

            $products = Product::where('merchant_id', $merchant->merchant_id)
                ->with(['discounts', 'points'])
                ->get();

            $filename = 'products_' . date('Y-m-d_H-i-s') . '.csv';
            $handle = fopen('php://temp', 'w+');

            // Headers
            fputcsv($handle, [
                'SKU', 'Name', 'Category', 'Brand', 'Price', 'Original Price',
                'Unit', 'Stock', 'Status', 'Featured', 'Discount Type', 'Discount Value',
                'Points Per Item', 'Created At'
            ]);

            // Data
            foreach ($products as $product) {
                $discount = $product->discounts->where('is_active', true)->first();
                $points = $product->points;

                fputcsv($handle, [
                    $product->sku,
                    $product->name,
                    $product->category,
                    $product->brand,
                    $product->price,
                    $product->original_price,
                    $product->unit,
                    $product->stock_quantity,
                    $product->is_active ? 'Active' : 'Inactive',
                    $product->is_featured ? 'Yes' : 'No',
                    $discount ? $discount->type : '',
                    $discount ? $discount->value : '',
                    $points ? $points->points_per_item : '',
                    $product->created_at,
                ]);
            }

            rewind($handle);
            $csvContent = stream_get_contents($handle);
            fclose($handle);

            return response($csvContent)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', "attachment; filename={$filename}");

        } catch (\Exception $e) {
            Log::error('Export error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to export products: ' . $e->getMessage()
            ], 500);
        }
    }
}