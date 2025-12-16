<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';

header('Content-Type: application/xml; charset=utf-8');

try {
    $db = Database::getInstance();
    $baseUrl = 'https://lastikciariyorum.com';

    echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
    <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
        <!-- Ana Sayfa -->
        <url>
            <loc><?= $baseUrl ?>/</loc>
            <lastmod><?= date('Y-m-d') ?></lastmod>
            <changefreq>daily</changefreq>
            <priority>1.0</priority>
        </url>

        <!-- Firma Ekle Sayfası -->
        <url>
            <loc><?= $baseUrl ?>/firma-ekle</loc>
            <lastmod><?= date('Y-m-d') ?></lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.8</priority>
        </url>

        <!-- İletişim -->
        <url>
            <loc><?= $baseUrl ?>/iletisim</loc>
            <lastmod><?= date('Y-m-d') ?></lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.6</priority>
        </url>

        <!-- Gizlilik Politikası -->
        <url>
            <loc><?= $baseUrl ?>/gizlilik-politikasi</loc>
            <lastmod><?= date('Y-m-d') ?></lastmod>
            <changefreq>yearly</changefreq>
            <priority>0.3</priority>
        </url>

        <!-- Çerez Politikası -->
        <url>
            <loc><?= $baseUrl ?>/cerez-politikasi</loc>
            <lastmod><?= date('Y-m-d') ?></lastmod>
            <changefreq>yearly</changefreq>
            <priority>0.3</priority>
        </url>

        <!-- KVKK Aydınlatma -->
        <url>
            <loc><?= $baseUrl ?>/kvkk-aydinlatma-metni</loc>
            <lastmod><?= date('Y-m-d') ?></lastmod>
            <changefreq>yearly</changefreq>
            <priority>0.3</priority>
        </url>

        <?php
        // Şehirler
        try {
            $cities = $db->fetchAll("SELECT slug, updated_at FROM cities ORDER BY name");
            foreach ($cities as $city):
                if (!empty($city['slug'])):
        ?>
                    <url>
                        <loc><?= $baseUrl ?>/<?= htmlspecialchars($city['slug']) ?></loc>
                        <lastmod><?= date('Y-m-d', strtotime($city['updated_at'] ?? 'now')) ?></lastmod>
                        <changefreq>weekly</changefreq>
                        <priority>0.9</priority>
                    </url>
        <?php
                endif;
            endforeach;
        } catch (Exception $e) {
            error_log('Sitemap Cities Error: ' . $e->getMessage());
        }
        ?>

        <?php
        // İlçeler
        try {
            $districts = $db->fetchAll("
            SELECT d.slug as district_slug, c.slug as city_slug, d.updated_at
            FROM districts d
            JOIN cities c ON d.city_id = c.id
            ORDER BY d.name
        ");
            foreach ($districts as $district):
                if (!empty($district['city_slug']) && !empty($district['district_slug'])):
        ?>
                    <url>
                        <loc><?= $baseUrl ?>/<?= htmlspecialchars($district['city_slug']) ?>/<?= htmlspecialchars($district['district_slug']) ?></loc>
                        <lastmod><?= date('Y-m-d', strtotime($district['updated_at'] ?? 'now')) ?></lastmod>
                        <changefreq>weekly</changefreq>
                        <priority>0.8</priority>
                    </url>
        <?php
                endif;
            endforeach;
        } catch (Exception $e) {
            error_log('Sitemap Districts Error: ' . $e->getMessage());
        }
        ?>

        <?php
        // Firmalar
        try {
            $companies = $db->fetchAll("
            SELECT comp.slug as company_slug, c.slug as city_slug, d.slug as district_slug, comp.updated_at
            FROM companies comp
            JOIN cities c ON comp.city_id = c.id
            LEFT JOIN districts d ON comp.district_id = d.id
            WHERE comp.approved = 1 AND comp.deleted_at IS NULL
            ORDER BY comp.updated_at DESC
        ");
            foreach ($companies as $company):
                // Tüm gerekli alanların dolu olduğundan emin ol
                if (!empty($company['city_slug']) && !empty($company['district_slug']) && !empty($company['company_slug'])):
                    $companyUrl = $company['city_slug'] . '/' . $company['district_slug'] . '/firma/' . $company['company_slug'];
        ?>
                    <url>
                        <loc><?= $baseUrl ?>/<?= htmlspecialchars($companyUrl) ?></loc>
                        <lastmod><?= date('Y-m-d', strtotime($company['updated_at'])) ?></lastmod>
                        <changefreq>weekly</changefreq>
                        <priority>0.8</priority>
                    </url>
        <?php
                endif;
            endforeach;
        } catch (Exception $e) {
            error_log('Sitemap Companies Error: ' . $e->getMessage());
        }
        ?>

        <?php
        // Makaleler
        try {
            $articles = $db->fetchAll("
            SELECT a.slug, c.slug as city_slug, d.slug as district_slug, a.updated_at
            FROM articles a
            JOIN cities c ON a.city_id = c.id
            LEFT JOIN districts d ON a.district_id = d.id
            WHERE a.deleted_at IS NULL
            ORDER BY a.created_at DESC
        ");
            foreach ($articles as $article):
                if (!empty($article['city_slug']) && !empty($article['slug'])):
                    $articleUrl = $article['city_slug'];
                    if (!empty($article['district_slug'])) {
                        $articleUrl .= '/' . $article['district_slug'];
                    }
                    $articleUrl .= '/' . $article['slug'];
        ?>
                    <url>
                        <loc><?= $baseUrl ?>/<?= htmlspecialchars($articleUrl) ?></loc>
                        <lastmod><?= date('Y-m-d', strtotime($article['updated_at'])) ?></lastmod>
                        <changefreq>monthly</changefreq>
                        <priority>0.7</priority>
                    </url>
        <?php
                endif;
            endforeach;
        } catch (Exception $e) {
            error_log('Sitemap Articles Error: ' . $e->getMessage());
        }
        ?>
    </urlset>
<?php
} catch (Exception $e) {
    // Hata durumunda log'a yaz
    error_log('Sitemap Error: ' . $e->getMessage());
    // Boş ama geçerli bir sitemap döndür
    echo '</urlset>';
}
?>