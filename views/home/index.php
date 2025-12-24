<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="hero">
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">Türkiye'nin Her Yerinde Lastik Tamircisi</h1>
            <p class="hero-description">En yakın ve güvenilir lastik tamircilerini bulun. Tüm iller ve ilçeler için kapsamlı rehber.</p>

            <div class="search-box">
                <input type="text" id="citySearch" placeholder="İl veya ilçe ara..." class="search-input" autocomplete="off">
            </div>
        </div>
    </div>
</div>

<section class="cities-section">
    <div class="container">
        <h2 class="section-title" id="citiesSectionTitle">Tüm İller</h2>

        <div class="cities-grid" id="citiesGrid">
            <?php foreach ($cities as $city): ?>
                <a href="<?= url($city['slug']) ?>" class="city-card" data-type="city" data-name="<?= htmlspecialchars($city['name']) ?>">
                    <h3 class="city-name"><?= htmlspecialchars($city['name']) ?></h3>
                    <div class="city-stats">
                        <span class="stat">
                            <span class="stat-icon">🏢</span>
                            <?= $city['company_count'] ?> Firma
                        </span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if (!empty($latestArticles)): ?>
    <section class="articles-section">
        <div class="container">
            <h2 class="section-title">Son Makaleler</h2>

            <div class="articles-grid">
                <?php foreach ($latestArticles as $article): ?>
                    <?php $articleUrl = $article['city_slug'] . ($article['district_slug'] ? '/' . $article['district_slug'] : '') . '/' . $article['slug']; ?>
                    <a href="<?= url($articleUrl) ?>" class="article-card">
                        <?php if ($article['featured_image']): ?>
                            <img src="<?= asset('uploads/' . $article['featured_image']) ?>" alt="<?= htmlspecialchars($article['title']) ?>" class="article-image" loading="lazy">
                        <?php endif; ?>

                        <div class="article-content">
                            <div class="article-meta">
                                <span class="article-location"><?= htmlspecialchars($article['city_name']) ?><?= $article['district_name'] ? ' / ' . htmlspecialchars($article['district_name']) : '' ?></span>
                            </div>

                            <h3 class="article-title"><?= htmlspecialchars($article['title']) ?></h3>

                            <?php
                            $excerpt = getArticleExcerpt($article['excerpt'], $article['content']);
                            if ($excerpt):
                            ?>
                                <p class="article-excerpt"><?= htmlspecialchars($excerpt) ?></p>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2 class="cta-title">Lastik Tamirhanenizi Ekleyin</h2>
            <p class="cta-description">İşletmenizi binlerce potansiyel müşteriye ulaştırın</p>
            <a href="<?= url('firma-ekle') ?>" class="btn btn-large text-light">Hemen Ekle</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>