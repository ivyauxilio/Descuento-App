<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\QrCodeUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRCodeController extends Controller
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
     * Get promotion QR code data.
     */
    public function getQrData(string $promotionId)
    {
        try {
            $merchant = $this->getMerchant();

            $promotion = Promotion::where('merchant_id', $merchant->merchant_id)
                ->where('promotion_id', $promotionId)
                ->firstOrFail();

            // Generate QR code image as base64
            $qrCodeImage = $this->generateQrCodeBase64($promotion);

            $qrData = [
                'promotion_id' => $promotion->promotion_id,
                'qr_code' => $promotion->qr_code,
                'title' => $promotion->title,
                'description' => $promotion->description,
                'promo_type' => $promotion->promo_type,
                'value' => $promotion->value,
                'start_date' => $promotion->start_date,
                'end_date' => $promotion->end_date,
                'status' => $promotion->status,
                'used_count' => $promotion->used_count,
                'usage_limit' => $promotion->usage_limit,
                'remaining' => $promotion->getRemainingUses(),
                'usage_percentage' => $promotion->getUsagePercentage(),
                'merchant_name' => $promotion->merchant->business_name ?? null,
                'is_valid' => $promotion->isValidQrCode(),
                'qr_code_image' => $qrCodeImage,
                'last_used_at' => $promotion->last_used_at,
            ];

            return response()->json([
                'data' => $qrData,
                'message' => 'QR code data retrieved successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('QR Code retrieval failed: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to retrieve QR code data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate QR code as base64 string.
     */
    private function generateQrCodeBase64($promotion): string
    {
        try {
            $qrData = json_encode([
                'promotion_id' => $promotion->promotion_id,
                'qr_code' => $promotion->qr_code,
                'title' => $promotion->title,
                'type' => $promotion->promo_type,
                'value' => $promotion->value,
                'merchant_id' => $promotion->merchant_id,
            ]);

            // Generate QR code as SVG
            $svg = QrCode::size(300)
                ->format('svg')
                ->errorCorrection('H')
                ->generate($qrData);

            // Convert to base64
            return 'data:image/svg+xml;base64,' . base64_encode($svg);

        } catch (\Exception $e) {
            Log::error('QR Code generation failed: ' . $e->getMessage());
            
            // Return a fallback SVG
            return $this->getFallbackQrCode();
        }
    }

    /**
     * Get fallback QR code SVG.
     */
    private function getFallbackQrCode(): string
    {
        $svg = '<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
            <rect width="200" height="200" fill="#f0f0f0" rx="10"/>
            <text x="100" y="100" text-anchor="middle" font-family="Arial" font-size="14" fill="#999">
                QR Code Unavailable
            </text>
            <text x="100" y="120" text-anchor="middle" font-family="Arial" font-size="10" fill="#ccc">
                Please regenerate
            </text>
        </svg>';
        
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Verify and validate QR code.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
            'promotion_id' => 'required|uuid',
            'device_id' => 'nullable|string',
            'location' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $promotion = Promotion::where('promotion_id', $request->promotion_id)
                ->where('qr_code', $request->qr_code)
                ->first();

            if (!$promotion) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Invalid QR code',
                ], 404);
            }

            // Check if promotion is valid
            if (!$promotion->isValidQrCode()) {
                return response()->json([
                    'valid' => false,
                    'message' => 'This promotion is no longer valid',
                    'reason' => $this->getInvalidReason($promotion),
                ], 422);
            }

            // Check if user can use
            if ($request->user_id && !$promotion->canUserUse($request->user_id)) {
                return response()->json([
                    'valid' => false,
                    'message' => 'You have already used this promotion the maximum number of times',
                ], 422);
            }

            // Create QR code usage
            $usage = QrCodeUsage::create([
                'usage_id' => (string) Str::uuid(),
                'promotion_id' => $promotion->promotion_id,
                'merchant_id' => $promotion->merchant_id,
                'user_id' => $request->user_id,
                'qr_code' => $request->qr_code,
                'discount_applied' => $promotion->value,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'device_id' => $request->device_id,
                'location' => $request->location,
                'scanned_at' => now(),
            ]);

            // Increment usage count
            $promotion->incrementUsage();

            DB::commit();

            return response()->json([
                'valid' => true,
                'data' => [
                    'promotion' => $promotion,
                    'usage' => $usage,
                    'remaining_uses' => $promotion->getRemainingUses(),
                ],
                'message' => 'QR code verified successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('QR Code verification failed: ' . $e->getMessage());
            
            return response()->json([
                'valid' => false,
                'message' => 'Failed to verify QR code',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get QR code usage statistics.
     */
    public function getStats()
    {
        try {
            $merchant = $this->getMerchant();

            $stats = [
                'total_qr_codes' => Promotion::where('merchant_id', $merchant->merchant_id)->count(),
                'active_qr_codes' => Promotion::where('merchant_id', $merchant->merchant_id)
                    ->active()
                    ->count(),
                'total_scans' => QrCodeUsage::where('merchant_id', $merchant->merchant_id)->count(),
                'unique_users' => QrCodeUsage::where('merchant_id', $merchant->merchant_id)
                    ->distinct('user_id')
                    ->count('user_id'),
                'today_scans' => QrCodeUsage::where('merchant_id', $merchant->merchant_id)
                    ->whereDate('scanned_at', today())
                    ->count(),
                'most_used_promotion' => Promotion::where('merchant_id', $merchant->merchant_id)
                    ->orderBy('used_count', 'desc')
                    ->first(),
            ];

            return response()->json([
                'data' => $stats,
                'message' => 'QR code statistics retrieved successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('QR Code stats failed: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to retrieve QR code statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get invalid reason.
     */
    private function getInvalidReason($promotion): string
    {
        if ($promotion->status !== 'active') {
            return 'Promotion is not active';
        }

        if ($promotion->start_date > now()) {
            return 'Promotion has not started yet';
        }

        if ($promotion->end_date && $promotion->end_date < now()) {
            return 'Promotion has expired';
        }

        if ($promotion->usage_limit && $promotion->used_count >= $promotion->usage_limit) {
            return 'Promotion usage limit reached';
        }

        return 'Promotion is invalid';
    }
}