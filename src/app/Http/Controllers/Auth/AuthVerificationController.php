<?php

namespace App\Http\Controllers\Auth;

use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;

class AuthVerificationController extends AuthController
{
    /**
     * Verify OTP and authenticate user.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string',
        ]);

        $otp = Otp::where('email', $request->email)
            ->where('code', $request->code)
            ->where('expires_at', '>=', now())
            ->first();

        if (! $otp) {
            return response()->json(['message' => 'Invalid or expired OTP'], 401);
        }

        $user = User::firstOrCreate(['email' => $request->email]);
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user]);
    }
}
