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

<!-- HUB SEO: Main Content Section (600-900 words) -->
<?php if (!empty($district['content'])): ?>
    <section class="hub-content-section">
        <div class="container">
            <div class="hub-main-content">
                <?= $district['content'] ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- HUB SEO: H2 Sections with Contextual Links -->
<?php
$h2Sections = $district['h2_sections'] ?? [];
if (!empty($h2Sections) && is_array($h2Sections)):
?>
    <section class="h2-sections">
        <div class="container">
            <?php foreach ($h2Sections as $section): ?>
                <?php if (!empty($section['title'])): ?>
                    <div class="h2-section-block">
                        <h2 class="h2-section-title"><?= htmlspecialchars($section['title']) ?></h2>

                        <?php if (!empty($section['description'])): ?>
                            <p class="h2-section-description"><?= htmlspecialchars($section['description']) ?></p>
                        <?php endif; ?>

                        <?php if (!empty($section['linked_article_id'])): ?>
                            <?php
                            // Find the linked article from the articles array
                            $linkedArticle = null;
                            foreach ($articles as $article) {
                                if ($article['id'] == $section['linked_article_id']) {
                                    $linkedArticle = $article;
                                    break;
                                }
                            }
                            ?>
                            <?php if ($linkedArticle): ?>
                                <a href="<?= $this->getConfig('base_path') ?>/<?= $city['slug'] ?>/<?= $district['slug'] ?>/<?= $linkedArticle['slug'] ?>"
                                    class="h2-section-link">
                                    Detaylı Bilgi: <?= htmlspecialchars($linkedArticle['title']) ?> →
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<!-- HUB Architecture: Single District HUB Article (long comprehensive content) -->
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
            <p class="info-message">Bu ilçe için henüz detaylı içerik eklenmemiş.</p>
        </div>
    </section>
<?php endif; ?>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>