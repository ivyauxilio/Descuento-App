<?php
// app/Http/Controllers/Merchant/PlanController.php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\MerchantWallet;
use App\Models\CreditTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PlanController extends Controller
{
    // List all active plans
    public function index()
    {
        $plans = SubscriptionPlan::active()->get();
        
        return response()->json([
            'success' => true,
            'data' => $plans,
        ]);
    }

    // Get single plan
    public function show($slug)
    {
        $plan = SubscriptionPlan::where('slug', $slug)->firstOrFail();
        
        return response()->json([
            'success' => true,
            'data' => $plan,
        ]);
    }

    // Get merchant's wallet
    public function wallet()
    {
        $merchant = auth()->user()->merchant;
        
        $wallet = MerchantWallet::firstOrCreate(
            ['merchant_id' => $merchant->merchant_id],
            [
                'credit_balance' => 0,
                'total_credits_purchased' => 0,
                'total_credits_used' => 0,
                'total_spent' => 0,
            ]
        );

        return response()->json([
            'success' => true,
            'data' => $wallet,
        ]);
    }

    // Purchase a plan
    public function purchase(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,plan_id',
            'payment_method' => 'required|in:gcash,card,bank_transfer',
            'payment_reference' => 'nullable|string',
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

            $plan = SubscriptionPlan::findOrFail($request->plan_id);

            // Get or create wallet
            $wallet = MerchantWallet::firstOrCreate(
                ['merchant_id' => $merchant->merchant_id],
                [
                    'credit_balance' => 0,
                    'total_credits_purchased' => 0,
                    'total_credits_used' => 0,
                    'total_spent' => 0,
                ]
            );

            // Add credits
            $transaction = $wallet->addCredits($plan->total_credits, [
                'type' => 'purchase',
                'amount' => $plan->price,
                'plan_id' => $plan->plan_id,
                'reference_number' => 'TXN-' . strtoupper(Str::random(10)),
                'description' => "Purchased {$plan->name} - {$plan->total_credits} credits",
                'payment_status' => 'paid', // Will be pending if using real payment gateway
                'payment_method' => $request->payment_method,
                'metadata' => [
                    'base_credits' => $plan->base_credits,
                    'bonus_credits' => $plan->bonus_credits,
                ],
            ]);

            // Update wallet total spent
            $wallet->increment('total_spent', $plan->price);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'wallet' => $wallet->fresh(),
                    'transaction' => $transaction,
                ],
                'message' => "Successfully purchased {$plan->name}! 🎉",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Purchase failed: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get transaction history
    public function transactions(Request $request)
    {
        $merchant = auth()->user()->merchant;

        $transactions = CreditTransaction::where('merchant_id', $merchant->merchant_id)
            ->with('plan')
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }
}