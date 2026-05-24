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

    // AI Auto Reply settings
    public bool $ai_auto_reply_enabled;

    public ?string $ai_auto_reply_model;

    public ?string $ai_auto_reply_system_prompt;

    public ?int $ai_auto_reply_max_per_conversation;

    public ?int $ai_auto_reply_context_messages;

    public static function group(): string
    {
        return 'ai';
    }
}
