<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $name;

    public ?string $description;

    public ?string $slogan;

    public ?string $light_logo;

    public ?string $dark_logo;

    public ?string $favicon;

    public ?string $default_og_image;

    public ?string $login_page_background_image;

    public bool $allow_theme_switch;

    public string $default_user_theme;

    public bool $allow_direction_switch;

    public string $default_site_direction;

    public bool $allow_language_switch;

    public string $default_site_language;

    public string $homepage_type;

    public ?string $homepage_redirect;

    public bool $enforce_user_identity_checks;

    public bool $enforce_email_validation;

    public bool $allow_pwa_installs;

    public bool $hide_identity_checks;

    public bool $hide_create_post_menu;

    public bool $hide_stream_create_menu;

    public bool $use_browser_language_if_available;

    public bool $enable_smooth_page_change_transitions;

    public string $redirect_page_after_register;

    public string $timezone;

//    public string $app_url;
    public ?string $app_url = null;

    public static function group(): string
    {
        return 'site';
    }
}
