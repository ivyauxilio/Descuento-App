<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerWallet;
use App\Models\CustomerWalletTransaction;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\NotificationService;

class WalletController extends Controller
{
    public function show()
    {
        $wallet = auth()->user()->getOrCreateWallet();
        return response()->json(['success' => true, 'data' => $wallet]);
    }

    public function transactions(Request $request)
    {
        $wallet = auth()->user()->getOrCreateWallet();

        $query = CustomerWalletTransaction::where('user_id', auth()->id())
            ->orderByDesc('created_at');

        // Filters
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('payment_status', $request->status);
        }

        $transactions = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'data' => $transactions->items(),
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'total' => $transactions->total(),
                'wallet' => [
                    'balance' => $wallet->balance,
                    'pending_balance' => $wallet->pending_balance,
                    'total_earned' => $wallet->total_earned,
                    'total_withdrawn' => $wallet->total_withdrawn,
                ],
            ],
        ]);
    }

    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:gcash,maya,bank_transfer',
            'account_number' => 'required|string|max:50',
            'account_name' => 'required|string|max:100',
        ]);

        $user = auth()->user();
        $wallet = $user->getOrCreateWallet();

        $amount = (float) $request->amount;
        $minWithdrawal = (float) SystemSetting::get('referral.min_withdrawal', 500);

        // Validations
        if ($amount < $minWithdrawal) {
            return response()->json([
                'success' => false,
                'message' => "Minimum withdrawal is ₱" . number_format($minWithdrawal, 2),
            ], 422);
        }

        if ($wallet->balance < $amount) {
            return response()->json([
                'success' => false,
                'message' => "Insufficient balance. Available: ₱" . number_format($wallet->balance, 2),
            ], 422);
        }

        // Check for pending withdrawal
        $pendingExists = CustomerWalletTransaction::where('user_id', $user->id)
            ->where('type', 'withdrawal')
            ->where('payment_status', 'pending')
            ->exists();

        if ($pendingExists) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a pending withdrawal request. Please wait for it to be processed.',
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Lock wallet
            $lockedWallet = CustomerWallet::where('wallet_id', $wallet->wallet_id)
                ->lockForUpdate()
                ->first();

            if ($lockedWallet->balance < $amount) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance.',
                ], 422);
            }

            $before = $lockedWallet->balance;

            // Deduct from balance
            $lockedWallet->balance -= $amount;
            $lockedWallet->total_withdrawn += $amount;
            $lockedWallet->last_withdrawal_at = now();
            $lockedWallet->save();

            // Create transaction record
            $txn = CustomerWalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'withdrawal',
                'amount' => -$amount,
                'balance_before' => $before,
                'balance_after' => $lockedWallet->balance,
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'description' => "Withdrawal request to {$request->payment_method}",
                'metadata' => [
                    'account_number' => $request->account_number,
                    'account_name' => $request->account_name,
                    'requested_at' => now()->toIso8601String(),
                    'requested_ip' => $request->ip(),
                ],
            ]);

            DB::commit();

            app(NotificationService::class)->withdrawalRequested($user, $amount);
            
            return response()->json([
                'success' => true,
                'data' => $txn,
                'message' => 'Withdrawal request submitted. Pending admin approval.',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Withdrawal request failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit withdrawal request.',
            ], 500);
        }
    }
}