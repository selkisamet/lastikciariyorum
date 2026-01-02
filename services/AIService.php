<?php

require_once __DIR__ . '/AI/AIProviderFactory.php';
require_once __DIR__ . '/../models/AIProviderSetting.php';

/**
 * AI Service - Multi-Provider with Failover
 *
 * Çoklu AI sağlayıcı desteği ve otomatik failover ile makale üretimi.
 * Mevcut AIService'in yeniden yapılandırılmış versiyonu.
 */
class AIService
{
    private $settingsModel;

    public function __construct()
    {
        $this->settingsModel = new AIProviderSetting();
    }

    /**
     * Makale üret (Multi-keyword support + Automatic Failover)
     *
     * @param array $params {
     *     @type string $city İl adı
     *     @type string $district İlçe adı (opsiyonel)
     *     @type array $keywords Anahtar kelimeler (array, boş ise template kullanılır)
     *     @type int $word_count Hedef kelime sayısı (varsayılan: 1500)
     *     @type string $primary_keyword Ana anahtar kelime
     * }
     * @return array {
     *     @type string $title Makale başlığı
     *     @type string $content Makale içeriği (HTML)
     *     @type string $excerpt Kısa özet
     *     @type string $meta_title SEO meta title
     *     @type string $meta_description SEO meta description
     * }
     * @throws Exception Tüm sağlayıcılar başarısız olursa
     */
    public function generateArticle($params)
    {
        // Aktif sağlayıcıları öncelik sırasına göre al
        $providers = $this->settingsModel->getActiveProviders();

        if (empty($providers)) {
            throw new Exception('Aktif AI sağlayıcı bulunamadı. Lütfen admin panelden en az bir sağlayıcıyı aktif edin.');
        }

        $errors = [];
        $attemptedProviders = [];

        // Her sağlayıcıyı sırayla dene (öncelik sırasına göre)
        foreach ($providers as $providerSettings) {
            try {
                // Provider örneğini oluştur
                $provider = AIProviderFactory::createFromSettings($providerSettings);
                $attemptedProviders[] = $provider->getName();

                // Makale üret
                $startTime = microtime(true);
                $articleData = $provider->generateArticle($params);
                $responseTime = round((microtime(true) - $startTime) * 1000); // ms

                // Başarılı - istatistikleri güncelle
                $this->settingsModel->updateStats(
                    $providerSettings['id'],
                    true, // success
                    null, // no error
                    $responseTime
                );

                // Session'a hangi provider kullanıldığını kaydet (eğer session başlamışsa)
                if (session_status() === PHP_SESSION_NONE) {
                    @session_start(); // Suppress warning if headers already sent
                }
                if (session_status() === PHP_SESSION_ACTIVE) {
                    $_SESSION['ai_provider_used'] = $provider->getName();
                    $_SESSION['ai_response_time'] = $responseTime;
                }

                // Başarıyla döndür
                return $articleData;

            } catch (Exception $e) {
                // Hata - istatistikleri güncelle
                $this->settingsModel->updateStats(
                    $providerSettings['id'],
                    false, // failed
                    $e->getMessage()
                );

                // Hatayı kaydet ve sonraki provider'a geç
                $errors[] = $providerSettings['display_name'] . ': ' . $e->getMessage();
                continue;
            }
        }

        // Tüm sağlayıcılar başarısız oldu
        $errorMessage = sprintf(
            'Tüm AI sağlayıcılar başarısız oldu (%d denendi: %s). Hatalar: %s',
            count($attemptedProviders),
            implode(', ', $attemptedProviders),
            implode(' | ', $errors)
        );

        throw new Exception($errorMessage);
    }

