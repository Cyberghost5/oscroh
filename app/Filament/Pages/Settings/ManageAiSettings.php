<?php

namespace App\Filament\Pages\Settings;

use App\Settings\AiSettings;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use BackedEnum;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class ManageAiSettings extends SettingsPage
{
    use HasPageShield;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $slug = 'settings/ai';

    protected static string $settings = AiSettings::class;

    protected static ?string $title = 'AI Settings';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('AI Settings')
                ->columnSpanFull()
                ->tabs([

                    Tabs\Tab::make('General')
                        ->schema([
                            Toggle::make('open_ai_enabled')
                                ->label('Enable OpenAI')
                                ->columnSpanFull()
                                ->helperText('Master switch for all OpenAI-powered features on this platform.'),

                            Select::make('open_ai_model')
                                ->label('OpenAI model')
                                ->options([
                                    'gpt-5-chat-latest' => 'GPT 5',
                                    'gpt-4o'            => 'GPT 4o',
                                    'gpt-4o-mini'       => 'GPT 4o-mini',
                                    'o3'                => 'GPT 3',
                                ])
                                ->required()
                                ->helperText('Select the OpenAI model to be used. For more details and pricing, check OpenAI docs.'),

                            TextInput::make('open_ai_api_key')
                                ->label('OpenAI API key')
                                ->password()
                                ->revealable()
                                ->required(),

                            TextInput::make('open_ai_completion_max_tokens')
                                ->label('Max tokens')
                                ->numeric()
                                ->required()
                                ->helperText("Dictates how long the suggestion should be. E.g. 1000 tokens is about 750 words. (shouldn't exceed 2048 tokens)."),

                            TextInput::make('open_ai_completion_temperature')
                                ->label('Temperature')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(2)
                                ->step(0.1)
                                ->required()
                                ->helperText('What sampling temperature to use, between 0 and 2. Higher values like 0.8 will make the output more random, while lower values like 0.2 will make it more focused and deterministic.'),
                        ])
                        ->columns(2),

                    Tabs\Tab::make('Auto Reply')
                        ->schema([
                            Toggle::make('ai_auto_reply_enabled')
                                ->label('Enable AI Auto Reply (Global)')
                                ->columnSpanFull()
                                ->helperText('Master switch for the AI auto-reply feature. Individual creators must also have it enabled on their profile.'),

                            Select::make('ai_auto_reply_model')
                                ->label('Auto Reply model')
                                ->options([
                                    'gpt-5-chat-latest' => 'GPT 5',
                                    'gpt-4o'            => 'GPT 4o',
                                    'gpt-4o-mini'       => 'GPT 4o-mini',
                                    'o3'                => 'GPT 3',
                                ])
                                ->required()
                                ->helperText('The OpenAI model used specifically for auto-reply generation.'),

                            TextInput::make('ai_auto_reply_max_per_conversation')
                                ->label('Max AI replies per conversation')
                                ->numeric()
                                ->minValue(1)
                                ->required()
                                ->helperText('Maximum number of AI-generated replies allowed per unique fan–creator conversation. Prevents runaway token costs.'),

                            TextInput::make('ai_auto_reply_context_messages')
                                ->label('Context message history count')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(50)
                                ->required()
                                ->helperText('How many of the most recent messages to send to the AI as conversation context.'),

                            Textarea::make('ai_auto_reply_system_prompt')
                                ->label('Default system prompt')
                                ->columnSpanFull()
                                ->rows(8)
                                ->helperText(
                                    'The default instruction given to the AI on how to behave as the creator. ' .
                                    'Individual creators can override this with their own prompt. ' .
                                    'Tip: instruct the AI to be flirty, engaging, and naturally encourage the fan to send a tip.'
                                )
                                ->placeholder(
                                    "You are {creator_name}, a popular content creator. " .
                                    "Reply in a warm, flirty, and engaging tone as if you are personally chatting with a fan. " .
                                    "Keep replies short (1–3 sentences). " .
                                    "Where natural, hint that you appreciate tips and that tipping makes you more likely to reply with exclusive content."
                                ),
                        ])
                        ->columns(2),

                ]),
        ]);
    }
}
