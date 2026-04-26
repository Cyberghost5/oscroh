<?php

namespace App\Filament\Pages\Settings;

use App\Settings\SocialSettings;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use BackedEnum;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class ManageSocialSettings extends SettingsPage
{
    use HasPageShield;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-share';

    protected static ?string $slug = 'settings/social';

    protected static string $settings = SocialSettings::class;

    protected static ?string $title = 'Social Media Settings';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Social Settings')
                ->columnSpanFull()
                ->tabs([
                    Tabs\Tab::make('Social Login')
                        ->columns(2)
                        ->schema([

                            Placeholder::make('social_login_info')
                                ->columnSpanFull()
                                ->label('')
                                ->hiddenLabel()
                                ->content(new HtmlString(view('filament.partials.social-login-info-box')->render())),

                            TextInput::make('facebook_client_id')->label('Facebook client ID'),
                            TextInput::make('facebook_secret')->label('Facebook client secret'),

                            TextInput::make('twitter_client_id')->label('Twitter client ID'),
                            TextInput::make('twitter_secret')->label('Twitter client secret'),

                            TextInput::make('google_client_id')->label('Google client ID'),
                            TextInput::make('google_secret')->label('Google client secret'),
                        ]),

                    Tabs\Tab::make('Social Links')
                        ->columns(2)
                        ->schema([
                            TextInput::make('facebook_url')->label('Facebook URL'),
                            TextInput::make('instagram_url')->label('Instagram URL'),
                            TextInput::make('twitter_url')->label('Twitter URL'),
                            TextInput::make('whatsapp_url')->label('WhatsApp URL'),
                            TextInput::make('tiktok_url')->label('TikTok URL'),
                            TextInput::make('youtube_url')->label('YouTube URL'),
                            TextInput::make('telegram_link')->label('Telegram URL'),
                            TextInput::make('reddit_url')->label('Reddit URL'),
                        ]),
                ]),
        ]);
    }
}
