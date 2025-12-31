<?php

/**
 * Migration Cleanup Script
 *
 * Amaç: SEO yapısal değişiklik öncesi duplicate makaleleri temizlemek
 *
 * İşlemler:
 * 1. Aynı il/ilçeye ait birden fazla makale tespit et
 * 2. En iyi makaleyi KORU (en uzun, en çok görüntülenen, en yeni)
 * 3. Diğer makaleleri SİL
 * 4. Silinen makale URL'lerini redirects tablosuna ekle
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Article.php';
require_once __DIR__ . '/../models/City.php';
require_once __DIR__ . '/../models/District.php';
require_once __DIR__ . '/../models/Redirect.php';

echo "=" . str_repeat("=", 70) . "=\n";
echo "  SEO Migration Cleanup - Duplicate Articles\n";
echo "=" . str_repeat("=", 70) . "=\n\n";

$articleModel = new Article();
$cityModel = new City();
$districtModel = new District();
$redirectModel = new Redirect();

$db = Database::getInstance()->getConnection();

// İstatistikler
$stats = [
    'total_duplicates' => 0,
    'kept_articles' => 0,
    'deleted_articles' => 0,
    'redirects_created' => 0,
    'errors' => []
];

echo "Adım 1: Duplicate makaleleri tespit et...\n";

// İl seviyesi duplicate makaleler
$sql = "SELECT city_id, COUNT(*) as count
        FROM articles
        WHERE district_id IS NULL
        GROUP BY city_id
        HAVING count > 1";

$cityDuplicates = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

echo "  - İl seviyesi duplicate: " . count($cityDuplicates) . " şehir\n";

// İlçe seviyesi duplicate makaleler
$sql = "SELECT city_id, district_id, COUNT(*) as count
        FROM articles
        WHERE district_id IS NOT NULL
        GROUP BY city_id, district_id
        HAVING count > 1";

$districtDuplicates = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

echo "  - İlçe seviyesi duplicate: " . count($districtDuplicates) . " ilçe\n\n";

$stats['total_duplicates'] = count($cityDuplicates) + count($districtDuplicates);

if ($stats['total_duplicates'] === 0) {
    echo "✓ Duplicate makale bulunamadı. Sistem temiz!\n\n";
    exit(0);
}

echo "Adım 2: En iyi makaleleri seç ve diğerlerini temizle...\n\n";

// İl seviyesi cleanup
foreach ($cityDuplicates as $duplicate) {
    $cityId = $duplicate['city_id'];
    $city = $cityModel->find($cityId);

    echo "  İl: {$city['name']} ({$duplicate['count']} makale)\n";

    // Tüm makaleleri getir
    $sql = "SELECT *,
                   LENGTH(content) as content_length
            FROM articles
            WHERE city_id = ? AND district_id IS NULL
            ORDER BY content_length DESC, view_count DESC, created_at DESC";

    $articles = $db->prepare($sql);
    $articles->execute([$cityId]);
    $articles = $articles->fetchAll(PDO::FETCH_ASSOC);

    // İlk makale = EN İYİ (en uzun, en çok görüntülenen, en yeni)
    $bestArticle = $articles[0];
    $stats['kept_articles']++;

    echo "    ✓ Korunan: {$bestArticle['title']} ({$bestArticle['content_length']} karakter, {$bestArticle['view_count']} görüntülenme)\n";

    // Diğerlerini sil ve redirect oluştur
    for ($i = 1; $i < count($articles); $i++) {
        $article = $articles[$i];

        // Redirect oluştur
        $oldUrl = "/{$city['slug']}/{$article['slug']}";
        $newUrl = "/{$city['slug']}";

        try {
            $redirectModel->createRedirect($oldUrl, $newUrl, 301);
            $stats['redirects_created']++;
            echo "      → Redirect: {$oldUrl} → {$newUrl}\n";
        } catch (Exception $e) {
            $stats['errors'][] = "Redirect hatası ({$oldUrl}): " . $e->getMessage();
        }

        // Makaleyi sil
        $deleteSql = "DELETE FROM articles WHERE id = ?";
        $db->prepare($deleteSql)->execute([$article['id']]);
        $stats['deleted_articles']++;

        echo "      ✗ Silinen: {$article['title']}\n";
    }

    echo "\n";
}

// İlçe seviyesi cleanup
foreach ($districtDuplicates as $duplicate) {
    $cityId = $duplicate['city_id'];
    $districtId = $duplicate['district_id'];

    $city = $cityModel->find($cityId);
    $district = $districtModel->find($districtId);

    echo "  İlçe: {$district['name']}, {$city['name']} ({$duplicate['count']} makale)\n";

    // Tüm makaleleri getir
    $sql = "SELECT *,
                   LENGTH(content) as content_length
            FROM articles
            WHERE city_id = ? AND district_id = ?
            ORDER BY content_length DESC, view_count DESC, created_at DESC";

    $articles = $db->prepare($sql);
    $articles->execute([$cityId, $districtId]);
    $articles = $articles->fetchAll(PDO::FETCH_ASSOC);

    // İlk makale = EN İYİ
    $bestArticle = $articles[0];
    $stats['kept_articles']++;

    echo "    ✓ Korunan: {$bestArticle['title']} ({$bestArticle['content_length']} karakter, {$bestArticle['view_count']} görüntülenme)\n";

    // Diğerlerini sil ve redirect oluştur
    for ($i = 1; $i < count($articles); $i++) {
        $article = $articles[$i];

        // Redirect oluştur
        $oldUrl = "/{$city['slug']}/{$district['slug']}/{$article['slug']}";
        $newUrl = "/{$city['slug']}/{$district['slug']}";

        try {
            $redirectModel->createRedirect($oldUrl, $newUrl, 301);
            $stats['redirects_created']++;
            echo "      → Redirect: {$oldUrl} → {$newUrl}\n";
        } catch (Exception $e) {
            $stats['errors'][] = "Redirect hatası ({$oldUrl}): " . $e->getMessage();
        }

        // Makaleyi sil
        $deleteSql = "DELETE FROM articles WHERE id = ?";
        $db->prepare($deleteSql)->execute([$article['id']]);
        $stats['deleted_articles']++;

        echo "      ✗ Silinen: {$article['title']}\n";
    }

    echo "\n";
}

// Sonuç özeti
echo "=" . str_repeat("=", 70) . "=\n";
echo "  ÖZET\n";
echo "=" . str_repeat("=", 70) . "=\n\n";

echo "  Toplam Duplicate Grup:  {$stats['total_duplicates']}\n";
echo "  Korunan Makale:         {$stats['kept_articles']}\n";
echo "  Silinen Makale:         {$stats['deleted_articles']}\n";
echo "  Oluşturulan Redirect:   {$stats['redirects_created']}\n";

if (!empty($stats['errors'])) {
    echo "\n  HATALAR:\n";
    foreach ($stats['errors'] as $error) {
        echo "    - {$error}\n";
    }
}

echo "\n✓ Migration tamamlandı!\n\n";
echo "Sonraki adım: database/add_unique_constraints.sql dosyasını çalıştırın.\n\n";
