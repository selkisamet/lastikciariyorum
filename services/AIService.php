<?php

/**
 * AI Service - Anthropic Claude API Integration
 *
 * Bu sınıf Anthropic Claude API kullanarak SEO-optimized makale içerikleri üretir.
 */
class AIService
{
    private $apiKey;
    private $apiUrl = 'https://api.anthropic.com/v1/messages';
    // API limitlerinize göre: Claude Sonnet 4 and 4.5 (30K input, 8K output, 50 req/min)
    private $model = 'claude-sonnet-4-20250514'; // Claude Sonnet 4.5 (en yeni ve güçlü)
    private $maxTokens = 8000; // Limitiniz: 8K output tokens

    public function __construct()
    {
        // API key'i .env veya config'den al
        $this->apiKey = env('ANTHROPIC_API_KEY');

        if (empty($this->apiKey)) {
            throw new Exception('ANTHROPIC_API_KEY bulunamadı. Lütfen .env dosyasına ekleyin.');
        }
    }

    /**
     * Makale üret (Multi-keyword support)
     *
     * @param array $params {
     *     @type string $city İl adı
     *     @type string $district İlçe adı (opsiyonel)
     *     @type array $keywords Anahtar kelimeler (array, boş ise template kullanılır)
     *     @type int $wordCount Hedef kelime sayısı (varsayılan: 1500)
     * }
     * @return array {
     *     @type string $title Makale başlığı
     *     @type string $content Makale içeriği (HTML)
     *     @type string $excerpt Kısa özet
     *     @type string $meta_title SEO meta title
     *     @type string $meta_description SEO meta description
     * }
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

        // Prompt oluştur (multi-keyword with primary keyword support)
        $prompt = $this->buildPrompt($location, $keywords, $wordCount, $city, $district, $primaryKeyword);

        // API'ye istek gönder
        try {
            $response = $this->sendRequest($prompt);

            // JSON yanıtını parse et
            $articleData = $this->parseResponse($response);

            return $articleData;
        } catch (Exception $e) {
            throw new Exception('AI makale üretimi başarısız: ' . $e->getMessage());
        }
    }

    /**
     * Prompt builder (Multi-keyword support with primary keyword)
     */
    private function buildPrompt($location, $keywords, $wordCount, $city, $district, $primaryKeyword = null)
    {
        $districtInfo = $district ? "İlçe: {$district}" : "İl geneli";
        $locationName = $district ?? $city;

        // Keyword listesini formatla
        $keywordList = implode("\n- ", $keywords);

        // Her keyword için hedef kelime sayısını hesapla
        $keywordCount = count($keywords);
        $wordsPerSection = (int)($wordCount / ($keywordCount + 2)); // +2 = giriş + sonuç

        // Primary keyword section
        $primaryKeywordSection = '';
        if ($primaryKeyword) {
            $primaryKeywordSection = <<<PRIMARY

ANA ANAHTAR KELİME: {$primaryKeyword}
⚠️ ZORUNLU KURALLAR:
1. H1 başlığı MUTLAKA şu formatta olmalı: "{$locationName} {$primaryKeyword}"
2. İlk paragrafta MUTLAKA "{$locationName} {$primaryKeyword}" geçmeli
3. URL slug'ında MUTLAKA "{$primaryKeyword}" kelimesi bulunmalı
4. Ana anahtar kelime içerik boyunca dengeli şekilde kullanılmalı (keyword density: %2-3)
PRIMARY;
        }

        return <<<PROMPT
Sen SEO uzmanı bir içerik yazarısın. Lastik servisleri hakkında UZUN ve DETAYLI bir HUB makalesi yazacaksın.

KONUM: {$location}
{$districtInfo}
{$primaryKeywordSection}

DİĞER ANAHTAR KELİMELER (HEPSİNİ TEK MAKALEDE DOĞAL ŞEKİLDE KULLAN):
- {$keywordList}

HEDEF KELİME SAYISI: {$wordCount} kelime (UZUN İÇERİK)

YAZIŞ KURALLARI:
1. Bu bir HUB sayfası - kullanıcıya MAKSIMUM değer sağla
2. TÜM anahtar kelimeleri organik şekilde kullan (keyword stuffing YAPMA)
3. Her keyword için ayrı H2 bölümü oluştur
4. Yerel SEO için konum adını doğal şekilde yerleştir
5. Kullanıcıya pratik, faydalı bilgiler ver (fiyatlar, saatler, hizmetler)
6. Paragraflar kısa ve okunaklı olmalı
7. HTML formatında yaz (p, h2, h3, strong, ul, li etiketleri kullan)
8. Türkçe dil kurallarına uy

MAKALE YAPISI (Her bölüm ~{$wordsPerSection} kelime):
1. GİRİŞ (~{$wordsPerSection} kelime): {$location} bölgesinde lastik hizmetlerine genel bakış
2. KEYWORD BÖLÜMÜ 1 - H2: {$keywords[0]} hakkında detaylı bilgi
3. KEYWORD BÖLÜMÜ 2 - H2: {$keywords[1]} hakkında detaylı bilgi
4. KEYWORD BÖLÜMÜ 3 - H2: {$keywords[2]} hakkında detaylı bilgi
5. KEYWORD BÖLÜMÜ 4 - H2: {$keywords[3]} hakkında detaylı bilgi
6. KEYWORD BÖLÜMÜ 5 - H2: {$keywords[4]} hakkında detaylı bilgi
7. SONUÇ (~{$wordsPerSection} kelime): Özet ve harekete geçirici mesaj

SEO İPUCLARI:
- Her H2 başlığında anahtar kelimeleri kullan
- İçerik bilgilendirici ve derinlemesine olmalı
- "Thin content" olmamalı - kullanıcıya DEĞER kat
- Duplicate content riskinden kaçın - özgün yaz

YANIT FORMATI (JSON):
Yanıtını SADECE şu JSON formatında ver, başka hiçbir şey yazma:

{
  "title": "Makale başlığı (60-70 karakter, ana anahtar kelime içermeli)",
  "content": "HTML formatında makale içeriği (~{$wordCount} kelime)",
  "excerpt": "150-200 karakterlik kısa özet",
  "meta_title": "SEO meta title (50-60 karakter)",
  "meta_description": "SEO meta description (150-160 karakter)"
}

ÖNEMLİ: Yanıt SADECE geçerli JSON olmalı. Markdown code block (```json) kullanma, direkt JSON objesi döndür.
PROMPT;
    }

    /**
     * API'ye istek gönder
     */
    private function sendRequest($prompt)
    {
        $data = [
            'model' => $this->model,
            'max_tokens' => $this->maxTokens,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ]
        ];

        $ch = curl_init($this->apiUrl);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-api-key: ' . $this->apiKey,
                'anthropic-version: 2023-06-01'
            ],
            CURLOPT_TIMEOUT => 60 // 60 saniye timeout
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        curl_close($ch);

        if ($curlError) {
            throw new Exception('cURL hatası: ' . $curlError);
        }

        if ($httpCode !== 200) {
            $errorMsg = 'API hatası (HTTP ' . $httpCode . ')';
            $errorData = json_decode($response, true);

            // Debug: Tam hata yanıtını göster
            if ($errorData) {
                if (isset($errorData['error']['message'])) {
                    $errorMsg .= ': ' . $errorData['error']['message'];
                }
                if (isset($errorData['error']['type'])) {
                    $errorMsg .= ' (Type: ' . $errorData['error']['type'] . ')';
                }
                // Tam yanıtı da ekle
                $errorMsg .= ' | Response: ' . $response;
            } else {
                $errorMsg .= ' | Raw response: ' . $response;
            }
            throw new Exception($errorMsg);
        }

        return $response;
    }

    /**
     * API yanıtını parse et
     */
    private function parseResponse($response)
    {
        $data = json_decode($response, true);

        if (!isset($data['content'][0]['text'])) {
            throw new Exception('API yanıtı beklenmeyen formatta');
        }

        $contentText = $data['content'][0]['text'];

        // JSON'ı extract et (```json wrapper varsa temizle)
        $contentText = preg_replace('/^```json\s*/m', '', $contentText);
        $contentText = preg_replace('/\s*```$/m', '', $contentText);
        $contentText = trim($contentText);

        $articleData = json_decode($contentText, true);

        if (!$articleData) {
            throw new Exception('Makale verisi parse edilemedi: ' . json_last_error_msg());
        }

        // Required fields kontrolü
        $requiredFields = ['title', 'content', 'excerpt', 'meta_title', 'meta_description'];
        foreach ($requiredFields as $field) {
            if (!isset($articleData[$field]) || empty($articleData[$field])) {
                throw new Exception("Eksik alan: {$field}");
            }
        }

        return $articleData;
    }

