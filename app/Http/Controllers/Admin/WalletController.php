<?php
// app/Http/Controllers/Admin/WalletController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MerchantWallet;
use App\Models\CreditTransaction;
use App\Services\CreditRefundService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function index(Request $request)
    {
        $wallets = MerchantWallet::with('merchant')
            ->when($request->search, function ($q) use ($request) {
                return $q->whereHas('merchant', function ($mq) use ($request) {
                    $mq->where('business_name', 'LIKE', "%{$request->search}%")
                       ->orWhere('email', 'LIKE', "%{$request->search}%");
                });
            })
            ->orderBy('credit_balance', 'desc')
            ->paginate(20);

        return view('admin.wallets.index', compact('wallets'));
    }

    public function show(MerchantWallet $wallet)
    {
        $transactions = CreditTransaction::where('merchant_id', $wallet->merchant_id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.wallets.show', compact('wallet', 'transactions'));
    }

    public function adjust(Request $request, MerchantWallet $wallet, CreditRefundService $service)
    {
        $request->validate([
            'amount' => 'required|integer',
            'reason' => 'required|string|max:255',
        ]);

        $result = $service->adjustCredits(
            $wallet->merchant_id,
            (int) $request->amount,
            $request->reason
        );

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }
}