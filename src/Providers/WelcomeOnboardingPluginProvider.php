<?php

namespace WelcomeOnboarding\Providers;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
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
        User::created(function (User $user) {
            try {
                $settings = app(SettingsRepository::class)->all();

                Log::info('Welcome Onboarding: user created event fired.', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'plugin_enabled' => $settings['enabled'] ?? null,
                ]);

                if (!$settings['enabled']) {
                    Log::info('Welcome Onboarding: plugin disabled, skipping onboarding mail.');

                    return;
                }

                /** @var PasswordBroker $broker */
                $broker = Password::broker(Filament::getPanel('app')->getAuthPasswordBroker());
                $token = $broker->createToken($user);

                $user->notifyNow(new WelcomeAccountCreated($token, $settings), ['mail']);

                Log::info('Welcome Onboarding: onboarding mail dispatched.', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            } catch (\Throwable $exception) {
                Log::error('Welcome Onboarding: failed to send onboarding mail.', [
                    'message' => $exception->getMessage(),
                    'user_id' => $user->id ?? null,
                    'email' => $user->email ?? null,
                ]);
            }
        });
    }
}
