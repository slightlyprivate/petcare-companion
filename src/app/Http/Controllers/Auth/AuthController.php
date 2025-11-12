<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthShowRequest;

/**
 * Controller handling authentication-related actions.
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
}
