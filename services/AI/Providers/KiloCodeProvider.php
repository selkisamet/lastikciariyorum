<?php

require_once __DIR__ . '/../AIProviderInterface.php';

/**
 * Kilo Code Provider
 *
 * Kilo Code (Türk yapımı AI) entegrasyonu
 * NOT: API dokümantasyonu gerçek detaylara göre güncellenmelidir
 */
class KiloCodeProvider extends AIProviderInterface
{
    /**
     * Authentication header'larını döndür
     */
    protected function getAuthHeaders()
    {
        return [
            'Content-Type: application/json',
            'X-API-Key: ' . $this->apiKey
        ];
    }

    /**
     * İstek formatını hazırla (Kilo Code API formatı - varsayılan)
     * NOT: Gerçek API dokümantasyonuna göre güncellenmelidir
     */
    protected function formatRequest($prompt, $params)
    {
        $requestData = [
            'model' => $params['model'] ?? $this->model,
            'max_tokens' => (int)($params['max_tokens'] ?? $this->maxTokens),
            'temperature' => (float)($params['temperature'] ?? $this->temperature),
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ]
        ];

        // Config'den language ve region ekle
        if (isset($this->config['language'])) {
            $requestData['language'] = $this->config['language'];
        }
        if (isset($this->config['region'])) {
            $requestData['region'] = $this->config['region'];
        }

        return $requestData;
    }

    /**
     * API yanıtını parse et
     * NOT: Gerçek API dokümantasyonuna göre güncellenmelidir
     */
    protected function parseResponse($response)
    {
        $data = json_decode($response, true);

        // Varsayılan format (gerçek API'ye göre değişebilir)
        if (isset($data['data']['text'])) {
            return $data['data']['text'];
        } elseif (isset($data['choices'][0]['message']['content'])) {
            // OpenAI-like format
            return $data['choices'][0]['message']['content'];
        } elseif (isset($data['content'][0]['text'])) {
            // Anthropic-like format
            return $data['content'][0]['text'];
        } else {
            throw new Exception('Invalid Kilo Code API response format');
        }
    }

    /**
     * Makale üret
     */
    public function generateArticle($params)
    {
        $city = $params['city'] ?? '';
        $district = $params['district'] ?? null;
        $keywords = $params['keywords'] ?? [];
        $wordCount = $params['word_count'] ?? 1500;
        $primaryKeyword = $params['primary_keyword'] ?? null;

        // Location string oluştur
        $location = $district ? "{$district}, {$city}" : $city;

        // Keyword template: Eğer keywords boş ise standart template kullan
        if (empty($keywords)) {
            $locationName = $district ?? $city;
            $keywords = [
                $locationName . ' lastikçi',
                $locationName . ' 7/24 lastikçi',
                $locationName . ' mobil lastikçi',
                $locationName . ' açık lastikçi',
                $locationName . ' lastik tamiri'
            ];
        }

        // Prompt oluştur
        $prompt = $this->buildPrompt($location, $keywords, $wordCount, $city, $district, $primaryKeyword);

        // API'ye istek gönder
        $requestData = $this->formatRequest($prompt, $params);
        $response = $this->sendRequest($requestData);

        // Parse response
        $contentText = $this->parseResponse($response);

        // JSON'ı extract et (```json wrapper varsa temizle)
        $contentText = preg_replace('/^```json\s*/m', '', $contentText);
        $contentText = preg_replace('/\s*```$/m', '', $contentText);
        $contentText = trim($contentText);

        $articleData = json_decode($contentText, true);

        if (!$articleData) {
            throw new Exception('Article data parse failed: ' . json_last_error_msg());
        }

        // Required fields kontrolü
        $requiredFields = ['title', 'content', 'excerpt', 'meta_title', 'meta_description'];
        foreach ($requiredFields as $field) {
            if (!isset($articleData[$field]) || empty($articleData[$field])) {
                throw new Exception("Missing field: {$field}");
            }
        }

        return $articleData;
    }

    /**
     * API bağlantısını test et
     */
    public function testConnection()
    {
        try {
            $testArticle = $this->generateArticle([
                'city' => 'İstanbul',
                'district' => 'Kadıköy',
                'keywords' => ['Kadıköy lastikçi'],
                'word_count' => 400
            ]);

            return [
                'success' => true,
                'message' => 'Kilo Code API connection successful!',
                'test_article' => $testArticle
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
