<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Service provider for Laravel Horizon.
 */
class HorizonServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // No bindings required
    }

    public function boot(): void
    {
        // Only run if Horizon is installed
        if (! class_exists(\Laravel\Horizon\Horizon::class)) {
            return;
        }

        // Restrict dashboard access: allow in local env; otherwise only admins
        \Laravel\Horizon\Horizon::auth(function ($request) {
            if (app()->environment('local')) {
                return true;
            }

            $user = $request->user();
            return $user && method_exists($user, 'getAttribute') && $user->getAttribute('role') === 'admin';
        });
    }
}
