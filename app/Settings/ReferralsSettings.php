<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ReferralsSettings extends Settings
{
    public bool $enabled;

    public bool $disable_for_non_verified;

    public bool $auto_follow_the_user;

    public ?int $fee_percentage;

    public ?int $apply_for_months;

    public ?int $fee_limit;

    public ?string $referrals_default_link_page;

    public static function group(): string
    {
        return 'referrals';
    }
}