    /**
     * API durumunu test et
     */
    public function testConnection()
    {
        try {
            $testArticle = $this->generateArticle([
                'city' => 'İstanbul',
                'district' => 'Kadıköy',
                'keywords' => ['Kadıköy lastikçi'], // Simple test keyword
                'word_count' => 400 // Test için kısa
            ]);

            return [
                'success' => true,
                'message' => 'API bağlantısı başarılı!',
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
     * Toplu makale üret (Multi-keyword template support)
     *
     * @param array $locations [['city_id' => 1, 'city_name' => 'İstanbul', 'district_id' => 1, 'district_name' => 'Kadıköy'], ...]
     * @param array $keywordTemplates Template array (empty için standart template kullanılır)
     * @param int $wordCount Hedef kelime sayısı (varsayılan: 1500)
     * @return array Başarı/hata listesi
     */
    public function generateBulkArticles($locations, $keywordTemplates = [], $wordCount = 1500)
    {
        $results = [];

        foreach ($locations as $location) {
            try {
                $params = [
                    'city' => $location['city_name'],
                    'district' => $location['district_name'] ?? null,
                    'keywords' => $keywordTemplates, // Empty = use template
                    'word_count' => $wordCount
                ];

                $article = $this->generateArticle($params);

                $results[] = [
                    'success' => true,
                    'location' => $location,
                    'article' => $article
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
     * Anahtar kelime önerileri üret
     *
     * @param array $city City data
     * @param array|null $district District data (optional)
     * @return array Array of 10 keyword suggestions
     */
    public function generateKeywordSuggestions($city, $district = null)
    {
        $location = $district ? "{$district['name']}, {$city['name']}" : $city['name'];
        $locationName = $district ? $district['name'] : $city['name'];

        $prompt = <<<PROMPT
Lastik servisleri için SEO anahtar kelime önerileri üret.

KONUM: {$location}

TALİMAT:
Lastik servisleri/tamiri için {$locationName} bölgesine özel 10 adet SEO-uyumlu anahtar kelime öner.

KURALLAR:
1. Her keyword tek başına anlamlı olmalı (lokasyon adı EKLEME, sadece keyword'ü yaz)
2. Sadece keyword'leri döndür, açıklama yapma
3. Her satıra bir keyword
4. Türkçe karakterleri doğru kullan (ı, ş, ğ, ü, ö, ç)
5. Gerçekçi ve arama hacmi yüksek kelimeler seç

Örnek format:
lastikçi
7/24 lastikçi
mobil lastikçi
açık lastikçi
lastik tamiri
lastik değişimi
lastik patlaması
oto lastik
araç lastik
lastik bakımı

SADECE 10 KEYWORD DÖNDÜR (her satırda bir tane):
PROMPT;

        try {
            $response = $this->sendRequest($prompt, 500); // Short response

            // Extract keywords from response
            $keywords = array_filter(
                array_map('trim', explode("\n", trim($response))),
                function($line) {
                    return !empty($line) && !str_starts_with($line, '#') && !str_starts_with($line, '-');
                }
            );

            // Take first 10
            $keywords = array_slice(array_values($keywords), 0, 10);

            return $keywords;
        } catch (Exception $e) {
            throw new Exception('Keyword önerileri üretilemedi: ' . $e->getMessage());
        }
    }
}
