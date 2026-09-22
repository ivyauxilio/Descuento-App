<?php
// app/Http/Controllers/Admin/TransactionController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreditTransaction;
use App\Models\MerchantWallet;
use App\Models\Merchant;
use App\Models\SubscriptionPlan;
use App\Services\CreditRefundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class TransactionController extends Controller
{
    /**
     * List all credit transactions with filters
     */
    public function index(Request $request)
    {
        $query = CreditTransaction::with(['merchant', 'plan', 'promotion'])
            ->orderBy('created_at', 'desc');

        // Filter by type
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Filter by merchant
        if ($request->filled('merchant_id')) {
            $query->where('merchant_id', $request->merchant_id);
        }

        // Filter by payment status
        if ($request->filled('payment_status') && $request->payment_status !== 'all') {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by amount range
        if ($request->filled('amount_min')) {
            $query->where('amount', '>=', $request->amount_min);
        }
        if ($request->filled('amount_max')) {
            $query->where('amount', '<=', $request->amount_max);
        }

        // Filter by credits range
        if ($request->filled('credits_min')) {
            $query->where('credits', '>=', $request->credits_min);
        }
        if ($request->filled('credits_max')) {
            $query->where('credits', '<=', $request->credits_max);
        }

        // Search by reference or description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('payment_reference', 'LIKE', "%{$search}%")
                  ->orWhereHas('merchant', function ($mq) use ($search) {
                      $mq->where('business_name', 'LIKE', "%{$search}%")
                         ->orWhere('email', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Sort options
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $allowedSorts = ['created_at', 'credits', 'amount', 'balance_after'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        $transactions = $query->paginate($request->input('per_page', 25))
            ->withQueryString();

        // Get data for filter dropdowns
        $merchants = Merchant::orderBy('business_name')
            ->get(['merchant_id', 'business_name', 'email']);

        // Stats
        $stats = $this->getStats($request);

        return view('admin.transactions.index', compact(
            'transactions',
            'merchants',
            'stats'
        ));
    }

    /**
     * Show single transaction
     */
    public function show($id)
    {
        $transaction = CreditTransaction::with([
            'merchant',
            'plan',
            'promotion',
        ])->findOrFail($id);

        // Get related transactions from same merchant (before/after)
        $before = CreditTransaction::where('merchant_id', $transaction->merchant_id)
            ->where('created_at', '<', $transaction->created_at)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $after = CreditTransaction::where('merchant_id', $transaction->merchant_id)
            ->where('created_at', '>', $transaction->created_at)
            ->orderBy('created_at', 'asc')
            ->limit(5)
            ->get();

        $wallet = MerchantWallet::where('merchant_id', $transaction->merchant_id)->first();

        return view('admin.transactions.show', compact(
            'transaction',
            'before',
            'after',
            'wallet'
        ));
    }

    /**
     * Refund a purchase transaction
     */
    public function refund(Request $request, $id, CreditRefundService $refundService)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
            'refund_credits' => 'nullable|integer|min:1',
        ]);

        $transaction = CreditTransaction::findOrFail($id);

        // Only purchases can be refunded
        if ($transaction->type !== 'purchase') {
            return back()->with('error', 'Only purchase transactions can be refunded.');
        }

        // Check if already refunded
        $alreadyRefunded = CreditTransaction::where('merchant_id', $transaction->merchant_id)
            ->where('type', 'refund')
            ->where('metadata->original_transaction_id', $transaction->transaction_id)
            ->exists();

        if ($alreadyRefunded) {
            return back()->with('error', 'This transaction has already been refunded.');
        }

        try {
            DB::beginTransaction();

            $wallet = MerchantWallet::where('merchant_id', $transaction->merchant_id)
                ->lockForUpdate()
                ->firstOrFail();

            // Determine refund amount
            $creditsToRefund = $request->input('refund_credits', abs($transaction->credits));

            if ($wallet->credit_balance < $creditsToRefund) {
                // Allow going negative? Or block? Let's allow it and log it.
                \Log::warning('Refunding more credits than balance', [
                    'merchant_id' => $transaction->merchant_id,
                    'balance' => $wallet->credit_balance,
                    'refund' => $creditsToRefund,
                ]);
            }

            $before = $wallet->credit_balance;
            $wallet->credit_balance -= $creditsToRefund;
            $wallet->total_credits_used += $creditsToRefund;
            $wallet->save();

            // Create refund transaction
            $refund = CreditTransaction::create([
                'merchant_id' => $transaction->merchant_id,
                'type' => 'refund',
                'credits' => -$creditsToRefund,
                'balance_before' => $before,
                'balance_after' => $wallet->credit_balance,
                'amount' => -abs($transaction->amount),
                'plan_id' => $transaction->plan_id,
                'reference_number' => 'REF-' . strtoupper(\Illuminate\Support\Str::random(10)),
                'description' => "Refund: {$request->reason}",
                'metadata' => [
                    'original_transaction_id' => $transaction->transaction_id,
                    'original_reference' => $transaction->reference_number,
                    'refunded_by' => auth()->id(),
                    'refunded_by_name' => auth()->user()->firstname . ' ' . auth()->user()->lastname,
                    'reason' => $request->reason,
                ],
            ]);

            // Mark original transaction
            $originalMeta = $transaction->metadata ?? [];
            $originalMeta['refunded'] = true;
            $originalMeta['refunded_at'] = now()->toIso8601String();
            $originalMeta['refund_transaction_id'] = $refund->transaction_id;
            $transaction->update(['metadata' => $originalMeta]);

            DB::commit();

            \Log::info('Transaction refunded', [
                'original' => $transaction->transaction_id,
                'refund' => $refund->transaction_id,
                'credits' => $creditsToRefund,
                'by' => auth()->id(),
            ]);

            return redirect()
                ->route('admin.transactions.show', $transaction->transaction_id)
                ->with('success', "Refunded {$creditsToRefund} credits.");

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Refund failed: ' . $e->getMessage());
            return back()->with('error', 'Refund failed: ' . $e->getMessage());
        }
    }

    /**
     * Export transactions to CSV
     */
    public function export(Request $request)
    {
        // Reuse filter logic
        $query = CreditTransaction::with(['merchant', 'plan'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }
        if ($request->filled('merchant_id')) {
            $query->where('merchant_id', $request->merchant_id);
        }
        if ($request->filled('payment_status') && $request->payment_status !== 'all') {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhereHas('merchant', function ($mq) use ($search) {
                      $mq->where('business_name', 'LIKE', "%{$search}%");
                  });
            });
        }

        $filename = 'transactions_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Transaction ID',
                'Date',
                'Merchant',
                'Email',
                'Type',
                'Credits',
                'Balance After',
                'Amount',
                'Currency',
                'Plan',
                'Payment Status',
                'Payment Method',
                'Payment Reference',
                'Reference #',
                'Description',
            ]);

            // Stream data in chunks
            $query->chunk(500, function ($transactions) use ($file) {
                foreach ($transactions as $t) {
                    fputcsv($file, [
                        $t->transaction_uuid,
                        $t->created_at->format('Y-m-d H:i:s'),
                        $t->merchant->business_name ?? 'N/A',
                        $t->merchant->email ?? 'N/A',
                        ucfirst($t->type),
                        $t->credits,
                        $t->balance_after,
                        $t->amount,
                        $t->currency,
                        $t->plan->name ?? '',
                        $t->payment_status ?? '',
                        $t->payment_method ?? '',
                        $t->payment_reference ?? '',
                        $t->reference_number ?? '',
                        $t->description ?? '',
                    ]);
                }
            });

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Reverse a transaction (admin only)
     * Creates an opposite transaction to cancel the original
     */
    public function reverse(Request $request, $id, CreditRefundService $service)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $transaction = CreditTransaction::findOrFail($id);

        // Prevent reversing a reverse
        if ($transaction->type === 'adjustment' && isset($transaction->metadata['reverses'])) {
            return back()->with('error', 'Cannot reverse an adjustment.');
        }

        try {
            DB::beginTransaction();

            // Reverse means: add opposite credits
            $oppositeCredits = -$transaction->credits;

            $result = $service->adjustCredits(
                $transaction->merchant_id,
                $oppositeCredits,
                "Reversal of transaction {$transaction->transaction_uuid}: {$request->reason}",
                auth()->id()
            );

            if (!$result['success']) {
                DB::rollBack();
                return back()->with('error', $result['message']);
            }

            // Mark original
            $meta = $transaction->metadata ?? [];
            $meta['reversed'] = true;
            $meta['reversed_at'] = now()->toIso8601String();
            $meta['reversed_by'] = auth()->id();
            $meta['reversal_reason'] = $request->reason;
            $transaction->update(['metadata' => $meta]);

            DB::commit();

            return redirect()
                ->route('admin.transactions.show', $transaction->transaction_id)
                ->with('success', "Transaction reversed successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Reversal failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete a transaction (only for cleaning up errors, admin only)
     */
    public function destroy($id)
    {
        $transaction = CreditTransaction::findOrFail($id);

        // Safety checks
        if ($transaction->type === 'purchase' && $transaction->payment_status === 'paid') {
            return back()->with('error', 'Cannot delete a paid transaction. Use refund instead.');
        }

        if ($transaction->payment_status === 'paid') {
            return back()->with('error', 'Cannot delete paid transactions. Use refund or reverse.');
        }

        DB::beginTransaction();
        try {
            // Adjust wallet if the transaction added credits
            $wallet = MerchantWallet::where('merchant_id', $transaction->merchant_id)
                ->lockForUpdate()
                ->first();

            if ($wallet && $transaction->credits != 0) {
                // Reverse the effect
                $wallet->credit_balance -= $transaction->credits;
                
                if ($transaction->credits > 0) {
                    $wallet->total_credits_purchased = max(0, $wallet->total_credits_purchased - $transaction->credits);
                } else {
                    $wallet->total_credits_used = max(0, $wallet->total_credits_used - abs($transaction->credits));
                }
                
                $wallet->save();
            }

            $transaction->delete();

            DB::commit();
            return back()->with('success', 'Transaction deleted.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }

    /**
     * Get aggregated stats for the current filter
     */
    private function getStats(Request $request): array
    {
        $query = CreditTransaction::query();

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }
        if ($request->filled('merchant_id')) {
            $query->where('merchant_id', $request->merchant_id);
        }
        if ($request->filled('payment_status') && $request->payment_status !== 'all') {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhereHas('merchant', function ($mq) use ($search) {
                      $mq->where('business_name', 'LIKE', "%{$search}%");
                  });
            });
        }

        return [
            'total' => (clone $query)->count(),
            'total_credits_added' => (clone $query)->where('credits', '>', 0)->sum('credits'),
            'total_credits_used' => abs((clone $query)->where('credits', '<', 0)->sum('credits')),
            'total_revenue' => (clone $query)->where('payment_status', 'paid')->sum('amount'),
            'purchases_count' => (clone $query)->where('type', 'purchase')->count(),
            'refunds_count' => (clone $query)->where('type', 'refund')->count(),
            'usages_count' => (clone $query)->where('type', 'usage')->count(),
            'bonuses_count' => (clone $query)->where('type', 'bonus')->count(),
        ];
    }

    /**
     * API: Get recent transactions (for dashboard widget)
     */
    public function recent(Request $request)
    {
        $transactions = CreditTransaction::with(['merchant', 'plan'])
            ->orderBy('created_at', 'desc')
            ->limit($request->input('limit', 10))
            ->get();

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    /**
     * Bulk delete un-paid transactions
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:credit_transactions,transaction_id',
        ]);

        // Only allow deleting pending/failed purchases and bonuses
        $deletable = CreditTransaction::whereIn('transaction_id', $request->ids)
            ->where(function ($q) {
                $q->where('payment_status', '!=', 'paid')
                  ->orWhereNull('payment_status')
                  ->orWhere('type', 'bonus');
            })
            ->get();

        $blocked = count($request->ids) - $deletable->count();

        DB::beginTransaction();
        try {
            foreach ($deletable as $t) {
                // Adjust wallet
                $wallet = MerchantWallet::where('merchant_id', $t->merchant_id)
                    ->lockForUpdate()
                    ->first();

                if ($wallet && $t->credits != 0) {
                    $wallet->credit_balance -= $t->credits;
                    $wallet->save();
                }
            }

            CreditTransaction::whereIn('transaction_id', $deletable->pluck('transaction_id'))->delete();

            DB::commit();

            $msg = "Deleted {$deletable->count()} transactions.";
            if ($blocked > 0) {
                $msg .= " {$blocked} were skipped (paid).";
            }

            return back()->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Bulk delete failed: ' . $e->getMessage());
        }
    }
}