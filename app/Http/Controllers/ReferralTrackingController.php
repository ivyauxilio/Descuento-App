<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class ReferralTrackingController extends Controller
{
    /**
     * Handle referral link click — store code in cookie, redirect to signup
     */
    public function track($code, Request $request)
    {
        // Validate code exists
        $referrer = User::where('referral_code', $code)->first();

        if (!$referrer) {
            return redirect('/')->with('error', 'Invalid referral link.');
        }

        // Prevent self-referral click
        if (auth()->check() && auth()->id() === $referrer->id) {
            return redirect('/')->with('info', 'You cannot refer yourself.');
        }

        // Store in cookie (30 days)
        $expiryDays = (int) \App\Models\SystemSetting::get('referral.cookie_expiry_days', 30);

        Cookie::queue('referral_code', $code, 60 * 24 * $expiryDays);

        // Also store in session as backup
        session(['referral_code' => $code]);

        return redirect()->route('register', ['ref' => $code]);
    }
}