<?php

namespace WelcomeOnboarding\Notifications;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeAccountCreated extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $settings
     */
    public function __construct(
        public ?string $token = null,
        public array $settings = [],
    ) {}

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $settings = $this->resolveSettingsForLocale($notifiable->language ?? config('app.locale', 'en'));
        $setupUrl = $this->token
            ? Filament::getPanel('app')->getResetPasswordUrl($this->token, $notifiable)
            : url('');

        $message = (new MailMessage())
            ->subject($this->replaceTokens((string) ($settings['subject'] ?? 'Welcome to :app'), $notifiable, $setupUrl))
            ->greeting($this->replaceTokens((string) ($settings['heading'] ?? 'Welcome to :app, :name!'), $notifiable, $setupUrl))
            ->line($this->replaceTokens((string) ($settings['intro_text'] ?? ''), $notifiable, $setupUrl))
            ->line(trans('welcome-onboarding::mail.labels.username', ['username' => $notifiable->username]))
            ->line(trans('welcome-onboarding::mail.labels.email', ['email' => $notifiable->email]))
            ->action((string) ($settings['setup_button_label'] ?? 'Set Up Your Account'), $setupUrl);

        $steps = collect(preg_split('/\r\n|\r|\n/', (string) ($settings['first_login_steps'] ?? '')) ?: [])
            ->map(fn ($step) => trim((string) $step))
            ->filter();

        if ($steps->isNotEmpty()) {
            $message->line(trans('welcome-onboarding::mail.labels.first_login_checklist'));

            foreach ($steps as $step) {
                $message->line('- ' . $this->replaceTokens($step, $notifiable, $setupUrl));
            }
        }

        $links = $this->buildLinks($settings, $setupUrl);
        if ($links->isNotEmpty()) {
            $message->line(trans('welcome-onboarding::mail.labels.helpful_links'));

            foreach ($links as $label => $url) {
                $message->line($label . ': ' . $url);
            }
        }

        $closingText = trim((string) ($settings['closing_text'] ?? ''));
        if ($closingText !== '') {
            $message->line($this->replaceTokens($closingText, $notifiable, $setupUrl));
        }

        return $message;
    }

    private function replaceTokens(string $text, User $user, string $setupUrl): string
    {
        return str_replace(
            [':app', ':name', ':username', ':email', ':panel_url', ':setup_url'],
            [config('app.name'), $user->username, $user->username, $user->email, url(''), $setupUrl],
            $text
        );
    }

    /**
     * @return \Illuminate\Support\Collection<string, string>
     */
    private function buildLinks(array $settings, string $setupUrl): \Illuminate\Support\Collection
    {
        $links = collect();

        $welcomeUrl = trim((string) ($settings['welcome_url'] ?? ''));
        if ($welcomeUrl !== '') {
            $links->put((string) ($settings['welcome_link_label'] ?? trans('welcome-onboarding::mail.links.getting_started')), $welcomeUrl);
        }

        foreach ([
            trans('welcome-onboarding::mail.links.discord') => $settings['discord_url'] ?? '',
            trans('welcome-onboarding::mail.links.community') => $settings['community_url'] ?? '',
            trans('welcome-onboarding::mail.links.support') => $settings['support_url'] ?? '',
        ] as $label => $url) {
            $url = trim((string) $url);
            if ($url !== '') {
                $links->put($label, $url);
            }
        }

        foreach (($settings['extra_links'] ?? []) as $link) {
            $label = trim((string) ($link['label'] ?? ''));
            $url = trim((string) ($link['url'] ?? ''));

            if ($label !== '' && $url !== '') {
                $links->put($label, $url);
            }
        }

        if ($links->isEmpty()) {
            $links->put(trans('welcome-onboarding::mail.links.panel'), url(''));
            $links->put(trans('welcome-onboarding::mail.links.account_setup'), $setupUrl);
        }

        return $links;
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveSettingsForLocale(string $locale): array
    {
        $baseSettings = $this->settings;
        $locale = str($locale)->lower()->replace('_', '-')->toString();

        $translation = collect($this->settings['translations'] ?? [])
            ->first(function ($translation) use ($locale) {
                $translationLocale = str((string) ($translation['locale'] ?? ''))->lower()->replace('_', '-')->toString();

                return $translationLocale === $locale
                    || ($locale !== '' && str_contains($locale, '-') && $translationLocale === str($locale)->before('-')->toString());
            });

        if (!is_array($translation)) {
            return $baseSettings;
        }

        foreach (['subject', 'heading', 'intro_text', 'setup_button_label', 'welcome_link_label', 'first_login_steps', 'closing_text'] as $field) {
            if (filled($translation[$field] ?? null)) {
                $baseSettings[$field] = $translation[$field];
            }
        }

        if (!empty($translation['extra_links'] ?? [])) {
            $baseSettings['extra_links'] = $translation['extra_links'];
        }

        return $baseSettings;
    }
}
