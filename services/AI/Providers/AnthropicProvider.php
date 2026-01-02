<?php

require_once __DIR__ . '/../AIProviderInterface.php';

/**
 * Anthropic Claude Provider
 *
 * Claude API entegrasyonu (mevcut AIService'ten taşındı)
 */
class AnthropicProvider extends AIProviderInterface
{
    /**
     * Authentication header'larını döndür
     */
    protected function getAuthHeaders()
    {
        $headers = [
            'Content-Type: application/json',
            'x-api-key: ' . $this->apiKey
        ];

        // provider_config'den versiyonu al
        if (isset($this->config['headers']['anthropic-version'])) {
            $headers[] = 'anthropic-version: ' . $this->config['headers']['anthropic-version'];
        } else {
            $headers[] = 'anthropic-version: 2023-06-01';
        }

        return $headers;
    }

    /**
     * İstek formatını hazırla (Anthropic API formatı)
     */
    protected function formatRequest($prompt, $params)
    {
        return [
            'model' => $params['model'] ?? $this->model,
            'max_tokens' => (int)($params['max_tokens'] ?? $this->maxTokens),
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ]
        ];
    }

    /**
     * API yanıtını parse et
     */
    protected function parseResponse($response)
    {
        $data = json_decode($response, true);

        if (!isset($data['content'][0]['text'])) {
            throw new Exception('Invalid Anthropic API response format');
        }

        return $data['content'][0]['text'];
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
                'message' => 'Claude API connection successful!',
                'test_article' => $testArticle
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Keyword önerileri üret
     */
    public function generateKeywordSuggestions($city, $district = null, $primaryKeyword = 'lastikçi')
    {
        $location = $district ? "{$district['name']}, {$city['name']}" : $city['name'];
        $locationName = $district ? $district['name'] : $city['name'];

        $prompt = <<<PROMPT
Lastik servisleri için DESTEKLEYİCİ SEO anahtar kelime önerileri üret.

KONUM: {$location}

TALİMAT:
Lastik servisleri/tamiri için {$locationName} bölgesine özel 10 adet DESTEKLEYİCİ anahtar kelime öner.

ÖNEMLİ:
- "{$primaryKeyword}" kelimesi ANA ANAHTAR KELİME olacak (BUNU ÖNERME!)
- Sadece DESTEKLEYICI/NİTELEYİCİ kelimeler öner

KURALLAR:
1. Her keyword tek başına anlamlı olmalı (lokasyon adı EKLEME, sadece keyword'ü yaz)
2. Sadece keyword'leri döndür, açıklama yapma
3. Her satıra bir keyword
4. Türkçe karakterleri doğru kullan (ı, ş, ğ, ü, ö, ç)
5. "{$primaryKeyword}" kelimesini ASLA kullanma (bu ana keyword olacak)
6. Gerçekçi ve arama hacmi yüksek kelimeler seç

Doğru format örnekleri (her satırda bir tane):
7/24 lastik servisi
mobil lastik tamiri
açık lastik tamircisi
lastik değişimi
lastik patlaması yardımı
oto lastik bakımı
araç lastik tamiri
yol yardım lastik
acil lastik tamiri
lastik hava basıncı kontrolü

SADECE 10 DESTEKLEYİCİ KEYWORD DÖNDÜR (her satırda bir tane):
PROMPT;

        $requestData = $this->formatRequest($prompt, ['max_tokens' => 500]);
        $response = $this->sendRequest($requestData);
        $textContent = $this->parseResponse($response);

        // Extract keywords from response
        $keywords = array_filter(
            array_map('trim', explode("\n", trim($textContent))),
            function($line) {
                return !empty($line) && !str_starts_with($line, '#') && !str_starts_with($line, '-');
            }
        );

        // Take first 10
        $keywords = array_slice(array_values($keywords), 0, 10);

        return $keywords;
    }
}
