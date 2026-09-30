<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReferralService;

class ReferralApiController extends Controller
{
    protected ReferralService $referrals;

    public function __construct(ReferralService $referrals)
    {
        $this->referrals = $referrals;
    }

    public function me()
    {
        $stats = $this->referrals->getUserStats(auth()->user());

        return response()->json(['success' => true, 'data' => $stats]);
    }

    public function list()
    {
        $referrals = auth()->user()->referralsMade()
            ->with('referee:id,firstname,lastname,email')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json(['success' => true, 'data' => $referrals]);
    }

    public function wallet()
    {
        $wallet = auth()->user()->getOrCreateWallet();

        return response()->json(['success' => true, 'data' => $wallet]);
    }

    public function walletTransactions()
    {
        $txns = auth()->user()->walletTransactions()
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json(['success' => true, 'data' => $txns]);
    }
}