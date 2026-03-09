<?php

namespace App\Filament\Pages\Settings;

use App\Settings\SecuritySettings;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use BackedEnum;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class ManageSecuritySettings extends SettingsPage
{
    use HasPageShield;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $slug = 'settings/security';

    protected static string $settings = SecuritySettings::class;

    protected static ?string $title = 'Security Settings';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Security Settings')
                ->columnSpanFull()
                ->tabs([
                    Tabs\Tab::make('General')
                        ->columns(2)
                        ->schema([

                            Toggle::make('enable_2fa')
                                ->label('Enable 2FA')
                                ->helperText('Adds an email-based 2FA step via email when users log in.')
                                ->columnSpanFull(),

                            Toggle::make('default_2fa_on_register')
                                ->label('Enabled 2FA on register')
                                ->helperText('Automatically enable 2FA for new registrations.')
                                ->columnSpanFull(),

                            Toggle::make('allow_users_2fa_switch')
                                ->label('Allow users to disable 2FA')
                                ->helperText('Allowing users to be able to change their 2FA settings.')
                                ->columnSpanFull(),

                            Toggle::make('enforce_app_ssl')
                                ->label('Enforce SSL')
                                ->helperText('Redirect all traffic to HTTPS. Not necessary on most hosting providers.')
                                ->columnSpanFull(),

                        ]),

                    Tabs\Tab::make('Captcha')
                        ->columns(2)
                        ->schema([
                            Select::make('captcha_driver')
                                ->label('Captcha driver')
                                ->options([
                                    'none' => 'None',
                                    'turnstile' => 'Cloudflare Turnstile',
                                    'hcaptcha' => 'hCaptcha',
                                    'recaptcha' => 'Google reCAPTCHA',
                                ])
                                ->default('none')
                                ->reactive()
                                ->placeholder('Select a driver')
                                ->helperText('Select which captcha system to use for authentication forms.')
                                ->columnSpanFull(),

                            // === reCAPTCHA ===
                            TextInput::make('recaptcha_site_key')
                                ->label('Site key')
                                ->visible(fn ($get) => $get('captcha_driver') === 'recaptcha'),

                            TextInput::make('recaptcha_site_secret_key')
                                ->label('Secret key')
                                ->visible(fn ($get) => $get('captcha_driver') === 'recaptcha'),

                            // === Turnstile ===
                            TextInput::make('turnstile_site_key')
                                ->label('Site key')
                                ->visible(fn ($get) => $get('captcha_driver') === 'turnstile'),

                            TextInput::make('turnstile_site_secret_key')
                                ->label('Secret key')
                                ->visible(fn ($get) => $get('captcha_driver') === 'turnstile'),

                            // === hCaptcha ===
                            TextInput::make('hcaptcha_site_key')
                                ->label('Site key')
                                ->visible(fn ($get) => $get('captcha_driver') === 'hcaptcha'),

                            TextInput::make('hcaptcha_site_secret_key')
                                ->label('Secret key')
                                ->visible(fn ($get) => $get('captcha_driver') === 'hcaptcha'),
                        ]),

                    Tabs\Tab::make('Geo-blocking')
                        ->columns(2)
                        ->schema([
                            Toggle::make('allow_geo_blocking')
                                ->label('Enable Geo-blocking')
                                ->helperText("If enabled, users will be able to disallow certain countries to access their content."),

                            TextInput::make('abstract_api_key')
                                ->label('Abstract API key')
                                ->helperText('Used to detect and block users by region (via Abstract API).')
                                ->placeholder('Your Abstract API Key'),

                        ]),

                    Tabs\Tab::make('Email deliverability')
                        ->columns(2)
                        ->schema([
                            Toggle::make('enforce_email_valid_check')
                                ->label('Validate emails on register')
                                ->helperText('Requires valid, deliverable email during registration.'),

                            TextInput::make('email_abstract_api_key')
                                ->label('Abstract API key')
                                ->helperText('Used for validating email addresses on signup (via Abstract API).')
                                ->placeholder('Your Abstract API Key'),

                        ]),

                ]),
        ]);
    }
}
