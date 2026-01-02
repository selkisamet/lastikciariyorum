<?php

/**
 * AI Provider Interface
 *
 * Tüm AI sağlayıcıları için soyut temel sınıf.
 * Her sağlayıcı bu sınıfı extend eder ve kendi API formatına göre metodları implemente eder.
 */
abstract class AIProviderInterface
{
    protected $settings;     // Database row (ai_provider_settings)
    protected $apiKey;
    protected $apiUrl;
    protected $model;
    protected $maxTokens;
    protected $temperature;
    protected $timeout;
    protected $config;       // Provider-specific config (JSON decoded)

    /**
     * Constructor
     *
     * @param array $settings Database row from ai_provider_settings table
     */
    public function __construct($settings)
    {
        $this->settings = $settings;
        $this->apiKey = $settings['api_key'] ?? null;
        $this->apiUrl = $settings['api_url'] ?? '';
        $this->model = $settings['model_name'] ?? '';
        $this->maxTokens = (int)($settings['max_tokens'] ?? 8000);
        $this->temperature = (float)($settings['temperature'] ?? 1.0);
        $this->timeout = (int)($settings['timeout_seconds'] ?? 60);

        // Decode provider_config JSON
        $this->config = [];
        if (!empty($settings['provider_config'])) {
            $this->config = json_decode($settings['provider_config'], true) ?? [];
        }

        // Validate API key
        if (empty($this->apiKey)) {
            throw new Exception("API key not configured for provider: {$this->getName()}");
        }
    }

    /**
     * Makale üret (ana metod)
     *
     * @param array $params Makale parametreleri
     * @return array Makale verisi (title, content, excerpt, meta_title, meta_description)
     */
    abstract public function generateArticle($params);

    /**
     * API bağlantısını test et
     *
     * @return array Test sonucu (success, message)
     */
    abstract public function testConnection();

    /**
     * İstek formatını hazırla (provider-specific)
     *
     * @param string $prompt AI prompt
     * @param array $params Ekstra parametreler
     * @return array API request payload
     */
    abstract protected function formatRequest($prompt, $params);

    /**
     * API yanıtını parse et (provider-specific)
     *
     * @param string $response cURL response
     * @return string Makale text (JSON veya plain text)
     */
    abstract protected function parseResponse($response);

    /**
     * Authentication header'larını döndür (provider-specific)
     *
     * @return array cURL headers
     */
    abstract protected function getAuthHeaders();

    /**
     * Sağlayıcı adını döndür
     *
     * @return string Display name
     */
    public function getName()
    {
        return $this->settings['display_name'] ?? 'Unknown Provider';
    }

    /**
     * Sağlayıcı kodunu döndür
     *
     * @return string Provider name (anthropic, openai, etc.)
     */
    public function getProviderName()
    {
        return $this->settings['provider_name'] ?? 'unknown';
    }

