<?php $pageId = 'companies'; ?>
<?php require_once __DIR__ . '/../layouts/admin-header.php'; ?>

<div class="admin-header">
    <h1>Excel İçe Aktarma Sonucu</h1>
    <a href="<?= $this->getConfig('base_path') ?>/admin/firmalar" class="btn btn-secondary">Firma Listesine Git</a>
</div>

<div class="result-container">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <div>
                <h3>Başarılı!</h3>
                <p><?= htmlspecialchars($_SESSION['success']) ?></p>
            </div>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <div>
                <h3>Hata!</h3>
                <p><?= htmlspecialchars($_SESSION['error']) ?></p>
            </div>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="errors-card">
            <h2>Hatalı Satırlar (<?= count($errors) ?>)</h2>
            <p class="text-muted">Aşağıdaki satırlarda hatalar tespit edildi ve bu firmalar eklenemedi:</p>

            <div class="errors-list">
                <?php foreach ($errors as $error): ?>
                    <div class="error-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="help-box">
                <h3>Hatalar Nasıl Düzeltilir?</h3>
                <ul>
                    <li>Şehir ve ilçe adlarının veritabanında bulunduğundan emin olun</li>
                    <li>İl-ilçe eşleşmesinin doğru olduğunu kontrol edin</li>
                    <li>Zorunlu alanların (name, city_id, district_id) dolu olduğunu kontrol edin</li>
                    <li>Excel dosyanızı düzelttikten sonra tekrar yükleyebilirsiniz</li>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <div class="actions">
        <a href="<?= $this->getConfig('base_path') ?>/admin/firma-import" class="btn btn-primary">
            Yeni Excel Dosyası Yükle
        </a>
        <a href="<?= $this->getConfig('base_path') ?>/admin/firmalar" class="btn btn-secondary">
            Firma Listesine Git
        </a>
    </div>
</div>

<style>
.result-container {
    max-width: 900px;
    margin: 0 auto;
}

.alert {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1.5rem;
    border-radius: 0.5rem;
    margin-bottom: 2rem;
    border-left: 4px solid;
}

.alert svg {
    flex-shrink: 0;
    margin-top: 0.25rem;
}

.alert h3 {
    margin: 0 0 0.5rem 0;
    font-size: 1.1rem;
}

.alert p {
    margin: 0;
    line-height: 1.6;
}

.alert-success {
    background: #d4edda;
    border-left-color: #28a745;
    color: #155724;
}

.alert-success svg {
    stroke: #28a745;
}

.alert-error {
    background: #f8d7da;
    border-left-color: #dc3545;
    color: #721c24;
}

.alert-error svg {
    stroke: #dc3545;
}

.errors-card {
    background: white;
    border-radius: 0.5rem;
    padding: 2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.errors-card h2 {
    margin-top: 0;
    margin-bottom: 0.5rem;
    color: var(--text-primary);
}

.text-muted {
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin-bottom: 1.5rem;
}

.errors-list {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1.5rem;
    max-height: 400px;
    overflow-y: auto;
}

.error-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.75rem;
    background: white;
    border-radius: 0.25rem;
    margin-bottom: 0.5rem;
    border-left: 3px solid #dc3545;
}

.error-item:last-child {
    margin-bottom: 0;
}

.error-item svg {
    flex-shrink: 0;
    margin-top: 0.15rem;
    color: #dc3545;
}

.error-item span {
    flex: 1;
    font-size: 0.9rem;
    line-height: 1.5;
}

.help-box {
    background: #e7f3ff;
    border-left: 4px solid #0066cc;
    border-radius: 0.5rem;
    padding: 1.5rem;
}

.help-box h3 {
    margin-top: 0;
    margin-bottom: 1rem;
    color: #0066cc;
    font-size: 1rem;
}

.help-box ul {
    margin: 0;
    padding-left: 1.5rem;
}

.help-box li {
    margin-bottom: 0.5rem;
    line-height: 1.6;
}

.actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
}

.actions .btn {
    padding: 0.75rem 1.5rem;
}

@media (max-width: 768px) {
    .result-container {
        padding: 0 1rem;
    }

    .errors-card {
        padding: 1rem;
    }

    .alert {
        padding: 1rem;
    }

    .actions {
        flex-direction: column;
    }

    .actions .btn {
        width: 100%;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/admin-footer.php'; ?>