    /**
     * Toplu makale üret (Multi-keyword template support + Failover)
     *
     * @param array $locations [['city_id' => 1, 'city_name' => 'İstanbul', 'district_id' => 1, 'district_name' => 'Kadıköy'], ...]
     * @param string $primaryKeyword Ana anahtar kelime
     * @param array $supportingKeywords Destekleyici anahtar kelimeler (can be empty)
     * @param int $wordCount Hedef kelime sayısı (varsayılan: 1500)
     * @return array Başarı/hata listesi
     */
    public function generateBulkArticles($locations, $primaryKeyword, $supportingKeywords = [], $wordCount = 1500)
    {
        $results = [];

        foreach ($locations as $location) {
            try {
                $params = [
                    'city' => $location['city_name'],
                    'district' => $location['district_name'] ?? null,
                    'primary_keyword' => $primaryKeyword,
                    'keywords' => $supportingKeywords,
                    'word_count' => $wordCount
                ];

                // generateArticle() zaten failover yapacak
                $article = $this->generateArticle($params);

                $results[] = [
                    'success' => true,
                    'location' => $location,
                    'article' => $article,
                    'provider_used' => $_SESSION['ai_provider_used'] ?? 'Unknown'
                ];

                // Rate limiting: API'ye yük bindirmemek için kısa bekleme
                usleep(500000); // 0.5 saniye

            } catch (Exception $e) {
                $results[] = [
                    'success' => false,
                    'location' => $location,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Anahtar kelime önerileri üret (Failover)
     *
     * @param array $city City data
     * @param array|null $district District data (optional)
     * @param string $primaryKeyword Primary keyword to exclude from suggestions
     * @return array Array of 10 keyword suggestions
     * @throws Exception Tüm sağlayıcılar başarısız olursa
     */
    public function generateKeywordSuggestions($city, $district = null, $primaryKeyword = 'lastikçi')
    {
        // Aktif sağlayıcıları al
        $providers = $this->settingsModel->getActiveProviders();

        if (empty($providers)) {
            throw new Exception('Aktif AI sağlayıcı bulunamadı.');
        }

        $errors = [];

        foreach ($providers as $providerSettings) {
            try {
                $provider = AIProviderFactory::createFromSettings($providerSettings);

                // Keyword önerileri üret
                $keywords = $provider->generateKeywordSuggestions($city, $district, $primaryKeyword);

                // Başarılı - istatistikleri güncelle
                $this->settingsModel->updateStats($providerSettings['id'], true, null);

                return $keywords;

            } catch (Exception $e) {
                // Hata - istatistikleri güncelle
                $this->settingsModel->updateStats($providerSettings['id'], false, $e->getMessage());
                $errors[] = $providerSettings['display_name'] . ': ' . $e->getMessage();
                continue;
            }
        }

        // Tüm sağlayıcılar başarısız
        throw new Exception('Keyword önerileri üretilemedi: ' . implode(' | ', $errors));
    }

    /**
     * Belirli bir sağlayıcıyı test et
     *
     * @param string $providerName Provider name (anthropic, openai, vb.)
     * @return array Test sonucu
     */
    public function testProvider($providerName)
    {
        try {
            $provider = AIProviderFactory::create($providerName);
            return $provider->testConnection();
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * API durumunu test et (eski testConnection metodu - geri uyumluluk için)
     * Varsayılan sağlayıcıyı test eder
     *
     * @return array Test sonucu
     */
    public function testConnection()
    {
        try {
            $defaultProvider = $this->settingsModel->getDefaultProvider();

            if (!$defaultProvider) {
                // Varsayılan yoksa ilk aktif provider'ı kullan
                $activeProviders = $this->settingsModel->getActiveProviders();
                if (empty($activeProviders)) {
                    return [
                        'success' => false,
                        'message' => 'Aktif AI sağlayıcı bulunamadı.'
                    ];
                }
                $defaultProvider = $activeProviders[0];
            }

            return $this->testProvider($defaultProvider['provider_name']);

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Tüm aktif sağlayıcıların durumunu kontrol et
     *
     * @return array Provider status array
     */
    public function getProvidersStatus()
    {
        $providers = $this->settingsModel->getActiveProviders();
        $status = [];

        foreach ($providers as $provider) {
            $status[] = [
                'name' => $provider['display_name'],
                'provider_name' => $provider['provider_name'],
                'is_active' => $provider['is_active'] == 1,
                'is_default' => $provider['is_default'] == 1,
                'priority' => $provider['priority'],
                'success_count' => $provider['success_count'],
                'error_count' => $provider['error_count'],
                'last_used_at' => $provider['last_used_at'],
                'has_api_key' => !empty($provider['api_key'])
            ];
        }

        return $status;
    }
}