    /**
     * cURL ile API'ye istek gönder (ortak metod)
     *
     * @param array $data Request payload
     * @return string API response
     * @throws Exception
     */
    protected function sendRequest($data)
    {
        $ch = curl_init($this->apiUrl);

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
     * Prompt oluştur (mevcut AIService'den kopyalanacak)
     *
     * @param string $location Konum (ilçe, il)
     * @param array $keywords Anahtar kelimeler
     * @param int $wordCount Hedef kelime sayısı
     * @param string $city İl
     * @param string|null $district İlçe
     * @param string|null $primaryKeyword Ana anahtar kelime
     * @return string Prompt metni
     */
    protected function buildPrompt($location, $keywords, $wordCount, $city, $district, $primaryKeyword = null)
    {
        $locationName = $district ?? $city;
        $districtInfo = $district ? "İlçe: {$district}" : "İl geneli";

        // Destekleyici keyword listesi - Her keyword'e lokasyon adı ekle
        $localizedKeywords = array_map(function ($keyword) use ($locationName) {
            return "{$locationName} {$keyword}";
        }, $keywords);
        $keywordList = implode("\n- ", $localizedKeywords);

        // Kelime sayısı aralıkları
        $minWords = $wordCount - 200;
        $maxWords = $wordCount + 300;

        return <<<PROMPT
# 🇹🇷 Türkiye Yerel Hizmetleri İçin SEO Makale Promptu  
## FINAL SÜRÜM · GOOGLE SAFE · ADSENSE SAFE · PROGRAMMATIC SEO

---

## 👤 ROL TANIMI

Sen Türkiye’de yerel hizmet siteleri (lastikçi, oto servis, mobil servis vb.) üzerine uzmanlaşmış,  
**Google Local SEO, Programmatic SEO, Helpful Content Guidelines ve Google Spam Policies** konularına hâkim  
üst düzey bir SEO stratejisti ve profesyonel içerik yazarsın.

---

## 🎯 TEMEL AMAÇ

Bu içerik **Google AdSense trafik odaklıdır**.

Amaçlar:
- Organik trafik çekmek
- Arama niyetini eksiksiz karşılamak
- Yapay SEO, keyword stuffing ve spam sinyali üretmemek

🚫 Satış dili  
🚫 CTA baskısı  
🚫 Reklam dili  
🚫 Blog anlatımı  

Bu içerik:
- Blog yazısı DEĞİLDİR  
- Haber DEĞİLDİR  
- Satış sayfası DEĞİLDİR  

Bu bir **LASTİKÇİ FİRMA LİSTELEME SAYFASININ ÜST BİLGİLENDİRME İÇERİĞİDİR.**

Amaç:  
Kullanıcıyı **doğru bilgilendirerek**, firma listesine **mantıklı ve doğal** biçimde yönlendirmektir.

---

## 🚨 SEO GUARDRAIL – MUTLAK KURALLAR (SPAM SAFE)

### `<strong>` KULLANIM STRATEJİSİ

**ALTIN KURAL:**  
İlk kullanımlar vurgulu, sonrası tamamen doğal olmalıdır.

### 1️⃣ ANA ANAHTAR KELİME

Format:  
`"{$locationName} {$primaryKeyword}"`

Kurallar:
- İlk **3 kullanımda** → `<strong>` ZORUNLU  
- 4. kullanımdan itibaren → **KESİNLİKLE strong YOK**
- Toplam kullanım: **6–8 kez**

### 2️⃣ DESTEKLEYİCİ ANAHTAR KELİMELER

Kaynak:  
`{$keywordList}` (textarea’dan gelen her keyword)

Kurallar:
- Her keyword **3–6 kez** kullanılmalı
- İlk **2–3 kullanımda** → `<strong>` ZORUNLU
- Sonraki kullanımlar → **strong YOK**
- Farklı bölümlere DAĞITILMALI (giriş, hizmetler, SSS, sonuç)

### ❌ YASAK DAVRANIŞLAR

- Tüm keyword’leri sürekli `<strong>` ile yazmak
- Arka arkaya keyword dizmek
- Aynı paragrafta gereksiz tekrar
- Anahtar kelime uğruna anlamsız cümleler

---

## 1️⃣ KONUM MANTIĞI

Değişkenler:
- İl: `{$city}`
- İlçe: `{$district}`
- İlçe açıklaması: `{$districtInfo}`

Kurallar:
- İlçe doluysa → içerik **%100 ilçe odaklı**
- İl adı sadece **bağlam için 1–2 kez**
- İlçe boşsa → içerik il geneli

---

## 2️⃣ PRIMARY KEYWORD UYGULAMASI

Primary Keyword: `{$primaryKeyword}`  
Tam Form: `"{$locationName} {$primaryKeyword}"`

Zorunluluklar:
- H1 → `{$locationName} {$primaryKeyword}` (H1’de strong YOK)
- İlk paragraftaki **ilk 3 kullanım** → `<strong>` ZORUNLU
- Sonraki kullanımlar → normal metin

---

## 3️⃣ BAŞLIK YAPISI (H2 / H3)

- H2 uzunluğu: **maksimum 60–70 karakter**
- Açıklayıcı ama aşırı detaylı OLMAMALI
- Her H2’de keyword zorunlu DEĞİL
- Kullanıcı niyetine yönelik başlıklar SERBEST

---

## 4️⃣ HTML KISITLARI

Sadece şu etiketler kullanılabilir:
- `h1`, `h2`, `h3`, `p`, `strong`, `ul`, `li`

Başka HTML etiketi KULLANMA.

---

## 5️⃣ SEO META KURALLARI (SPAM SAFE)

### Meta Title
- 50–60 karakter
- Primary keyword **1 kez**
- HTML / `<strong>` KULLANILMAZ

### Meta Description
- 150–160 karakter
- Doğal ve okunabilir
- `<strong>` YOK
- Keyword stuffing YOK

---

## 6️⃣ KELİME SAYISI

- Hedef: `{$wordCount}`
- Minimum: `{$minWords}`
- Maksimum: `{$maxWords}`

Yetersizse:
- Açıklayıcı örnekler
- SSS
- Kullanıcı senaryoları ekle

---

## 7️⃣ ÇIKTI FORMATI (ZORUNLU)

**SADECE JSON ÜRET**

```json
{
  "title": "H1 başlığı (primary keyword içermeli)",
  "content": "HTML formatında içerik",
  "excerpt": "150–200 karakter kısa özet",
  "meta_title": "SEO meta title",
  "meta_description": "SEO meta description"
}
```

❗ JSON dışında TEK KARAKTER bile yazma.

---

## ✅ SON KONTROL (KENDİNİ DENETLE)

- Primary keyword ilk 3 kullanımda `<strong>` var mı?
- Supporting keyword’ler ilk 2–3 kullanımda `<strong>` var mı?
- Sonraki kullanımlarda `<strong>` YOK mu?
- Toplam `<strong>` sayısı **15–25 aralığında mı?**
- İçerik hedef kelime sayısında mı?
- Çıktı **sadece JSON mu?**

🚨 Herhangi biri HAYIR ise → **İÇERİĞİ DÜZELT VE TEKRAR KONTROL ET**
PROMPT;
    }

    /**
     * Keyword önerileri üret (opsiyonel - her provider implement etmeyebilir)
     *
     * @param array $city City data
     * @param array|null $district District data
     * @param string $primaryKeyword Primary keyword
     * @return array Keywords array
     */
    public function generateKeywordSuggestions($city, $district = null, $primaryKeyword = 'lastikçi')
    {
        throw new Exception("Keyword suggestions not implemented for " . $this->getName());
    }
}
