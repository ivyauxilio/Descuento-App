<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    protected ReferralService $referrals;

    public function __construct(ReferralService $referrals)
    {
        $this->referrals = $referrals;
    }

    public function register(Request $request)
    {
        $request->validate([
            'firstname' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Get referral code — from query, session, or cookie
        // Works WITH or WITHOUT a code
        $referralCode = $request->input('ref')
            ?? session('referral_code')
            ?? Cookie::get('referral_code')
            ?? null;

        // Create user (registration always succeeds)
        $user = User::create([
            'uuid' => (string) Str::uuid(),
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'customer',
            'status' => 'active',
        ]);

        // Generate referral code
        if (empty($user->referral_code)) {
            $user->update([
                'referral_code' => User::generateUniqueReferralCode($user),
            ]);
        }

        // Create wallet
        $user->getOrCreateWallet();

        // ✅ Track referral only if a code was provided AND valid
        if ($referralCode) {
            $referral = $this->referrals->trackReferral($user, $referralCode, [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Clear tracking
            Cookie::queue(Cookie::forget('referral_code'));
            session()->forget('referral_code');

            if ($referral) {
                // Referee got ₱25 instantly
                auth()->login($user);

                return redirect('/account/referrals')->with('success',
                    '🎉 Welcome! You earned ₱' . number_format($referral->metadata['referee_reward'], 2) . ' welcome credit!'
                );
            }
        }

        // ✅ Normal registration (no referral)
        auth()->login($user);

        return redirect('/dashboard')->with('success', 'Welcome to KlickCard!');
    }
}