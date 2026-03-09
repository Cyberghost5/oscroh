<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SocialSettings extends Settings
{
    // Login
    public ?string $facebook_client_id;

    public ?string $facebook_secret;

    public ?string $twitter_client_id;

    public ?string $twitter_secret;

    public ?string $google_client_id;

    public ?string $google_secret;

    // Links
    public ?string $facebook_url;

    public ?string $instagram_url;

    public ?string $twitter_url;

    public ?string $whatsapp_url;

    public ?string $tiktok_url;

    public ?string $youtube_url;

    public ?string $telegram_link;

    public ?string $reddit_url;

    public static function group(): string
    {
        return 'social';
    }
}
