<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ignore horizon requests
        if ($request->is('horizon*')) {
            return $next($request);
        }
        Log::info('Request: '.$request->method().' '.$request->fullUrl().' from IP: '.$request->ip());

        return $next($request);
    }
}
