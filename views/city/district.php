<?php
$pageHeader = true;
$breadcrumb = '<a href="' . $this->getConfig('base_path') . '/">Ana Sayfa</a>
               <span class="separator">/</span>
               <a href="' . $this->getConfig('base_path') . '/' . $city['slug'] . '">' . htmlspecialchars($city['name']) . '</a>
               <span class="separator">/</span>
               <span>' . htmlspecialchars($district['name']) . '</span>';
$pageTitle = htmlspecialchars($district['name']) . ' Lastik Tamircileri';
$pageDescription = htmlspecialchars($city['name']) . ' ili ' . htmlspecialchars($district['name']) . ' ilçesinde bulunan güvenilir lastik tamircileri';

$hasSidebar = true;
ob_start();
?>
<div class="content-card">
    <h2 class="content-title">Makaleler</h2>

    <?php if (!empty($articles)): ?>
        <div class="article-list">
            <?php foreach ($articles as $article): ?>
                <a href="<?= $this->getConfig('base_path') ?>/<?= $city['slug'] ?>/<?= $district['slug'] ?>/<?= $article['slug'] ?>" class="article-list-item">
                    <?php if ($article['featured_image']): ?>
                        <img src="<?= $this->getConfig('base_path') . $this->getConfig('upload_url') . $article['featured_image'] ?>" alt="<?= htmlspecialchars($article['title']) ?>" class="article-thumbnail" loading="lazy">
                    <?php endif; ?>

                    <div class="article-info">
                        <h3 class="article-list-title"><?= htmlspecialchars($article['title']) ?></h3>

                        <?php if ($article['excerpt']): ?>
                            <p class="article-list-excerpt"><?= htmlspecialchars($article['excerpt']) ?></p>
                        <?php endif; ?>

                        <div class="article-meta-small">
                            <span>👁 <?= $article['view_count'] ?> görüntülenme</span>
                        </div>
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