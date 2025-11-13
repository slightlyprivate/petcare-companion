<?php

namespace App\Providers;

use App\Channels\TwilioChannel;
use App\Helpers\RateLimitHelper;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\ServiceProvider;

/**
 * Application service provider.
 *
 * @group Providers
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimitHelper::configure();

        $this->app->make(ChannelManager::class)->extend('twilio', function ($app) {
            return $app->make(TwilioChannel::class);
        });
    }
}
