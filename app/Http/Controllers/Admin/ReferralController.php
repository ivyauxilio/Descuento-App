<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use App\Models\User;
use App\Models\CustomerWalletTransaction;
use App\Services\ReferralService;
use App\Services\NotificationService;
use Illuminate\Http\Request;


class ReferralController extends Controller
{
    protected ReferralService $referrals;

    public function __construct(ReferralService $referrals)
    {
        $this->referrals = $referrals;
    }

    /**
     * List all referrals with filters
     */
    public function index(Request $request)
    {
        $query = Referral::with(['referrer', 'referee', 'approvedBy'])
            ->orderByDesc('created_at');

        // Filters
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('referral_code', 'LIKE', "%{$search}%")
                  ->orWhereHas('referrer', function ($rq) use ($search) {
                      $rq->where('email', 'LIKE', "%{$search}%")
                         ->orWhere('firstname', 'LIKE', "%{$search}%")
                         ->orWhere('lastname', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('referee', function ($rq) use ($search) {
                      $rq->where('email', 'LIKE', "%{$search}%")
                         ->orWhere('firstname', 'LIKE', "%{$search}%")
                         ->orWhere('lastname', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $referrals = $query->paginate(25)->withQueryString();

        // Stats
        $stats = [
            'total' => Referral::count(),
            'pending' => Referral::where('status', 'pending')->count(),
            'qualified' => Referral::where('status', 'qualified')->count(),
            'approved' => Referral::where('status', 'approved')->count(),
            'rejected' => Referral::where('status', 'rejected')->count(),
            'total_rewards_paid' => Referral::where('status', 'approved')->sum('reward_amount'),
            'total_pending_rewards' => Referral::whereIn('status', ['pending', 'qualified'])->sum('reward_amount'),
        ];

        return view('admin.referrals.index', compact('referrals', 'stats'));
    }

    /**
     * Show one referral
     */
    public function show($id)
    {
        $referral = Referral::with(['referrer', 'referee', 'approvedBy', 'walletTransaction'])
            ->findOrFail($id);

        return view('admin.referrals.show', compact('referral'));
    }

    /**
     * Approve a referral and credit the wallet
     */
    public function approve($id)
    {
        $referral = Referral::findOrFail($id);

        try {
            $this->referrals->approveReferral($referral, auth()->user());

            return back()->with('success', "Referral approved. ₱{$referral->reward_amount} credited.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to approve: ' . $e->getMessage());
        }
    }

    /**
     * Reject a referral
     */
    public function reject(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $referral = Referral::findOrFail($id);

        $this->referrals->rejectReferral($referral, $request->reason, auth()->user());

        return back()->with('success', 'Referral rejected.');
    }

    /**
     * Bulk approve
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:referrals,referral_id',
        ]);

        $approved = 0;
        $failed = 0;

        foreach ($request->ids as $id) {
            $referral = Referral::find($id);
            if ($referral && in_array($referral->status, ['pending', 'qualified'])) {
                try {
                    $this->referrals->approveReferral($referral, auth()->user());
                    $approved++;
                } catch (\Exception $e) {
                    $failed++;
                }
            }
        }

        return back()->with('success', "Approved {$approved} referrals." . ($failed ? " {$failed} failed." : ''));
    }

    /**
     * List all wallet transactions (point generation log)
     */
    public function transactions(Request $request)
    {
        $query = CustomerWalletTransaction::with(['user', 'referral'])
            ->orderByDesc('created_at');

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('payment_status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('email', 'LIKE', "%{$search}%")
                  ->orWhere('firstname', 'LIKE', "%{$search}%");
            });
        }

        $transactions = $query->paginate(30)->withQueryString();

        $stats = [
            'total_transactions' => CustomerWalletTransaction::count(),
            'total_credited' => CustomerWalletTransaction::where('amount', '>', 0)->sum('amount'),
            'total_withdrawn' => abs(CustomerWalletTransaction::where('type', 'withdrawal')->sum('amount')),
            'total_pending_withdrawals' => CustomerWalletTransaction::where('type', 'withdrawal')
                ->where('payment_status', 'pending')
                ->sum('amount'),
        ];

        return view('admin.referrals.transactions', compact('transactions', 'stats'));
    }

    /**
     * Confirm withdrawal / mark as paid
     */
    public function confirmTransaction(Request $request, $id)
    {
        $request->validate([
            'payment_reference' => 'nullable|string|max:255',
            'status' => 'required|in:paid,failed',
        ]);

        $txn = CustomerWalletTransaction::findOrFail($id);

        $txn->update([
            'payment_status' => $request->status,
            'payment_reference' => $request->payment_reference,
        ]);

        if ($request->status === 'failed') {
            // Refund the wallet
            $wallet = $txn->user->getOrCreateWallet();
            $wallet->credit(abs((float) $txn->amount), [
                'type' => 'reversal',
                'description' => "Reversal of failed withdrawal #{$txn->transaction_uuid}",
            ]);
        }
        
        if ($request->status === 'paid') {
            app(NotificationService::class)->withdrawalApproved($txn->user, abs((float) $txn->amount));
        } else if ($request->status === 'failed') {
            app(NotificationService::class)->withdrawalRejected(
                $txn->user,
                abs((float) $txn->amount),
                $request->reason ?? 'Processing failed'
            );
        }

        return back()->with('success', 'Transaction ' . $request->status . '.');
    }
}