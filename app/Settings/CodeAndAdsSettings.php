<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class CodeAndAdsSettings extends Settings
{
    public ?string $custom_css;

    public ?string $custom_js;

    public ?string $sidebar_ad_spot;

    public static function group(): string
    {
        return 'code-and-ads';
    }
}
