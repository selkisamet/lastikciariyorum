<?php $pageId = 'district-edit'; ?>
<?php require_once __DIR__ . '/../layouts/admin-header.php'; ?>

<div class="admin-header">
    <h1>İlçe Düzenle: <?= htmlspecialchars($district['name']) ?></h1>
    <p class="subtitle">Şehir: <?= htmlspecialchars($city['name']) ?></p>
</div>

<div class="form-container">
    <form method="POST" action="<?= $this->getConfig('base_path') ?>/admin/ilce-duzenle/<?= $district['id'] ?>" class="standard-form">
        <div class="form-grid">
            <!-- İlçe Adı -->
            <div class="form-group">
                <label for="name">İlçe Adı *</label>
                <input type="text" id="name" name="name" class="form-input"
                    value="<?= htmlspecialchars($district['name']) ?>" required>
                <small>İlçe adı (Örn: Kadıköy, Çankaya)</small>
            </div>

            <!-- Slug -->
            <div class="form-group">
                <label for="slug">URL Slug *</label>
                <input type="text" id="slug" name="slug" class="form-input"
                    value="<?= htmlspecialchars($district['slug']) ?>" required>
                <small>URL için kullanılacak slug (Örn: kadikoy, cankaya)</small>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Güncelle</button>
            <a href="<?= $this->getConfig('base_path') ?>/admin/ilceler" class="btn btn-secondary">İptal</a>
        </div>
    </form>
</div>

<!-- HUB Article Section -->
<div class="form-container" style="margin-top: 2rem;">
    <h2>Bu İlçe İçin HUB Makalesi</h2>

    <?php if ($hubArticle): ?>
        <div class="info-box success">
            <h3>✅ Makale Mevcut</h3>
            <p><strong>Başlık:</strong> <?= htmlspecialchars($hubArticle['title']) ?></p>
            <p><strong>Yayın Durumu:</strong>
                <?= $hubArticle['is_published'] ? '<span class="badge badge-success">Yayında</span>' : '<span class="badge badge-warning">Taslak</span>' ?>
            </p>
            <?php if (!empty($hubArticle['view_count'])): ?>
                <p><strong>Görüntülenme:</strong> <?= number_format($hubArticle['view_count']) ?></p>
            <?php endif; ?>
        </div>
        <div class="form-actions">
            <a href="<?= $this->getConfig('base_path') ?>/admin/makale-duzenle/<?= $hubArticle['id'] ?>"
                class="btn btn-primary">
                📝 Makaleyi Düzenle
            </a>
            <a href="<?= $this->getConfig('base_path') ?>/<?= $city['slug'] ?>/<?= $district['slug'] ?>"
                class="btn btn-info" target="_blank">
                👁️ Sayfayı Görüntüle
            </a>
        </div>
    <?php else: ?>
        <div class="info-box warning">
            <h3>⚠️ Bu ilçe için henüz HUB makalesi yok</h3>
            <p>İlçe sayfasında görünecek detaylı içerik için bir makale oluşturmalısınız.</p>
        </div>
        <div class="form-actions">
            <a href="<?= $this->getConfig('base_path') ?>/admin/ai-makale-uret?district_id=<?= $district['id'] ?>"
                class="btn btn-success">
                ✨ AI ile Makale Oluştur
            </a>
        </div>
    <?php endif; ?>
</div>

<style>
.subtitle {
    color: #6c757d;
    font-size: 0.95rem;
    margin-top: 0.5rem;
}

.info-box {
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
}

.info-box.success {
    background: #d1e7dd;
    border-color: #badbcc;
}

.info-box.warning {
    background: #fff3cd;
    border-color: #ffecb5;
}

.info-box h3 {
    margin: 0 0 1rem 0;
    font-size: 1.1rem;
}

.info-box p {
    margin: 0.5rem 0;
}
</style>

<?php require_once __DIR__ . '/../layouts/admin-footer.php'; ?>
