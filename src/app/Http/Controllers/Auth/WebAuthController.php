<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\AuthUserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Web authentication controller for handling login/logout via the web interface.
 */
class WebAuthController extends Controller
{
    /** @var AuthUserService */
    protected $userService;

    /**
     * Create a new controller instance.
     */
    public function __construct(AuthUserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('pets.index.web');
        }

        return view('auth.login');
    }

    /**
     * Handle login via OTP verification.
     */
    public function login(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp_code' => 'required|string|size:6',
        ]);

        try {
            // Validate OTP directly using the service
            [$user, $token] = $this->userService->validate($request->email, $request->otp_code);

            // Log the user in using session-based authentication
            Auth::login($user);

            return redirect()->route('pets.index.web')
                ->with('success', 'Login successful!');
        } catch (\Exception $e) {
            return back()->withErrors([
                'otp_code' => 'Invalid or expired OTP code.',
            ])->withInput(['email' => $request->email]);
        }
    }

    /**
     * Handle logout.
     */
    public function logout(): \Illuminate\Http\RedirectResponse
    {
        Auth::logout();

        return redirect()->route('login')
            ->with('success', 'Logged out successfully!');
    }
}
