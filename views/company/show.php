<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-header">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= $this->getConfig('base_path') ?>/">Ana Sayfa</a>
            <span class="separator">/</span>
            <a href="<?= $this->getConfig('base_path') ?>/<?= $city['slug'] ?>"><?= htmlspecialchars($city['name']) ?></a>
            <?php if ($district): ?>
                <span class="separator">/</span>
                <a href="<?= $this->getConfig('base_path') ?>/<?= $city['slug'] ?>/<?= $district['slug'] ?>"><?= htmlspecialchars($district['name']) ?></a>
            <?php endif; ?>
            <span class="separator">/</span>
            <span><?= htmlspecialchars($company['name']) ?></span>
        </nav>
    </div>
</div>

<section class="content-section">
    <div class="container">
        <div class="company-detail">
            <div class="company-header">
                <h1 class="company-detail-title"><?= htmlspecialchars($company['name']) ?></h1>
                <div class="company-meta">
                    <span class="meta-tag">📍 <?= htmlspecialchars($city['name']) ?><?= $district ? ' / ' . htmlspecialchars($district['name']) : '' ?></span>
                    <span class="meta-tag">👁 <?= $company['view_count'] ?> görüntülenme</span>
                </div>
            </div>

            <div class="company-info-grid">
                <?php if ($company['phone']): ?>
                    <div class="info-card">
                        <div class="info-icon">📞</div>
                        <div class="info-content">
                            <h3>Telefon</h3>
                            <a href="tel:<?= htmlspecialchars($company['phone']) ?>" class="info-link"><?= htmlspecialchars($company['phone']) ?></a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($company['address']): ?>
                    <div class="info-card">
                        <div class="info-icon">📍</div>
                        <div class="info-content">
                            <h3>Adres</h3>
                            <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($company['address']) ?>" target="_blank" rel="noopener" class="info-link"><?= htmlspecialchars($company['address']) ?></a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($company['email']): ?>
                    <div class="info-card">
                        <div class="info-icon">✉️</div>
                        <div class="info-content">
                            <h3>E-posta</h3>
                            <a href="mailto:<?= htmlspecialchars($company['email']) ?>" class="info-link"><?= htmlspecialchars($company['email']) ?></a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($company['website']): ?>
                    <div class="info-card">
                        <div class="info-icon">🌐</div>
                        <div class="info-content">
                            <h3>Website</h3>
                            <a href="<?= htmlspecialchars($company['website']) ?>" target="_blank" rel="noopener" class="info-link">Web sitesini ziyaret et</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($company['description']): ?>
                <div class="company-description">
                    <h2>Hakkında</h2>
                    <p><?= nl2br(htmlspecialchars($company['description'])) ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
