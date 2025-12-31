<?php
$pageHeader = true;
$breadcrumb = '<a href="' . $this->getConfig('base_path') . '/">Ana Sayfa</a>
               <span class="separator">/</span>
               <span>' . htmlspecialchars($city['name']) . '</span>';
// HUB SEO: Variables are already set by controller, just use them directly
// $pageTitle = meta title for <title> tag
// $h1 = H1 heading for page
// $pageDescription = description shown on page

// HUB Architecture: No sidebar for city pages (companies removed per SEO plan)
$hasSidebar = false;
ob_start();
?>

<?php if (!empty($districts)): ?>
    <section class="districts-section">
        <div class="container">
            <h2 class="section-title"><?= htmlspecialchars($city['name']) ?> İlçeleri</h2>

            <div class="districts-grid">
                <?php foreach ($districts as $district): ?>
                    <a href="<?= $this->getConfig('base_path') ?>/<?= $city['slug'] ?>/<?= $district['slug'] ?>" class="district-card">
                        <h3 class="district-name"><?= htmlspecialchars($district['name']) ?></h3>
                        <div class="district-stats">
                            <span class="stat-small">🏢 <?= $district['company_count'] ?> Firma</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- HUB Architecture: Single City HUB Article (replaces company list) -->
<?php if (!empty($hubArticle)): ?>
    <section class="hub-article-section">
        <div class="container">
            <article class="hub-article">
                <h2 class="hub-article-title"><?= htmlspecialchars($hubArticle['title']) ?></h2>

                <?php if (!empty($hubArticle['excerpt'])): ?>
                    <div class="hub-article-excerpt">
                        <p><strong><?= htmlspecialchars($hubArticle['excerpt']) ?></strong></p>
                    </div>
                <?php endif; ?>

                <div class="hub-article-content">
                    <?= $hubArticle['content'] ?>
                </div>

                <?php if (!empty($hubArticle['view_count'])): ?>
                    <div class="hub-article-meta">
                        <span class="meta-item">👁️ <?= number_format($hubArticle['view_count']) ?> görüntülenme</span>
                        <?php if (!empty($hubArticle['published_at'])): ?>
                            <span class="meta-item">📅 <?= date('d.m.Y', strtotime($hubArticle['published_at'])) ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </article>
        </div>
    </section>
<?php else: ?>
    <section class="no-content-notice">
        <div class="container">
            <p class="info-message">Bu şehir için henüz detaylı içerik eklenmemiş. İlçe sayfalarından bilgi edinebilirsiniz.</p>
        </div>
    </section>
<?php endif; ?>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>