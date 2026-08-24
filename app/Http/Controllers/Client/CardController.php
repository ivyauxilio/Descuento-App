<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\PhysicalCard;
use App\Models\QrCodeUsage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CardController extends Controller
{
    /**
     * Get all cards for the authenticated user.
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            
            $cards = PhysicalCard::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($card) {
                    return [
                        'card_id' => $card->card_id,
                        'card_number' => $this->maskCardNumber($card->card_number),
                        'status' => $card->status,
                        'balance' => $card->balance,
                        'points' => $card->points,
                        'issued_at' => $card->issued_at,
                        'expires_at' => $card->expires_at,
                        'is_active' => $card->isActive(),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $cards,
                'message' => 'Cards retrieved successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Card index error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve cards',
            ], 500);
        }
    }

    /**
     * Activate a new physical card.
     */
    public function activate(Request $request)
    {
        $request->validate([
            'card_number' => 'required|string|min:10|max:20',
        ]);

        try {
            DB::beginTransaction();

            $user = auth()->user();
            
            // Find the card by number (full number, not masked)
            $card = PhysicalCard::where('card_number', $request->card_number)
                ->whereNull('user_id') // Not yet assigned to a user
                ->first();

            if (!$card) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid card number. Please check and try again.',
                ], 404);
            }

            // Check if card is already active
            if ($card->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'This card has already been activated.',
                ], 422);
            }

            // Check if card is expired or lost
            if ($card->status === 'expired') {
                return response()->json([
                    'success' => false,
                    'message' => 'This card has expired. Please contact support.',
                ], 422);
            }

            if ($card->status === 'lost') {
                return response()->json([
                    'success' => false,
                    'message' => 'This card has been reported lost. Please contact support.',
                ], 422);
            }

            // Activate the card
            $card->user_id = $user->id;
            $card->status = 'active';
            $card->issued_at = now();
            $card->expires_at = now()->addYears(2); // 2 years expiry
            $card->save();

            // Create welcome bonus if any
            $this->addWelcomeBonus($card);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'card_id' => $card->card_id,
                    'card_number' => $this->maskCardNumber($card->card_number),
                    'status' => $card->status,
                    'balance' => $card->balance,
                    'points' => $card->points,
                    'issued_at' => $card->issued_at,
                    'expires_at' => $card->expires_at,
                ],
                'message' => 'Card activated successfully! Welcome to Disquento! 🎉',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Card activation error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate card: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get card details.
     */
    public function show(string $id)
    {
        try {
            $user = auth()->user();
            
            $card = PhysicalCard::where('card_id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$card) {
                return response()->json([
                    'success' => false,
                    'message' => 'Card not found.',
                ], 404);
            }

            // Get recent transactions
            $transactions = QrCodeUsage::where('card_id', $card->card_id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($txn) {
                    return [
                        'id' => $txn->usage_id,
                        'type' => $txn->amount_paid ? 'debit' : 'credit',
                        'amount' => $txn->discount_applied,
                        'points' => $txn->points_earned,
                        'description' => $txn->promotion->title ?? 'Promotion',
                        'merchant' => $txn->merchant->business_name ?? 'Unknown',
                        'created_at' => $txn->created_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'card' => [
                        'card_id' => $card->card_id,
                        'card_number' => $this->maskCardNumber($card->card_number),
                        'full_card_number' => $card->card_number, // For security, only send when needed
                        'card_qr_code'=> $card->qr_code,
                        'status' => $card->status,
                        'balance' => $card->balance,
                        'points' => $card->points,
                        'issued_at' => $card->issued_at,
                        'expires_at' => $card->expires_at,
                        'is_active' => $card->isActive(),
                    ],
                    'transactions' => $transactions,
                ],
                'message' => 'Card details retrieved successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Card show error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve card details',
            ], 500);
        }
    }

    /**
     * Lock/unlock card.
     */
    public function toggleLock(string $id)
    {
        try {
            DB::beginTransaction();

            $user = auth()->user();
            
            $card = PhysicalCard::where('card_id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$card) {
                return response()->json([
                    'success' => false,
                    'message' => 'Card not found.',
                ], 404);
            }

            // Toggle status between active and inactive
            if ($card->status === 'active') {
                $card->status = 'inactive';
                $message = 'Card locked successfully.';
            } elseif ($card->status === 'inactive') {
                $card->status = 'active';
                $message = 'Card unlocked successfully.';
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot change status of this card.',
                ], 422);
            }

            $card->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'card_id' => $card->card_id,
                    'status' => $card->status,
                ],
                'message' => $message,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Card toggle lock error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle card lock',
            ], 500);
        }
    }

    /**
     * Report card as lost.
     */
    public function reportLost(string $id)
    {
        try {
            DB::beginTransaction();

            $user = auth()->user();
            
            $card = PhysicalCard::where('card_id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$card) {
                return response()->json([
                    'success' => false,
                    'message' => 'Card not found.',
                ], 404);
            }

            if ($card->status === 'lost') {
                return response()->json([
                    'success' => false,
                    'message' => 'Card is already reported as lost.',
                ], 422);
            }

            $card->status = 'lost';
            $card->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'card_id' => $card->card_id,
                    'status' => $card->status,
                ],
                'message' => 'Card reported as lost. Please contact support to order a replacement.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Card report lost error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to report card as lost',
            ], 500);
        }
    }

    /**
     * Get card balance.
     */
    public function getBalance(string $id)
    {
        try {
            $user = auth()->user();
            
            $card = PhysicalCard::where('card_id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$card) {
                return response()->json([
                    'success' => false,
                    'message' => 'Card not found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'card_id' => $card->card_id,
                    'balance' => $card->balance,
                    'points' => $card->points,
                ],
                'message' => 'Balance retrieved successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Card balance error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve balance',
            ], 500);
        }
    }

    /**
     * Get card transactions.
     */
    public function getTransactions(string $id, Request $request)
    {
        try {
            $user = auth()->user();
            
            $card = PhysicalCard::where('card_id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$card) {
                return response()->json([
                    'success' => false,
                    'message' => 'Card not found.',
                ], 404);
            }

            $limit = $request->get('limit', 20);
            
            $transactions = QrCodeUsage::where('card_id', $card->card_id)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($txn) {
                    return [
                        'id' => $txn->usage_id,
                        'type' => $txn->amount_paid ? 'debit' : 'credit',
                        'amount' => $txn->amount_paid ?? $txn->discount_applied,
                        'discount' => $txn->discount_applied,
                        'points' => $txn->points_earned,
                        'description' => $txn->promotion->title ?? 'Promotion',
                        'merchant' => $txn->merchant->business_name ?? 'Unknown',
                        'created_at' => $txn->created_at,
                        'status' => $txn->status,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $transactions,
                'message' => 'Transactions retrieved successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Card transactions error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve transactions',
            ], 500);
        }
    }

    /**
     * Add welcome bonus to card.
     */
    private function addWelcomeBonus($card): void
    {
        // Add 50 points as welcome bonus
        $card->points += 5;
        $card->save();

        // Log the bonus transaction
        // You can create a separate table for points transactions if needed
        Log::info('Welcome bonus added for card: ' . $card->card_number);
    }

    /**
     * Mask card number for display.
     */
    private function maskCardNumber(string $cardNumber): string
    {
        $length = strlen($cardNumber);
        if ($length <= 4) {
            return $cardNumber;
        }
        
        $visible = 4;
        $masked = str_repeat('*', $length - $visible);
        $lastDigits = substr($cardNumber, -$visible);
        
        return $masked . $lastDigits;
    }
}