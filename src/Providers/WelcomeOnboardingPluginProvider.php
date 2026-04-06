<?php

namespace WelcomeOnboarding\Providers;

use App\Notifications\AccountCreated;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use WelcomeOnboarding\Notifications\WelcomeAccountCreated;
use WelcomeOnboarding\Support\SettingsRepository;

class WelcomeOnboardingPluginProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingsRepository::class, function () {
            return new SettingsRepository();
        });
    }

    public function boot(): void
    {
        Event::listen(NotificationSending::class, function (NotificationSending $event) {
            if ($event->channel !== 'mail' || !$event->notification instanceof AccountCreated) {
                return null;
            }

            $settings = app(SettingsRepository::class)->all();
            if (!$settings['enabled']) {
                return null;
            }

            $event->notifiable->notifyNow(new WelcomeAccountCreated($event->notification->token, $settings), ['mail']);
            
            return null;
        });
    }
}
