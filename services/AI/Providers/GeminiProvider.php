<?php

require_once __DIR__ . '/../AIProviderInterface.php';

/**
 * Google Gemini Provider
 *
 * Google Gemini API entegrasyonu
 */
class GeminiProvider extends AIProviderInterface
{
    /**
     * Gemini için özel API URL (model dahil, key URL'de)
     */
    protected function getApiUrl()
    {
        // Gemini API'de key URL parametresinde gidiyor
        $modelName = $this->model;
        return "{$this->apiUrl}{$modelName}:generateContent?key={$this->apiKey}";
    }

    /**
     * Authentication header'larını döndür
     * Gemini'de API key URL'de olduğu için header'da yok
     */
    protected function getAuthHeaders()
    {
        return [
            'Content-Type: application/json'
        ];
    }

    /**
     * İstek formatını hazırla (Gemini API formatı)
     */
    protected function formatRequest($prompt, $params)
    {
        $generationConfig = [
            'maxOutputTokens' => (int)($params['max_tokens'] ?? $this->maxTokens),
            'temperature' => (float)($params['temperature'] ?? $this->temperature)
        ];

        $requestData = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => $generationConfig
        ];

        // Safety settings varsa ekle
        if (isset($this->config['safety_settings'])) {
            $requestData['safetySettings'] = $this->config['safety_settings'];
        }

        return $requestData;
    }

    /**
     * API yanıtını parse et
     */
    protected function parseResponse($response)
    {
        $data = json_decode($response, true);

        if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            throw new Exception('Invalid Gemini API response format');
        }

        return $data['candidates'][0]['content']['parts'][0]['text'];
    }

    /**
     * cURL ile API'ye istek gönder (Gemini için override - URL farklı)
     */
    protected function sendRequest($data)
    {
        $apiUrl = $this->getApiUrl(); // Model ve key dahil URL

        $ch = curl_init($apiUrl);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $this->getAuthHeaders(),
            CURLOPT_TIMEOUT => $this->timeout
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        curl_close($ch);

        if ($curlError) {
            throw new Exception("cURL error: {$curlError}");
        }

        if ($httpCode !== 200) {
            $errorMsg = "HTTP {$httpCode}";
            $errorData = json_decode($response, true);

            if ($errorData && isset($errorData['error']['message'])) {
                $errorMsg .= ": " . $errorData['error']['message'];
            } elseif ($errorData && isset($errorData['error'])) {
                $errorMsg .= ": " . json_encode($errorData['error']);
            } else {
                $errorMsg .= ": " . substr($response, 0, 200);
            }

            throw new Exception($errorMsg);
        }

        return $response;
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
                'message' => 'Gemini API connection successful!',
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
