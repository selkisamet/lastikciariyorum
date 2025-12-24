<?php
$pageHeader = true;
$breadcrumb = '<a href="' . $this->getConfig('base_path') . '/">Ana Sayfa</a>
               <span class="separator">/</span>
               <a href="' . $this->getConfig('base_path') . '/' . $city['slug'] . '">' . htmlspecialchars($city['name']) . '</a>';
if ($district) {
    $breadcrumb .= '<span class="separator">/</span>
                    <a href="' . $this->getConfig('base_path') . '/' . $city['slug'] . '/' . $district['slug'] . '">' . htmlspecialchars($district['name']) . '</a>';
}
$breadcrumb .= '<span class="separator">/</span>
                <span>' . htmlspecialchars($article['title']) . '</span>';

$hasSidebar = true;
ob_start();
?>
<article class="article-detail">
    <?php if ($article['featured_image']): ?>
        <img src="<?= $this->getConfig('base_path') . $this->getConfig('upload_url') . $article['featured_image'] ?>" alt="<?= htmlspecialchars($article['title']) ?>" class="article-featured-image">
    <?php endif; ?>

    <h1 class="article-detail-title"><?= htmlspecialchars($article['title']) ?></h1>

    <div class="article-meta-bar">
        <span class="meta-item">📍 <?= htmlspecialchars($city['name']) ?><?= $district ? ' / ' . htmlspecialchars($district['name']) : '' ?></span>
    </div>

    <div class="article-body">
        <?= $article['content'] ?>
    </div>
</article>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>