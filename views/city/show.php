<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-header">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= $this->getConfig('base_path') ?>/">Ana Sayfa</a>
            <span class="separator">/</span>
            <span><?= htmlspecialchars($city['name']) ?></span>
        </nav>
        <h1 class="page-title"><?= htmlspecialchars($city['name']) ?> Lastik Tamircileri</h1>
        <p class="page-description"><?= htmlspecialchars($city['name']) ?> ilinde bulunan güvenilir lastik tamircileri ve lastik tamir servisleri</p>
    </div>
</div>

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

<section class="content-section">
    <div class="container">
        <div class="two-column-layout">
            <?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

            <main class="main-content">
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
            </main>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>