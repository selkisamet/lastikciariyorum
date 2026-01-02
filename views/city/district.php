<?php
$pageHeader = true;
$breadcrumb = '<a href="' . $this->getConfig('base_path') . '/">Ana Sayfa</a>
               <span class="separator">/</span>
               <a href="' . $this->getConfig('base_path') . '/' . $city['slug'] . '">' . htmlspecialchars($city['name']) . '</a>
               <span class="separator">/</span>
               <span>' . htmlspecialchars($district['name']) . '</span>';
// HUB SEO: Variables are already set by controller, just use them directly
// $pageTitle = meta title for <title> tag
// $h1 = H1 heading for page
// $pageDescription = description shown on page

$hasSidebar = true;
ob_start();
?>

<!-- HUB Architecture: Single District HUB Article (long comprehensive content) -->
<?php if (!empty($hubArticle)): ?>
    <section class="article-section">
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
            </article>
        </div>
    </section>
<?php else: ?>
    <section class="no-content-notice">
        <div class="container">
            <p class="info-message">Bu ilçe için henüz detaylı içerik eklenmemiş.</p>
        </div>
    </section>
<?php endif; ?>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>