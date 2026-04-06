<?php

return [
    'sections' => [
        'behavior' => 'Behavior',
        'links' => 'Links',
        'first_login' => 'First Login',
        'localization' => 'Localization',
    ],
    'fields' => [
        'enabled' => 'Enable custom onboarding email',
        'subject' => 'Email subject',
        'heading' => 'Email heading',
        'intro_text' => 'Intro text',
        'setup_button_label' => 'Setup button label',
        'setup_button_url' => 'Setup button URL override',
        'welcome_url' => 'Getting started URL',
        'welcome_link_label' => 'Getting started label',
        'discord_url' => 'Discord URL',
        'community_url' => 'Community URL',
        'support_url' => 'Support URL',
        'extra_links' => 'Extra links',
        'link_label' => 'Label',
        'link_url' => 'URL',
        'first_login_steps' => 'First-login instructions',
        'closing_text' => 'Closing text',
        'translations' => 'Locale overrides',
        'locale' => 'Locale',
    ],
    'help' => [
        'placeholders' => 'Available placeholders: :app, :name, :username, :email, :panel_url, :setup_url',
        'intro_text' => 'Shown near the top of the welcome email.',
        'setup_button_url' => 'Optional. Leave empty to use Pelican\'s normal reset-password link. You can use placeholders like :setup_url, :panel_url, :email, and :username.',
        'one_per_line' => 'Enter one step per line.',
        'localization' => 'Add locale-specific overrides like en, en-us, de, or fr. Empty fields fall back to the default content above.',
        'locale' => 'Use a locale code such as en, en-us, de, or fr.',
    ],
    'actions' => [
        'add_link' => 'Add link',
        'add_translation' => 'Add locale override',
        'send_test_email' => 'Send Test Email',
    ],
    'notifications' => [
        'test_sent_title' => 'Test onboarding email sent',
        'test_sent_body' => 'A test onboarding email was sent to :email.',
    ],
];
