<?php

namespace App\Providers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AiServiceProvider extends ServiceProvider
{
    public const OPEN_AI_BASE_URL = 'https://api.openai.com/v1';
    public const OPEN_AI_COMPLETION_PATH = '/completions';
    public const OPEN_AI_CHAT_COMPLETION_PATH = '/chat/completions';

    // Updated models as of 2025
    public const CHAT_MODELS = [
        'gpt-5-chat-latest',
        'gpt-4o',
        'gpt-4o-mini',
        'o3',
    ];

    /**
     * Register any application services.
     */
    public function register()
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
    }

    public static function generateCompletionRequest(string $key)
    {
        $suggestion = "";

        if (getSetting('ai.open_ai_enabled')) {
            $httpClient = new Client();

            $model = getSetting('ai.open_ai_model');
            $endpointPath = self::getEndpointPath($model);
            $requestBody = self::buildRequestBody($key, $model);

            $chatGptRequest = $httpClient->request(
                'POST',
                self::OPEN_AI_BASE_URL.$endpointPath,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer '.getSetting('ai.open_ai_api_key'),
                    ],
                    'body' => json_encode($requestBody),
                ]
            );

            $response = json_decode($chatGptRequest->getBody(), true);

            if (isset($response['choices'][0])) {
                $suggestion = trim($response['choices'][0]['text'] ?? $response['choices'][0]['message']['content'] ?? '');
            }
        }

        return $suggestion;
    }

    private static function getEndpointPath(string $model)
    {
        return in_array($model, self::CHAT_MODELS) ? self::OPEN_AI_CHAT_COMPLETION_PATH : self::OPEN_AI_COMPLETION_PATH;
    }

    private static function buildRequestBody(string $key, string $model)
    {
        return in_array($model, self::CHAT_MODELS)
            ? self::buildChatCompletionRequestBody($key, $model)
            : self::buildCompletionRequestBody($key, $model);
    }

    private static function buildCompletionRequestBody(string $key, string $model)
    {
        return [
            'model' => $model,
            'prompt' => $key,
            'temperature' => self::getChatGptTemperatureValue(),
            'max_tokens' => self::getChatGptMaxTokensValue(),
        ];
    }

    private static function buildChatCompletionRequestBody(string $key, string $model)
    {
        $body = [
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => $key],
            ],
            'temperature' => self::getChatGptTemperatureValue(),
        ];

        // Only add max_tokens for models that support it
        if (!in_array($model, ['o4-mini', 'o3'])) {
            $body['max_tokens'] = self::getChatGptMaxTokensValue();
        }

        return $body;
    }

    public static function getChatGptTemperatureValue()
    {
        $temperature = 1;
        if (getSetting('ai.open_ai_temperature')) {
            $settingValue = intval(getSetting('ai.open_ai_temperature'));
            if ($settingValue >= 0 && $settingValue <= 2) {
                $temperature = $settingValue;
            }
        }

        return $temperature;
    }

    public static function getChatGptMaxTokensValue()
    {
        $maxTokens = 100;
        if (getSetting('ai.open_ai_completion_max_tokens')) {
            $settingValue = intval(getSetting('ai.open_ai_completion_max_tokens'));
            if ($settingValue > 0 && $settingValue <= 4096) {
                $maxTokens = $settingValue;
            }
        }

        return $maxTokens;
    }
}
