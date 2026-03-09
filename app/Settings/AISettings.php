<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class AISettings extends Settings
{
    public bool $open_ai_enabled;

    public ?string $open_ai_api_key;

    public ?int $open_ai_completion_max_tokens;

    public ?float $open_ai_completion_temperature;

    public ?string $open_ai_model;

    public static function group(): string
    {
        return 'ai';
    }
}
