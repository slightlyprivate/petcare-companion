<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthShowRequest;
use Illuminate\Http\Request;

/**
 * Controller handling authentication-related actions.
 *
 * @authenticated
 *
 * @group Authentication
 */
class AuthController extends Controller
{
    /**
     * Display the authenticated user's information.
     */
    public function show(AuthShowRequest $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        $this->authorize('view', $user);

        return response()->json($user);
    }

    /**
     * Log out the authenticated user (session-based).
     */
    public function logout(Request $request): \Illuminate\Http\Response
    {
        auth()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
