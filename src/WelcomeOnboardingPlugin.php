<?php

namespace WelcomeOnboarding;

use App\Contracts\Plugins\HasPluginSettings;
use Filament\Actions\Action;
use Filament\Contracts\Plugin;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Panel;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Support\Facades\Password;
use WelcomeOnboarding\Notifications\WelcomeAccountCreated;
use WelcomeOnboarding\Support\SettingsRepository;

class WelcomeOnboardingPlugin implements Plugin, HasPluginSettings
{
    public function getId(): string
    {
        return 'welcome-onboarding';
    }

    public function register(Panel $panel): void
    {
        //
    }

    public function boot(Panel $panel): void
    {
        //
    }

    /**
     * @return Component[]
     */
    public function getSettingsForm(): array
    {
        $settings = app(SettingsRepository::class)->all();

        return [
            Section::make(trans('welcome-onboarding::ui.sections.behavior'))
                ->headerActions([
                    Action::make('send_test_email')
                        ->label(trans('welcome-onboarding::ui.actions.send_test_email'))
                        ->action(function (Get $get) {
                            $user = user();
                            if (!$user) {
                                return;
                            }

                            /** @var PasswordBroker $broker */
                            $broker = Password::broker(\Filament\Facades\Filament::getPanel('app')->getAuthPasswordBroker());
                            $token = $broker->createToken($user);

                            $settings = app(SettingsRepository::class)->normalize($this->getFormState($get));

                            $user->notifyNow(new WelcomeAccountCreated($token, $settings), ['mail']);

                            Notification::make()
                                ->title(trans('welcome-onboarding::ui.notifications.test_sent_title'))
                                ->body(trans('welcome-onboarding::ui.notifications.test_sent_body', ['email' => $user->email]))
                                ->success()
                                ->send();
                        }),
                ])
                ->schema([
                    Toggle::make('enabled')
                        ->label(trans('welcome-onboarding::ui.fields.enabled'))
                        ->default($settings['enabled']),
                    TextInput::make('subject')
                        ->label(trans('welcome-onboarding::ui.fields.subject'))
                        ->default($settings['subject'])
                        ->required()
                        ->helperText(trans('welcome-onboarding::ui.help.placeholders')),
                    TextInput::make('heading')
                        ->label(trans('welcome-onboarding::ui.fields.heading'))
                        ->default($settings['heading'])
                        ->required()
                        ->helperText(trans('welcome-onboarding::ui.help.placeholders')),
                    Textarea::make('intro_text')
                        ->label(trans('welcome-onboarding::ui.fields.intro_text'))
                        ->default($settings['intro_text'])
                        ->rows(3)
                        ->required()
                        ->helperText(trans('welcome-onboarding::ui.help.intro_text')),
                    TextInput::make('setup_button_label')
                        ->label(trans('welcome-onboarding::ui.fields.setup_button_label'))
                        ->default($settings['setup_button_label'])
                        ->required(),
                    TextInput::make('setup_button_url')
                        ->label(trans('welcome-onboarding::ui.fields.setup_button_url'))
                        ->default($settings['setup_button_url'])
                        ->helperText(trans('welcome-onboarding::ui.help.setup_button_url')),
                ]),
            Section::make(trans('welcome-onboarding::ui.sections.links'))
                ->schema([
                    TextInput::make('welcome_url')
                        ->label(trans('welcome-onboarding::ui.fields.welcome_url'))
                        ->default($settings['welcome_url'])
                        ->url(),
                    TextInput::make('welcome_link_label')
                        ->label(trans('welcome-onboarding::ui.fields.welcome_link_label'))
                        ->default($settings['welcome_link_label']),
                    TextInput::make('discord_url')
                        ->label(trans('welcome-onboarding::ui.fields.discord_url'))
                        ->default($settings['discord_url'])
                        ->url(),
                    TextInput::make('community_url')
                        ->label(trans('welcome-onboarding::ui.fields.community_url'))
                        ->default($settings['community_url'])
                        ->url(),
                    TextInput::make('support_url')
                        ->label(trans('welcome-onboarding::ui.fields.support_url'))
                        ->default($settings['support_url'])
                        ->url(),
                    Repeater::make('extra_links')
                        ->label(trans('welcome-onboarding::ui.fields.extra_links'))
                        ->default($settings['extra_links'])
                        ->schema([
                            TextInput::make('label')
                                ->label(trans('welcome-onboarding::ui.fields.link_label'))
                                ->required(),
                            TextInput::make('url')
                                ->label(trans('welcome-onboarding::ui.fields.link_url'))
                                ->required()
                                ->url(),
                        ])
                        ->columns(2)
                        ->addActionLabel(trans('welcome-onboarding::ui.actions.add_link'))
                        ->reorderable(false),
                ]),
            Section::make(trans('welcome-onboarding::ui.sections.first_login'))
                ->schema([
                    Textarea::make('first_login_steps')
                        ->label(trans('welcome-onboarding::ui.fields.first_login_steps'))
                        ->default($settings['first_login_steps'])
                        ->rows(6)
                        ->helperText(trans('welcome-onboarding::ui.help.one_per_line')),
                    Textarea::make('closing_text')
                        ->label(trans('welcome-onboarding::ui.fields.closing_text'))
                        ->default($settings['closing_text'])
                        ->rows(3),
                ]),
            Section::make(trans('welcome-onboarding::ui.sections.localization'))
                ->description(trans('welcome-onboarding::ui.help.localization'))
                ->schema([
                    Repeater::make('translations')
                        ->label(trans('welcome-onboarding::ui.fields.translations'))
                        ->default($settings['translations'])
                        ->schema([
                            TextInput::make('locale')
                                ->label(trans('welcome-onboarding::ui.fields.locale'))
                                ->required()
                                ->placeholder('en')
                                ->helperText(trans('welcome-onboarding::ui.help.locale')),
                            TextInput::make('subject')
                                ->label(trans('welcome-onboarding::ui.fields.subject'))
                                ->helperText(trans('welcome-onboarding::ui.help.placeholders')),
                            TextInput::make('heading')
                                ->label(trans('welcome-onboarding::ui.fields.heading'))
                                ->helperText(trans('welcome-onboarding::ui.help.placeholders')),
                            Textarea::make('intro_text')
                                ->label(trans('welcome-onboarding::ui.fields.intro_text'))
                                ->rows(3)
                                ->helperText(trans('welcome-onboarding::ui.help.placeholders')),
                            TextInput::make('setup_button_label')
                                ->label(trans('welcome-onboarding::ui.fields.setup_button_label')),
                            TextInput::make('setup_button_url')
                                ->label(trans('welcome-onboarding::ui.fields.setup_button_url'))
                                ->helperText(trans('welcome-onboarding::ui.help.setup_button_url')),
                            TextInput::make('welcome_link_label')
                                ->label(trans('welcome-onboarding::ui.fields.welcome_link_label')),
                            Textarea::make('first_login_steps')
                                ->label(trans('welcome-onboarding::ui.fields.first_login_steps'))
                                ->rows(5)
                                ->helperText(trans('welcome-onboarding::ui.help.placeholders')),
                            Textarea::make('closing_text')
                                ->label(trans('welcome-onboarding::ui.fields.closing_text'))
                                ->rows(3)
                                ->helperText(trans('welcome-onboarding::ui.help.placeholders')),
                            Repeater::make('extra_links')
                                ->label(trans('welcome-onboarding::ui.fields.extra_links'))
                                ->schema([
                                    TextInput::make('label')
                                        ->label(trans('welcome-onboarding::ui.fields.link_label'))
                                        ->required(),
                                    TextInput::make('url')
                                        ->label(trans('welcome-onboarding::ui.fields.link_url'))
                                        ->required()
                                        ->url(),
                                ])
                                ->columns(2)
                                ->addActionLabel(trans('welcome-onboarding::ui.actions.add_link'))
                                ->reorderable(false),
                        ])
                        ->columns(2)
                        ->addActionLabel(trans('welcome-onboarding::ui.actions.add_translation'))
                        ->reorderable(false),
                ]),
        ];
    }

    /**
     * @param  array<mixed, mixed>  $data
     */
    public function saveSettings(array $data): void
    {
        app(SettingsRepository::class)->save($data);
    }

    /**
     * @return array<string, mixed>
     */
    private function getFormState(Get $get): array
    {
        return [
            'enabled' => $get('enabled'),
            'subject' => $get('subject'),
            'heading' => $get('heading'),
            'intro_text' => $get('intro_text'),
            'setup_button_label' => $get('setup_button_label'),
            'setup_button_url' => $get('setup_button_url'),
            'welcome_url' => $get('welcome_url'),
            'welcome_link_label' => $get('welcome_link_label'),
            'discord_url' => $get('discord_url'),
            'community_url' => $get('community_url'),
            'support_url' => $get('support_url'),
            'extra_links' => $get('extra_links'),
            'first_login_steps' => $get('first_login_steps'),
            'closing_text' => $get('closing_text'),
            'translations' => $get('translations'),
        ];
    }
}
