<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthShowRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
    public function show(AuthShowRequest $request): JsonResponse
    {
        $user = $request->user();

        $this->authorize('view', $user);

        return response()->json($user);
    }

    /**
     * Return the authenticated user's status payload for health checks.
     */
    public function status(Request $request): JsonResponse
    {
        return response()->json([
            'authenticated' => true,
            'user' => $request->user(),
        ]);
    }

    /**
     * Log out the authenticated user by revoking their current access token.
     */
    public function logout(Request $request): Response
    {
        $token = $request->user()->currentAccessToken();
        $token->delete();

        return response()->noContent();
    }
}
