<?php $pageId = 'companies'; ?>
<?php require_once __DIR__ . '/../layouts/admin-header.php'; ?>

<div class="admin-header">
            <h1>Firma Detayları</h1>
            <div class="header-actions">
                <a href="<?= $this->getConfig('base_path') ?>/admin/firma-duzenle/<?= $company['id'] ?>" class="btn btn-primary">
                    ✏ Düzenle
                </a>
                <a href="<?= $this->getConfig('base_path') ?>/admin/firmalar" class="btn btn-secondary">
                    ← Geri Dön
                </a>
            </div>
        </div>

        <div class="detail-container">
            <!-- Durum ve Onay Bölümü -->
            <div class="detail-card">
                <div class="card-header">
                    <h3>Durum Bilgisi</h3>
                </div>
                <div class="card-body">
                    <div class="status-row">
                        <span class="label">Onay Durumu:</span>
                        <?php if ($company['is_approved']): ?>
                            <span class="status-badge status-approved">✓ Onaylı</span>
                        <?php else: ?>
                            <span class="status-badge status-pending">⏱ Onay Bekliyor</span>
                            <form method="POST" action="<?= $this->getConfig('base_path') ?>/admin/firma-onayla/<?= $company['id'] ?>" style="display: inline; margin-left: 1rem;">
                                <button type="submit" class="btn btn-success btn-small" onclick="return confirm('Bu firmayı onaylamak istediğinizden emin misiniz?')">
                                    ✓ Firmayı Onayla
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Firma Bilgileri -->
            <div class="detail-card">
                <div class="card-header">
                    <h3>🏢 Firma Bilgileri</h3>
                </div>
                <div class="card-body">
                    <?php if ($company['logo']): ?>
                        <div class="company-logo">
                            <img src="<?= asset('uploads/logos/' . $company['logo']) ?>" alt="<?= htmlspecialchars($company['name']) ?>">
                        </div>
                    <?php endif; ?>

                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="label">Firma Adı:</span>
                            <span class="value"><strong><?= htmlspecialchars($company['name']) ?></strong></span>
                        </div>

                        <div class="detail-item">
                            <span class="label">İl:</span>
                            <span class="value"><?= htmlspecialchars($company['city_name']) ?></span>
                        </div>

                        <div class="detail-item">
                            <span class="label">İlçe:</span>
                            <span class="value"><?= $company['district_name'] ? htmlspecialchars($company['district_name']) : '-' ?></span>
                        </div>

                        <div class="detail-item">
                            <span class="label">Telefon:</span>
                            <span class="value"><?= $company['phone'] ? htmlspecialchars($company['phone']) : '-' ?></span>
                        </div>

                        <div class="detail-item">
                            <span class="label">E-posta:</span>
                            <span class="value"><?= $company['email'] ? htmlspecialchars($company['email']) : '-' ?></span>
                        </div>

                        <div class="detail-item">
                            <span class="label">Website:</span>
                            <span class="value">
                                <?php if ($company['website']): ?>
                                    <a href="<?= htmlspecialchars($company['website']) ?>" target="_blank" rel="noopener">
                                        <?= htmlspecialchars($company['website']) ?> ↗
                                    </a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </span>
                        </div>

                        <div class="detail-item full-width">
                            <span class="label">Adres:</span>
                            <span class="value"><?= $company['address'] ? nl2br(htmlspecialchars($company['address'])) : '-' ?></span>
                        </div>

                        <div class="detail-item full-width">
                            <span class="label">Açıklama:</span>
                            <span class="value"><?= $company['description'] ? nl2br(htmlspecialchars($company['description'])) : '-' ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kullanıcı Bilgileri -->
            <?php if ($user): ?>
            <div class="detail-card">
                <div class="card-header">
                    <h3>👤 Kullanıcı Bilgileri</h3>
                </div>
                <div class="card-body">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="label">Ad Soyad:</span>
                            <span class="value"><?= htmlspecialchars($user['full_name']) ?></span>
                        </div>

                        <div class="detail-item">
                            <span class="label">E-posta:</span>
                            <span class="value"><?= htmlspecialchars($user['email']) ?></span>
                        </div>

                        <div class="detail-item">
                            <span class="label">Kullanıcı Durumu:</span>
                            <span class="value">
                                <?php if ($user['is_active']): ?>
                                    <span class="status-badge status-approved">Aktif</span>
                                <?php else: ?>
                                    <span class="status-badge status-pending">Pasif</span>
                                <?php endif; ?>
                            </span>
                        </div>

                        <div class="detail-item">
                            <span class="label">Kayıt Tarihi:</span>
                            <span class="value"><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="detail-card">
                <div class="card-header">
                    <h3>👤 Kullanıcı Bilgileri</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">Bu firmaya ait kullanıcı hesabı bulunmamaktadır.</p>
                </div>
            </div>
            <?php endif; ?>

            <!-- İstatistikler -->
            <div class="detail-card">
                <div class="card-header">
                    <h3>📊 İstatistikler</h3>
                </div>
                <div class="card-body">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="label">Görüntülenme:</span>
                            <span class="value"><strong><?= number_format($company['view_count'] ?? 0) ?></strong></span>
                        </div>

                        <div class="detail-item">
                            <span class="label">Eklenme Tarihi:</span>
                            <span class="value"><?= date('d.m.Y H:i', strtotime($company['created_at'])) ?></span>
                        </div>

                        <?php if ($company['updated_at']): ?>
                        <div class="detail-item">
                            <span class="label">Son Güncelleme:</span>
                            <span class="value"><?= date('d.m.Y H:i', strtotime($company['updated_at'])) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.admin-header h1 {
    margin: 0;
}

.header-actions {
    display: flex;
    gap: 0.5rem;
}

.detail-container {
    display: grid;
    gap: 1.5rem;
}

.detail-card {
    background: white;
    border-radius: 0.5rem;
    border: 1px solid var(--border-color);
    overflow: hidden;
}

.card-header {
    background: var(--bg-gray);
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border-color);
}

.card-header h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-primary);
}

.card-body {
    padding: 1.5rem;
}

.company-logo {
    margin-bottom: 1.5rem;
    text-align: center;
}

.company-logo img {
    max-width: 200px;
    max-height: 200px;
    border-radius: 0.5rem;
    border: 1px solid var(--border-color);
    object-fit: contain;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.detail-item.full-width {
    grid-column: 1 / -1;
}

.detail-item .label {
    font-weight: 600;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.detail-item .value {
    color: var(--text-primary);
    font-size: 1rem;
}

.detail-item .value a {
    color: var(--primary-color);
    text-decoration: none;
}

.detail-item .value a:hover {
    text-decoration: underline;
}

.status-row {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.status-badge {
    display: inline-block;
    padding: 0.4rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.85rem;
    font-weight: 600;
}

.status-approved {
    background: #d4edda;
    color: #155724;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.text-muted {
    color: var(--text-secondary);
    font-style: italic;
}

@media (max-width: 768px) {
    .admin-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .header-actions {
        width: 100%;
        flex-direction: column;
    }

    .header-actions .btn {
        width: 100%;
    }

    .detail-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/admin-footer.php'; ?>
