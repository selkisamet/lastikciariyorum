<?php $pageId = 'ai-articles'; ?>
<?php require_once __DIR__ . '/../layouts/admin-header.php'; ?>

<div class="admin-header">
    <h1>Toplu Üretim Sonucu</h1>
    <a href="<?= $this->getConfig('base_path') ?>/admin/makaleler" class="btn btn-primary">Makaleleri Görüntüle</a>
</div>

<div class="result-container">
    <?php if (empty($errors)): ?>
        <div class="alert alert-success">
            <h2>✓ Tüm makaleler başarıyla oluşturuldu!</h2>
            <p>Makaleleri incelemek için yukarıdaki "Makaleleri Görüntüle" butonuna tıklayın.</p>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            <h2>⚠️ Bazı Makaleler Oluşturulamadı</h2>
            <p>Aşağıdaki konumlar için makale üretimi başarısız oldu:</p>
        </div>

        <div class="error-list-container">
            <h3>Hata Listesi</h3>
            <ul class="error-list">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="action-buttons">
        <a href="<?= $this->getConfig('base_path') ?>/admin/ai-makale-uret" class="btn btn-secondary">
            ← Yeni Üretim Yap
        </a>
        <a href="<?= $this->getConfig('base_path') ?>/admin/makaleler" class="btn btn-primary">
            Makalelere Git →
        </a>
    </div>
</div>

<style>
.result-container {
    max-width: 800px;
    margin: 0 auto;
}

.alert h2 {
    margin: 0 0 12px 0;
    font-size: 24px;
}

.alert p {
    margin: 0;
    font-size: 16px;
}

.error-list-container {
    background: white;
    padding: 24px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-top: 20px;
}

.error-list-container h3 {
    margin: 0 0 16px 0;
    font-size: 18px;
}

.error-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.error-list li {
    padding: 12px;
    margin-bottom: 8px;
    background: #fff3cd;
    border-left: 3px solid #ffc107;
    border-radius: 4px;
}

.action-buttons {
    display: flex;
    gap: 12px;
    margin-top: 24px;
    justify-content: center;
}

@media (max-width: 768px) {
    .action-buttons {
        flex-direction: column;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/admin-footer.php'; ?>
