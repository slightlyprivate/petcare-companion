<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    /**
     * Display the authenticated user's information.
     */
    public function show(\Illuminate\Http\Request $request)
    {
        return response()->json($request->user());
    }
}
