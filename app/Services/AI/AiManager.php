<?php

namespace App\Services\AI;

use App\Models\AiSetting;
use App\Services\AI\Providers\UniversalProvider;
use App\Services\AI\Providers\GeminiProvider;
use Exception;

class AiManager
{
    /**
     * Resolves and returns the currently active AI provider based on settings.
     *
     * @return AiProviderInterface
     * @throws Exception
     */
    public static function provider(): AiProviderInterface
    {
        $settings = AiSetting::current();

        if (!$settings) {
            throw new Exception("AI Settings have not been configured.");
        }

        switch ($settings->api_type) {
            case 'openai_compatible':
                return new UniversalProvider(
                    $settings->base_url ?? 'https://api.openai.com/v1',
                    $settings->api_key ?? '',
                    $settings->model_name ?? 'gpt-4o'
                );
            
            case 'gemini':
                return new GeminiProvider(
                    $settings->api_key ?? '',
                    $settings->model_name ?? 'gemini-1.5-pro'
                );
            
            default:
                throw new Exception("Unknown API Type selected: " . $settings->api_type);
        }
    }
}
