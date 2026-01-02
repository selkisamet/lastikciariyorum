<?php

require_once __DIR__ . '/AIProviderInterface.php';

/**
 * AI Provider Factory
 *
 * Factory pattern ile provider örnekleri oluşturur.
 */
class AIProviderFactory
{
    /**
     * Provider'ı isme göre oluştur
     *
     * @param string $providerName Provider name (anthropic, openai, vb.)
     * @return AIProviderInterface Provider instance
     * @throws Exception Provider bulunamazsa veya yapılandırılmamışsa
     */
    public static function create($providerName)
    {
        require_once __DIR__ . '/../../models/AIProviderSetting.php';
        $model = new AIProviderSetting();
        $settings = $model->getByName($providerName);

        if (!$settings) {
            throw new Exception("Provider not found: {$providerName}");
        }

        return self::createFromSettings($settings);
    }

    /**
     * Provider'ı database settings'den oluştur
     *
     * @param array $settings Database row from ai_provider_settings
     * @return AIProviderInterface Provider instance
     * @throws Exception Bilinmeyen provider tipi
     */
    public static function createFromSettings($settings)
    {
        $providerName = $settings['provider_name'] ?? 'unknown';

        switch ($providerName) {
            case 'anthropic':
                require_once __DIR__ . '/Providers/AnthropicProvider.php';
                return new AnthropicProvider($settings);

            case 'openai':
                require_once __DIR__ . '/Providers/OpenAIProvider.php';
                return new OpenAIProvider($settings);

            case 'kilocode':
                require_once __DIR__ . '/Providers/KiloCodeProvider.php';
                return new KiloCodeProvider($settings);

            case 'gemini':
                require_once __DIR__ . '/Providers/GeminiProvider.php';
                return new GeminiProvider($settings);

            default:
                throw new Exception("Unknown provider type: {$providerName}");
        }
    }

    /**
     * Tüm aktif provider'ları oluştur
     *
     * @return array AIProviderInterface[] instances
     */
    public static function createAllActive()
    {
        require_once __DIR__ . '/../../models/AIProviderSetting.php';
        $model = new AIProviderSetting();
        $activeProviders = $model->getActiveProviders();

        $providers = [];
        foreach ($activeProviders as $settings) {
            try {
                $providers[] = self::createFromSettings($settings);
            } catch (Exception $e) {
                // Skip providers with configuration errors
                continue;
            }
        }

        return $providers;
    }

    /**
     * Varsayılan provider'ı oluştur
     *
     * @return AIProviderInterface|null
     */
    public static function createDefault()
    {
        require_once __DIR__ . '/../../models/AIProviderSetting.php';
        $model = new AIProviderSetting();
        $defaultProvider = $model->getDefaultProvider();

        if (!$defaultProvider) {
            // Varsayılan yoksa ilk aktif provider'ı kullan
            $activeProviders = $model->getActiveProviders();
            if (empty($activeProviders)) {
                throw new Exception('No active AI providers configured');
            }
            $defaultProvider = $activeProviders[0];
        }

        return self::createFromSettings($defaultProvider);
    }

    /**
     * Desteklenen provider listesi
     *
     * @return array Provider name => Display name mapping
     */
    public static function getSupportedProviders()
    {
        return [
            'anthropic' => 'Claude (Anthropic)',
            'openai' => 'ChatGPT (OpenAI)',
            'kilocode' => 'Kilo Code',
            'gemini' => 'Google Gemini'
        ];
    }
}
