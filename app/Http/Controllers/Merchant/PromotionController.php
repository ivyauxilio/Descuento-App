<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Merchant\PromotionRequest;
use App\Models\Promotion;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class PromotionController extends Controller
{
    /**
     * Display promotions for the merchant.
     */
    public function index(Request $request)
    {
        // Get the authenticated merchant
        $merchant = $this->getMerchant();
        
        if (!$merchant) {
            return redirect()->route('merchant.dashboard')
                ->with('error', 'You are not associated with any merchant.');
        }

        $query = Promotion::where('merchant_id', $merchant->merchant_id);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by promo type
        if ($request->filled('promo_type')) {
            $query->where('promo_type', $request->promo_type);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('promo_type', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // $promotions = $query->paginate(12);
        $promotions = $query->paginate($request->get('per_page', 15));
        
        // Get stats
        $stats = [
            'total' => Promotion::where('merchant_id', $merchant->merchant_id)->count(),
            'active' => Promotion::where('merchant_id', $merchant->merchant_id)
                ->where('status', 'active')
                ->where('start_date', '<=', now())
                ->where(function ($q) {
                    $q->whereNull('end_date')
                      ->orWhere('end_date', '>=', now());
                })
                ->count(),
            'expired' => Promotion::where('merchant_id', $merchant->merchant_id)
                ->where(function ($q) {
                    $q->where('status', 'expired')
                      ->orWhere('end_date', '<', now());
                })
                ->count(),
            'inactive' => Promotion::where('merchant_id', $merchant->merchant_id)
                ->where('status', 'inactive')
                ->count(),
        ];

        // $promoTypes = ['percentage', 'fixed', 'bogo'];
        // $statuses = ['active', 'inactive', 'expired'];

        // return view('merchant.promotions.index', compact(
        //     'promotions', 
        //     'stats', 
        //     'promoTypes', 
        //     'statuses',
        //     'merchant'
        // ));
        return response()->json([
            'data' => $promotions,
            'stats' => $stats,
            'message' => 'Promotions retrieved successfully',
        ]);
    }


    /**
     * Store a newly created promotion.
     */
    public function store(PromotionRequest $request)
    {
        try {
            DB::beginTransaction();

            $merchant = $this->getMerchant();

            $data = $request->validated();
            $data['merchant_id'] = $merchant->merchant_id;
            
            // Generate UUID for promotion_id
            $data['promotion_id'] = (string) Str::uuid();
            
            // Generate unique QR code
            $data['qr_code'] = $this->generateUniqueQrCode();

            // Set default values
            $data['status'] = $request->status ?? 'active';
            $data['used_count'] = 0;
            $data['usage_limit'] = $request->usage_limit ?? 100;

            // Handle poster image upload
            if ($request->hasFile('poster_image')) {
                $posterImage = $request->file('poster_image');
                $imagePath = $this->uploadPosterImage($posterImage, $data['promotion_id']);
                $data['poster_image'] = $imagePath['original'];
                $data['poster_thumbnail'] = $imagePath['thumbnail'];
            }

            // Set value to 0 for BOGO if not provided
            if ($data['promo_type'] === 'bogo' && !isset($data['value'])) {
                $data['value'] = 0;
            }

            // Handle tiered discount - ensure it's stored as JSON
            if ($data['promo_type'] === 'tiered' && isset($data['tiers'])) {
                $data['tiers'] = json_encode($data['tiers']);
            }

            // Handle is_stackable as boolean
            if (isset($data['is_stackable'])) {
                $data['is_stackable'] = filter_var($data['is_stackable'], FILTER_VALIDATE_BOOLEAN);
            }

            // ============================================
            // TYPE-SPECIFIC VALIDATION & PROCESSING
            // ============================================

            // Percentage Discount
            if ($data['promo_type'] === 'percentage') {
                $data['value'] = $request->value;
                $data['max_discount_amount'] = $request->max_discount_amount ?? null;
            }

            // Fixed Amount
            if ($data['promo_type'] === 'fixed') {
                $data['value'] = $request->value;
            }

            // BOGO (Buy One Get One)
            if ($data['promo_type'] === 'bogo') {
                $data['value'] = 0;
                $data['free_menu_item_id'] = $request->free_menu_item_id;
                $data['required_menu_item_id'] = $request->required_menu_item_id;
            }

            // Free Gift
            if ($data['promo_type'] === 'free_gift') {
                $data['value'] = 0;
                $data['free_gift_product_id'] = $request->free_gift_product_id;
            }

            // Bundle Deal
            if ($data['promo_type'] === 'bundle') {
                $data['buy_quantity'] = $request->buy_quantity;
                $data['get_quantity'] = $request->get_quantity;
                $data['get_discount_percentage'] = $request->get_discount_percentage ?? 0;
            }

            // Tiered Discount
            if ($data['promo_type'] === 'tiered') {
                $data['value'] = 0;
                // Tiers should already be JSON encoded
            }

            // Free Shipping
            if ($data['promo_type'] === 'free_shipping') {
                $data['value'] = 0;
            }

            // Loyalty Points
            if ($data['promo_type'] === 'loyalty_points') {
                $data['value'] = 0;
                $data['points_multiplier'] = $request->points_multiplier ?? 1;
            }

            // Buy X Get Y
            if ($data['promo_type'] === 'buy_x_get_y') {
                $data['buy_quantity'] = $request->buy_quantity;
                $data['get_quantity'] = $request->get_quantity;
                $data['get_discount_percentage'] = $request->get_discount_percentage;
            }

            // First Purchase
            if ($data['promo_type'] === 'first_purchase') {
                $data['value'] = $request->value;
            }

            // Flash Sale
            if ($data['promo_type'] === 'flash_sale') {
                $data['value'] = $request->value;
                $data['max_discount_amount'] = $request->max_discount_amount ?? null;
            }

            // Create the promotion
            $promotion = Promotion::create($data);

            DB::commit();

            return response()->json([
                'data' => $promotion->load('merchant'),
                'message' => 'Promotion created successfully',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Promotion creation failed: ' . $e->getMessage());
            Log::error('Request data: ' . json_encode($request->all()));
            
            return response()->json([
                'message' => 'Failed to create promotion',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Display promotion details.
     */
    public function show(string $id)
    {
        $merchant = $this->getMerchant();
        
        if (!$merchant) {
            return redirect()->route('merchant.dashboard')
                ->with('error', 'You are not associated with any merchant.');
        }

        $promotion = Promotion::where('merchant_id', $merchant->merchant_id)
            ->findOrFail($id);

        return response()->json([
            'data' => $promotion,
            'message' => 'Promotion retrieved successfully',
        ]);
        // return view('merchant.promotions.show', compact('promotion', 'merchant'));
    }

    /**
     * Update the specified promotion.
     */
    public function update(PromotionRequest $request, string $id)
    {
        try {
            DB::beginTransaction();

            $merchant = $this->getMerchant();

            $promotion = Promotion::where('merchant_id', $merchant->merchant_id)
                ->findOrFail($id);

            $data = $request->validated();

            // Set value to 0 for BOGO if not provided
            if ($data['promo_type'] === 'bogo' && !isset($data['value'])) {
                $data['value'] = 0;
            }

            // Handle tiered discount
            if ($data['promo_type'] === 'tiered' && isset($data['tiers'])) {
                $data['tiers'] = json_encode($data['tiers']);
            }

            // Handle is_stackable as boolean
            if (isset($data['is_stackable'])) {
                $data['is_stackable'] = filter_var($data['is_stackable'], FILTER_VALIDATE_BOOLEAN);
            }
            
            // Handle poster image upload
            if ($request->hasFile('poster_image')) {
                // Delete old images
                if ($promotion->poster_image) {
                    Storage::disk('public')->delete($promotion->poster_image);
                }
                if ($promotion->poster_thumbnail) {
                    Storage::disk('public')->delete($promotion->poster_thumbnail);
                }
                
                $imagePath = $this->uploadPosterImage($request->file('poster_image'), $promotion->promotion_id);
                $data['poster_image'] = $imagePath['original'];
                $data['poster_thumbnail'] = $imagePath['thumbnail'];
            }


            $promotion->update($data);

            DB::commit();

            return response()->json([
                'data' => $promotion->load('merchant'),
                'message' => 'Promotion updated successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Promotion update failed: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to update promotion',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
        /**
     * Remove the specified promotion.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $merchant = $this->getMerchant();

            $promotion = Promotion::where('merchant_id', $merchant->merchant_id)
                ->findOrFail($id);

            $promotion->delete();

            DB::commit();

            return response()->json([
                'message' => 'Promotion deleted successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete promotion',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

        /**
     * Update promotion status.
     */
    public function updateStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => ['required', 'in:active,inactive,expired'],
        ]);

        try {
            DB::beginTransaction();

            $merchant = $this->getMerchant();

            $promotion = Promotion::where('merchant_id', $merchant->merchant_id)
                ->findOrFail($id);

            $promotion->status = $request->status;
            $promotion->save();

            DB::commit();

            return response()->json([
                'data' => $promotion,
                'message' => 'Promotion status updated successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get promotion statistics for dashboard.
     */
    public function getStats()
    {
        $merchant = auth()->user()->merchant;
        
        if (!$merchant) {
            return response()->json(['error' => 'No merchant found'], 404);
        }

        $stats = [
            'total' => Promotion::where('merchant_id', $merchant->merchant_id)->count(),
            'active' => Promotion::where('merchant_id', $merchant->merchant_id)
                ->where('status', 'active')
                ->where('start_date', '<=', now())
                ->where(function ($q) {
                    $q->whereNull('end_date')
                      ->orWhere('end_date', '>=', now());
                })
                ->count(),
            'expired' => Promotion::where('merchant_id', $merchant->merchant_id)
                ->where(function ($q) {
                    $q->where('status', 'expired')
                      ->orWhere('end_date', '<', now());
                })
                ->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Generate a unique QR code.
     */
    private function generateUniqueQrCode(): string
    {
        // $prefix = 'PROMO';
        $timestamp = now()->timestamp;
        $random = strtoupper(Str::random(8));
        
        $qrCode = $timestamp . '-' . $random;

        // Ensure uniqueness
        while (Promotion::where('qr_code', $qrCode)->exists()) {
            $random = strtoupper(Str::random(8));
            $qrCode = $timestamp . '-' . $random;
        }

        return $qrCode;
    }


    private function getMerchant()
    {
        $merchant = auth()->user()->merchant;

        if (!$merchant) {
            abort(404, 'Merchant not found');
        }

        return $merchant;
    }

        /**
     * Upload poster image and create thumbnail.
     */
         // private function uploadPosterImage($image, $promotionId): array
    // {
    //     $timestamp = now()->timestamp;
    //     $extension = $image->getClientOriginalExtension();
    //     $filename = "promotion-{$promotionId}-{$timestamp}.{$extension}";
    //     $thumbnailFilename = "promotion-{$promotionId}-{$timestamp}-thumb.{$extension}";

    //     // Store original image
    //     $path = $image->storeAs('promotions/posters', $filename, 'public');
        
    //     // Create and store thumbnail
    //     $imageContent = $image->get();
    //     $thumbnail = Image::make($imageContent)->fit(300, 300)->encode($extension, 80);
    //     Storage::disk('public')->put("promotions/posters/{$thumbnailFilename}", $thumbnail);

    //     return [
    //         'original' => "promotions/posters/{$filename}",
    //         'thumbnail' => "promotions/posters/{$thumbnailFilename}",
    //     ];
    // }
    // private function uploadPosterImage($image, $promotionId): array
    // {
    //     try {
    //         $timestamp = now()->timestamp;
    //         $extension = $image->getClientOriginalExtension();
    //         $filename = "promotion-{$promotionId}-{$timestamp}.{$extension}";
    //         $thumbnailFilename = "promotion-{$promotionId}-{$timestamp}-thumb.{$extension}";

    //         // Store original image
    //         $path = $image->storeAs('promotions/posters', $filename, 'public');
            
    //         // Create and store thumbnail - Using ImageManager without facade
    //         $imageContent = file_get_contents($image->getRealPath());
            
    //         // Use ImageManager directly
    //         $manager = new ImageManager(['driver' => 'gd']);
    //         $thumbnail = $manager->make($imageContent)->fit(300, 300)->encode($extension, 80);
    //         Storage::disk('public')->put("promotions/posters/{$thumbnailFilename}", $thumbnail);

    //         return [
    //             'original' => "promotions/posters/{$filename}",
    //             'thumbnail' => "promotions/posters/{$thumbnailFilename}",
    //         ];
    //     } catch (\Exception $e) {
    //         Log::error('Image upload failed: ' . $e->getMessage());
    //         throw $e;
    //     }
    // }
    /**
     * Delete promotion poster image.
     */
    public function deletePoster(string $id)
    {
        try {
            DB::beginTransaction();

            $merchant = $this->getMerchant();

            $promotion = Promotion::where('merchant_id', $merchant->merchant_id)
                ->findOrFail($id);

            if ($promotion->poster_image) {
                Storage::disk('public')->delete($promotion->poster_image);
            }
            if ($promotion->poster_thumbnail) {
                Storage::disk('public')->delete($promotion->poster_thumbnail);
            }

            $promotion->poster_image = null;
            $promotion->poster_thumbnail = null;
            $promotion->save();

            DB::commit();

            return response()->json([
                'message' => 'Poster image deleted successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete poster image',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function uploadPosterImage($image, $promotionId): array
    {
        try {
            $timestamp = now()->timestamp;
            $extension = $image->getClientOriginalExtension();
            $filename = "promotion-{$promotionId}-{$timestamp}.{$extension}";
            $thumbnailFilename = "promotion-{$promotionId}-{$timestamp}-thumb.{$extension}";

            // Store original image
            $path = $image->storeAs('promotions/posters', $filename, 'public');
            
            // Create thumbnail using GD
            $sourcePath = Storage::disk('public')->path("promotions/posters/{$filename}");
            $thumbPath = Storage::disk('public')->path("promotions/posters/{$thumbnailFilename}");
            
            $this->createThumbnail($sourcePath, $thumbPath, 300, 300);

            return [
                'original' => "promotions/posters/{$filename}",
                'thumbnail' => "promotions/posters/{$thumbnailFilename}",
            ];
        } catch (\Exception $e) {
            Log::error('Image upload failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create thumbnail using GD library.
     */
    private function createThumbnail($sourcePath, $destinationPath, $width, $height)
    {
        $imageInfo = getimagesize($sourcePath);
        $sourceType = $imageInfo[2];
        
        // Create image resource based on type
        switch ($sourceType) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            case IMAGETYPE_WEBP:
                $sourceImage = imagecreatefromwebp($sourcePath);
                break;
            default:
                throw new \Exception('Unsupported image type');
        }
        
        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);
        
        // Calculate aspect ratio
        $sourceRatio = $sourceWidth / $sourceHeight;
        $targetRatio = $width / $height;
        
        if ($sourceRatio > $targetRatio) {
            $newWidth = $sourceHeight * $targetRatio;
            $newHeight = $sourceHeight;
            $x = ($sourceWidth - $newWidth) / 2;
            $y = 0;
        } else {
            $newWidth = $sourceWidth;
            $newHeight = $sourceWidth / $targetRatio;
            $x = 0;
            $y = ($sourceHeight - $newHeight) / 2;
        }
        
        // Create thumbnail
        $thumbnail = imagecreatetruecolor($width, $height);
        
        // Preserve transparency for PNG and GIF
        if ($sourceType === IMAGETYPE_PNG || $sourceType === IMAGETYPE_GIF) {
            imagecolortransparent($thumbnail, imagecolorallocate($thumbnail, 0, 0, 0));
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
        }
        
        imagecopyresampled(
            $thumbnail,
            $sourceImage,
            0, 0, $x, $y,
            $width, $height, $newWidth, $newHeight
        );
        
        // Save thumbnail
        switch ($sourceType) {
            case IMAGETYPE_JPEG:
                imagejpeg($thumbnail, $destinationPath, 80);
                break;
            case IMAGETYPE_PNG:
                imagepng($thumbnail, $destinationPath, 8);
                break;
            case IMAGETYPE_GIF:
                imagegif($thumbnail, $destinationPath);
                break;
            case IMAGETYPE_WEBP:
                imagewebp($thumbnail, $destinationPath, 80);
                break;
        }
        
        imagedestroy($sourceImage);
        imagedestroy($thumbnail);
    }
    

}