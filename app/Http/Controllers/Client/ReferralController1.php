<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\ReferralService;
use Illuminate\Http\Request;

class ReferralController1 extends Controller
{
    protected ReferralService $referrals;

    public function __construct(ReferralService $referrals)
    {
        $this->referrals = $referrals;
    }

    /**
     * Referral dashboard (list of referrals, stats, share link)
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->role !== 'customer') {
            abort(403);
        }

        $stats = $this->referrals->getUserStats($user);

        $referrals = $user->referralsMade()
            ->with('referee')
            ->orderByDesc('created_at')
            ->paginate(20);

        $walletTransactions = $user->walletTransactions()
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('customer.referrals.index', compact(
            'user',
            'stats',
            'referrals',
            'walletTransactions'
        ));
    }
}