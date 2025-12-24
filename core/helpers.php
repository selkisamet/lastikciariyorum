<?php

/**
 * Helper fonksiyonlar
 */

/**
 * .env dosyasını yükler ve parse eder
 */
function loadEnv($filePath = null) {
    static $loaded = false;

    if ($loaded) {
        return;
    }

    if ($filePath === null) {
        $filePath = __DIR__ . '/../.env';
    }

    if (!file_exists($filePath)) {
        throw new Exception('.env dosyası bulunamadı: ' . $filePath);
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Yorum satırlarını atla
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // KEY=VALUE formatını parse et
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Tırnak işaretlerini temizle
            $value = trim($value, '"\'');

            // Environment variable olarak kaydet
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }

    $loaded = true;
}

/**
 * Environment değişkenini döndürür
 *
 * @param string $key Environment değişken adı
 * @param mixed $default Varsayılan değer (opsiyonel)
 * @param bool $required True ise değer zorunlu, yoksa exception fırlatır
 * @return mixed
 * @throws Exception
 */
function env($key, $default = null, $required = false) {
    static $envLoaded = false;

    if (!$envLoaded) {
        loadEnv();
        $envLoaded = true;
    }

    if (array_key_exists($key, $_ENV)) {
        $value = $_ENV[$key];

        // Boş string kontrolü - hassas bilgiler için boş olmamalı
        if ($required && trim($value) === '') {
            throw new Exception("Environment değişkeni '$key' boş olamaz!");
        }

        // Boolean değerleri dönüştür
        if (strtolower($value) === 'true') {
            return true;
        }
        if (strtolower($value) === 'false') {
            return false;
        }
        if (strtolower($value) === 'null') {
            return null;
        }

        return $value;
    }

    // Değişken tanımlanmamış
    if ($required) {
        throw new Exception("Zorunlu environment değişkeni '$key' .env dosyasında bulunamadı!");
    }

    return $default;
}

/**
 * Zorunlu environment değişkenini döndürür
 * Değişken yoksa veya boşsa exception fırlatır
 */
function envRequired($key) {
    return env($key, null, true);
}

/**
 * Site URL'ini döndürür
 */
function siteUrl() {
    static $siteUrl = null;

    if ($siteUrl === null) {
        $siteUrl = rtrim(envRequired('APP_URL'), '/');
    }

    return $siteUrl;
}

/**
 * Asset URL oluşturur (CSS, JS, images için)
 */
function asset($path) {
    return siteUrl() . '/' . ltrim($path, '/');
}

/**
 * URL oluşturur (sayfa linkleri için)
 */
function url($path = '') {
    return siteUrl() . '/' . ltrim($path, '/');
}

/**
 * Makale içeriğinden otomatik özet oluşturur
 *
 * @param string $content HTML içerik
 * @param int $maxChars Maksimum karakter sayısı (varsayılan: 100)
 * @param int $maxWords Maksimum kelime sayısı (varsayılan: 15)
 * @return string
 */
function generateExcerpt($content, $maxChars = 100, $maxWords = 15) {
    // HTML etiketlerini temizle
    $text = strip_tags($content);

    // HTML entity'lerini decode et (&ccedil; -> ç, &uuml; -> ü gibi)
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    // Fazla boşlukları temizle
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);

    // Boş içerik kontrolü
    if (empty($text)) {
        return '';
    }

    // Kelimelere ayır
    $words = explode(' ', $text);

    // Kelime sayısına göre kısalt
    if (count($words) > $maxWords) {
        $words = array_slice($words, 0, $maxWords);
        $text = implode(' ', $words) . '...';
    }

    // Karakter sayısına göre kısalt
    if (mb_strlen($text) > $maxChars) {
        $text = mb_substr($text, 0, $maxChars);

        // Son kelimeyi tam göster (kelime ortasında kesmeyi önle)
        $lastSpace = mb_strrpos($text, ' ');
        if ($lastSpace !== false) {
            $text = mb_substr($text, 0, $lastSpace);
        }

        $text .= '...';
    }

    return $text;
}

/**
 * Makale özeti döndürür. Özet yoksa içerikten otomatik oluşturur
 *
 * @param string|null $excerpt Mevcut özet
 * @param string $content Makale içeriği
 * @return string
 */
function getArticleExcerpt($excerpt, $content) {
    if (!empty($excerpt)) {
        return $excerpt;
    }

    return generateExcerpt($content);
}

/**
 * Türkçe karakterleri normalize ederek küçük harfe çevirir
 * JavaScript'teki normalizeTurkish() ile aynı mantıkta çalışır
 *
 * @param string $text Normalize edilecek metin
 * @return string Normalize edilmiş metin
 */
function normalizeTurkish($text) {
    if (empty($text)) {
        return '';
    }

    // Türkçe büyük harfleri küçük harflerine dönüştür
    $text = str_replace(
        ['İ', 'I', 'Ş', 'Ğ', 'Ü', 'Ö', 'Ç'],
        ['i', 'ı', 'ş', 'ğ', 'ü', 'ö', 'ç'],
        $text
    );

    // Standart küçük harfe çevir
    return mb_strtolower($text, 'UTF-8');
}
