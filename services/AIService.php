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
     * Prompt builder (Professional Local SEO for listing pages)
     */
    private function buildPrompt($location, $keywords, $wordCount, $city, $district, $primaryKeyword = null)
    {
        $locationName = $district ?? $city;
        $districtInfo = $district ? "İlçe: {$district}" : "İl geneli";

        // Destekleyici keyword listesi
        $keywordList = implode("\n- ", $keywords);

        return <<<PROMPT
Sen Türkiye'de yerel hizmet siteleri (lastikçi, oto servis vb.) üzerine uzmanlaşmış, Google Local SEO, Programmatic SEO ve Helpful Content sistemlerini bilen üst düzey bir SEO stratejisti ve profesyonel içerik yazarısın.

Bu içerik:
- Blog yazısı DEĞİLDİR
- Haber DEĞİLDİR
- Satış sayfası DEĞİLDİR

Bu bir LASTİKÇİ FİRMA LİSTELEME SAYFASININ ÜST İÇERİĞİDİR.
Amaç: Kullanıcıyı doğru bilgilendirmek ve aşağıda yer alan firma listesine mantıklı ve güvenli şekilde yönlendirmektir.

---

## 1️⃣ KONUM BİLGİSİ

- İl: {$city}
- İlçe: {$district}
{$districtInfo}

Kurallar:
- İlçe doluysa → içerik TAMAMEN ilçe odaklıdır (İl adı sadece bağlam için 1-2 kez geçer)
- İlçe boşsa → içerik il geneli içindir (İlçeler sadece örnek olarak anılabilir)

---

## 2️⃣ PRIMARY KEYWORD

Primary Keyword: {$primaryKeyword}

KULLANIM:
- H1 → "{$locationName} {$primaryKeyword}"
- İlk paragraf → 1 kez
- Tüm içerikte toplam → 6-8 kez (doğal, keyword stuffing YOK)

---

## 3️⃣ DESTEKLEYİCİ KAVRAMLAR (ZORLAMADAN)

Aşağıdaki terimleri doğal şekilde kullan:
- {$keywordList}

Kurallar:
- Her biri 1-3 kez
- Aynı paragrafta en fazla 1 anahtar ifade
- Gereksiz H2 açma ZORUNLU DEĞİL

---

## 4️⃣ KESİN GUARDRAIL (ÇOK ÖNEMLİ)

AŞAĞIDAKILER KESİNLİKLE YASAK:

❌ Keyword stuffing
❌ Aynı cümlede il + ilçe + keyword tekrarları
❌ "En iyi / en ucuz / garantili" gibi iddialar
❌ Fiyat, kampanya, firma adı, yorum uydurma
❌ Blogvari uzun girişler
❌ Satış CTA'ları

Dil:
- Bilgilendirici
- Tarafsız
- Güven veren
- Abartısız

---

## 5️⃣ UX + SEO BİRLEŞİK YAPI

Bu içerik:
- Kullanıcının arama niyetini açıklar
- Neden firma listesine bakması gerektiğini mantıkla hazırlar

Akış:
1. Kullanıcı ihtiyacı
2. Hizmet türleri ve senaryolar
3. Doğru lastikçi seçimi
4. Firma listesine geçiş hissi

Direkt çağrı yok. "Liste aşağıda" hissi doğal verilir.

---

## 6️⃣ ZORUNLU SAYFA İSKELETİ

H1: {$locationName} {$primaryKeyword}

GİRİŞ:
- Bölgedeki lastik ihtiyacı
- Acil durumlara kısa vurgu

H2: {$locationName} Bölgesinde Lastikçi Hizmetleri

H2: Sunulan Lastik Hizmetleri
  (ul / li listesi)

H2: Lastikçi Seçerken Nelere Dikkat Edilmeli

H2: Sık Sorulan Sorular
  • 3-5 kısa soru & net cevap

KAPANIŞ:
- Kısa özet
- Firma listesinin devamında olduğu hissi

---

## 7️⃣ MİKRO LOKAL GERÇEKLİK

İçerikte 1-2 kısa cümleyle:
- Trafik yoğunluğu
- Yolda kalma riski
- Ana yollar / sanayi bölgeleri

Abartı YOK.

---

## 8️⃣ HTML KURALLARI

SADECE şu etiketler: h1, h2, h3, p, strong, ul, li

❌ div
❌ span
❌ markdown
❌ stil / class

---

## 9️⃣ SEO META

Meta Title:
- 50-60 karakter
- Primary keyword içerir

Meta Description:
- 150-160 karakter
- Konum + hizmet + güven hissi

---

## 🔟 HEDEF KELİME SAYISI

Toplam: {$wordCount} kelime

---

## ÇIKTI (SADECE JSON)

JSON DIŞINDA HİÇBİR ŞEY YAZMA.

{
  "title": "H1 başlığı (primary keyword içermeli)",
  "content": "HTML formatında makale içeriği",
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
    private function sendRequest($prompt, $maxTokens = null)
    {
        $data = [
            'model' => $this->model,
            'max_tokens' => $maxTokens ?? $this->maxTokens,
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
    public function generateBulkArticles($locations, $primaryKeyword, $supportingKeywords = [], $wordCount = 1500)
    {
        $results = [];

        foreach ($locations as $location) {
            try {
                $params = [
                    'city' => $location['city_name'],
                    'district' => $location['district_name'] ?? null,
                    'primary_keyword' => $primaryKeyword,
                    'keywords' => $supportingKeywords, // Supporting keywords (can be empty)
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
     * @param string $primaryKeyword Primary keyword to exclude from suggestions
     * @return array Array of 10 keyword suggestions
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

        try {
            $response = $this->sendRequest($prompt, 500); // Short response

            // Parse JSON response to get text content
            $data = json_decode($response, true);

            if (!isset($data['content'][0]['text'])) {
                throw new Exception('API yanıtı beklenmeyen formatta');
            }

            $textContent = $data['content'][0]['text'];

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
        } catch (Exception $e) {
            throw new Exception('Keyword önerileri üretilemedi: ' . $e->getMessage());
        }
    }
}
