<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\PhysicalCard;
use Illuminate\Http\Request;

class CardQRController extends Controller
{
    /**
     * Scan card QR code and get user details for verification
     */
    public function scan(Request $request)
    {
        $request->validate([
            'qr_data' => 'required|string',
        ]);

        try {
            // Get the authenticated merchant
            $merchant = auth()->user()->merchant;
            
            if (!$merchant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Merchant account not found.',
                ], 404);
            }

            // Log the incoming request for debugging
            // Log::info('Card scan request received', [
            //     'qr_data' => $request->qr_data,
            //     'merchant_id' => $merchant->merchant_id,
            // ]);

            // Get the QR code value (e.g., "AWHTCH7R")
            $qrCode = trim($request->qr_data);
            
            // Find the physical card by qr_code column
            $card = PhysicalCard::where('qr_code', $qrCode)
                ->with('user')
                ->first();

            if (!$card) {
                // Try to find by card_id or card_number as fallback
                $card = PhysicalCard::where(function($query) use ($qrCode) {
                        $query->where('card_id', $qrCode)
                              ->orWhere('card_number', $qrCode);
                    })
                    ->with('user')
                    ->first();
            }

            if (!$card) {
                return response()->json([
                    'success' => false,
                    'message' => 'Card not found. Please check the QR code.',
                    'data' => [
                        'qr_code' => $qrCode,
                    ],
                ], 404);
            }

            // Check if card is active
            if ($card->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => "This card is {$card->status}. Cannot process.",
                    'data' => [
                        'status' => $card->status,
                    ],
                ], 422);
            }

            // Check if card is expired
            if ($card->expires_at && $card->expires_at < now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This card has expired.',
                    'data' => [
                        'expires_at' => $card->expires_at,
                    ],
                ], 422);
            }

            // Check if card is assigned to a user
            if (!$card->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'This card has not been activated yet.',
                ], 422);
            }

            // Get the user details
            $user = $card->user;

            // Update last scan timestamp
            // $card->last_scanned_at = now();
            // $card->save();

            // Log successful scan
            // Log::info('Card scanned successfully', [
            //     'merchant_id' => $merchant->merchant_id,
            //     'card_id' => $card->card_id,
            //     'user_id' => $user->id,
            // ]);

            // Return user details for verification
            return response()->json([
                'success' => true,
                'data' => [
                    'card' => [
                        'card_id' => $card->card_id,
                        'card_number' => $this->maskCardNumber($card->card_number),
                        'qr_code' => $card->qr_code,
                        'status' => $card->status,
                        'balance' => $card->balance,
                        'points' => $card->points,
                        'issued_at' => $card->issued_at,
                        'expires_at' => $card->expires_at,
                    ],
                    'user' => [
                        'id' => $user->id,
                        'uuid' => $user->uuid,
                        'firstname' => $user->firstname,
                        'lastname' => $user->lastname,
                        'full_name' => $user->firstname . ' ' . $user->lastname,
                        'email' => $user->email,
                        'phone' => $user->phone ?? null,
                        'avatar' => $user->avatar_url ?? null,
                        'member_since' => $user->created_at,
                    ],
                    'verification' => [
                        'card_active' => true,
                        'card_owner_verified' => true,
                        'scan_timestamp' => now(),
                        'qr_valid' => true,
                    ],
                ],
                'message' => 'Card verified successfully.',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Log::error('Card QR scan error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to scan card: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get card details by QR code (for preview)
     */
    public function getCardByQr(Request $request)
    {
        $request->validate([
            'qr_data' => 'required|string',
        ]);

        try {
            $merchant = auth()->user()->merchant;
            
            if (!$merchant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Merchant account not found.',
                ], 404);
            }

            // Get the QR code value
            $qrCode = trim($request->qr_data);

            // Find the card by qr_code column first
            $card = PhysicalCard::with('user')
                ->where('qr_code', $qrCode)
                ->first();

            if (!$card) {
                // Try to find by card_id or card_number as fallback
                $card = PhysicalCard::with('user')
                    ->where(function($query) use ($qrCode) {
                        $query->where('card_id', $qrCode)
                              ->orWhere('card_number', $qrCode);
                    })
                    ->first();
            }

            if (!$card) {
                return response()->json([
                    'success' => false,
                    'message' => 'Card not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'card' => [
                        'card_id' => $card->card_id,
                        'card_number' => $this->maskCardNumber($card->card_number),
                        'qr_code' => $card->qr_code,
                        'status' => $card->status,
                        'balance' => $card->balance,
                        'points' => $card->points,
                        'issued_at' => $card->issued_at,
                        'expires_at' => $card->expires_at,
                    ],
                    'user' => $card->user ? [
                        'id' => $card->user->id,
                        'uuid' => $card->user->uuid,
                        'firstname' => $card->user->firstname,
                        'lastname' => $card->user->lastname,
                        'full_name' => $card->user->firstname . ' ' . $card->user->lastname,
                        'email' => $card->user->email,
                        'phone' => $card->user->phone ?? null,
                        'member_since' => $card->user->created_at,
                    ] : null,
                ],
            ]);

        } catch (\Exception $e) {
            // Log::error('Get card by QR error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get card details: ' . $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Validate card via QR code.
     */
    public function validateCard(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        $card = PhysicalCard::where('qr_code', $request->qr_code)->first();

        if (!$card) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid card.',
            ]);
        }

        return response()->json([
            'valid' => true,
            'data' => [
                'card_number' => $card->card_number,
                'status' => $card->status,
                'balance' => $card->balance,
            ],
        ]);
    }

        /**
     * Verify QR code against stored data
     */
    private function verifyQrCode($card, $qrData)
    {
        try {
            // Check if QR data contains the card ID
            if (strpos($qrData, $card->card_id) !== false) {
                return true;
            }

            // Check if QR data contains the masked card number
            if (strpos($qrData, $this->maskCardNumber($card->card_number)) !== false) {
                return true;
            }

            // If QR code is stored in database
            if ($card->qr_code && $card->qr_code === $qrData) {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
    /**
     * Decode QR code data
     */
    private function decodeQrData($qrData)
    {
        try {
            // Try to decode as JSON
            $decoded = json_decode($qrData, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }

            // Try to decode as base64
            $base64Decoded = base64_decode($qrData, true);
            if ($base64Decoded !== false) {
                $jsonDecoded = json_decode($base64Decoded, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($jsonDecoded)) {
                    return $jsonDecoded;
                }
            }

            // If it's a plain card number or ID
            if (is_numeric($qrData) || strlen($qrData) === 36) { // UUID length
                return ['card_id' => $qrData];
            }

            // If it's a URL with parameters
            parse_str(parse_url($qrData, PHP_URL_QUERY) ?? '', $queryParams);
            if (!empty($queryParams)) {
                return $queryParams;
            }

            return null;
        } catch (\Exception $e) {
            // Log::error('QR decode error: ' . $e->getMessage());
            return null;
        }
    }

    private function maskCardNumber($number)
    {
        $length = strlen($number);
        if ($length <= 4) {
            return '****';
        }
        return '****' . substr($number, -4);
    }

}