<?php

namespace App\Providers;

use App\Channels\TwilioChannel;
use App\Helpers\RateLimitHelper;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
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

        // TODO: Refactor to its own RateLimitHelper method for notification rate limits
        Event::listen(NotificationSending::class, function (NotificationSending $event) {
            $notifiable = $event->notifiable;

            if (! $notifiable || ! method_exists($notifiable, 'getKey')) {
                return null;
            }

            $env = app()->environment(['local', 'testing']) ? 'development' : 'production';
            $config = (array) config("rate-limits.notification.outbound.{$env}");

            $limit = (int) ($config['limit'] ?? 0);
            $decay = (int) ($config['decay_seconds'] ?? 3600);

            if ($limit <= 0) {
                return null;
            }

            $key = sprintf('notification-outbound:%s', $notifiable->getKey());

            if (RateLimiter::tooManyAttempts($key, $limit)) {
                Log::notice('Notification suppressed due to outbound rate limiting.', [
                    'notifiable_type' => get_class($notifiable),
                    'notifiable_id' => $notifiable->getKey(),
                    'channel' => $event->channel,
                    'notification' => get_class($event->notification),
                ]);

                return false;
            }

            RateLimiter::hit($key, $decay);

            return null;
        });

        $this->app->make(ChannelManager::class)->extend('twilio', function ($app) {
            return $app->make(TwilioChannel::class);
        });
    }
}
