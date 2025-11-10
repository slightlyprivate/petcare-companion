<?php

namespace App\Http\Controllers\Auth;

use App\Models\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AuthRequestController extends AuthController
{
    /**
     * Handle OTP request.
     */
    public function requestOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $code = random_int(100000, 999999);
        Otp::create([
            'email' => $request->email,
            'code' => $code,
            'expires_at' => now()->addMinutes(5),
        ]);

        // Send email (replace with notification or mailable)
        Mail::raw("Your OTP is $code", function ($message) use ($request) {
            $message->to($request->email)->subject('Your OTP Code');
        });

        return response()->json(['message' => 'OTP sent']);
    }
}
