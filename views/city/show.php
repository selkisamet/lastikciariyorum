<?php
$pageHeader = true;
$breadcrumb = '<a href="' . $this->getConfig('base_path') . '/">Ana Sayfa</a>
               <span class="separator">/</span>
               <span>' . htmlspecialchars($city['name']) . '</span>';
// HUB SEO: Variables are already set by controller, just use them directly
// $pageTitle = meta title for <title> tag
// $h1 = H1 heading for page
// $pageDescription = description shown on page

$hasSidebar = true;
ob_start();
?>

<!-- HUB SEO: Main Content Section (600-900 words) -->
<?php if (!empty($city['content'])): ?>
    <section class="hub-content-section">
        <div class="container">
            <div class="hub-main-content">
                <?= $city['content'] ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- HUB SEO: H2 Sections with Contextual Links -->
<?php
$h2Sections = $city['h2_sections'] ?? [];
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
                                <a href="<?= $this->getConfig('base_path') ?>/<?= $city['slug'] ?>/<?= $linkedArticle['slug'] ?>"
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

<div class="content-card">
    <h2 class="content-title">Makaleler</h2>

    <?php if (!empty($articles)): ?>
        <div class="article-list">
            <?php foreach ($articles as $article): ?>
                <a href="<?= $this->getConfig('base_path') ?>/<?= $city['slug'] ?>/<?= $article['slug'] ?>" class="article-list-item">
                    <?php if ($article['featured_image']): ?>
                        <img src="<?= $this->getConfig('base_path') . $this->getConfig('upload_url') . $article['featured_image'] ?>" alt="<?= htmlspecialchars($article['title']) ?>" class="article-thumbnail" loading="lazy">
                    <?php endif; ?>

                    <div class="article-info">
                        <h3 class="article-list-title"><?= htmlspecialchars($article['title']) ?></h3>

                        <?php
                        $excerpt = getArticleExcerpt($article['excerpt'], $article['content']);
                        if ($excerpt):
                        ?>
                            <p class="article-list-excerpt"><?= htmlspecialchars($excerpt) ?></p>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="no-data">Henüz makale bulunmuyor</p>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>