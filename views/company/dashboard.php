<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-header">
    <div class="container">
        <h1 class="page-title">Firma Paneli</h1>
        <p class="page-description">Firma bilgilerinizi görüntüleyin ve düzenleyin</p>
    </div>
</div>

<section class="content-section">
    <div class="container">
        <?php if (!empty($companies)): ?>
            <?php foreach ($companies as $company): ?>
                <div class="company-panel-card">
                    <div class="company-panel-header">
                        <div>
                            <h2><?= htmlspecialchars($company['name']) ?></h2>
                            <p class="company-panel-location">
                                📍 <?= htmlspecialchars($company['city_name']) ?><?= $company['district_name'] ? ' / ' . htmlspecialchars($company['district_name']) : '' ?>
                            </p>
                        </div>
                        <div class="company-panel-status">
                            <?php if ($company['is_approved']): ?>
                                <span class="status-badge status-approved">✓ Onaylı</span>
                            <?php else: ?>
                                <span class="status-badge status-pending">⏳ Onay Bekliyor</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="company-panel-grid">
                        <div class="info-box">
                            <div class="info-box-icon">📞</div>
                            <div class="info-box-content">
                                <h4>Telefon</h4>
                                <p><?= htmlspecialchars($company['phone'] ?? 'Belirtilmemiş') ?></p>
                            </div>
                        </div>

                        <div class="info-box">
                            <div class="info-box-icon">📧</div>
                            <div class="info-box-content">
                                <h4>E-posta</h4>
                                <p><?= htmlspecialchars($company['email'] ?? 'Belirtilmemiş') ?></p>
                            </div>
                        </div>

                        <div class="info-box">
                            <div class="info-box-icon">🌐</div>
                            <div class="info-box-content">
                                <h4>Website</h4>
                                <p><?= $company['website'] ? '<a href="' . htmlspecialchars($company['website']) . '" target="_blank">' . htmlspecialchars($company['website']) . '</a>' : 'Belirtilmemiş' ?></p>
                            </div>
                        </div>

                        <div class="info-box">
                            <div class="info-box-icon">👁️</div>
                            <div class="info-box-content">
                                <h4>Görüntülenme</h4>
                                <p><?= $company['view_count'] ?> kez</p>
                            </div>
                        </div>
                    </div>

                    <?php if ($company['address']): ?>
                        <div class="company-panel-section">
                            <h3>📍 Adres</h3>
                            <p><?= nl2br(htmlspecialchars($company['address'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($company['description']): ?>
                        <div class="company-panel-section">
                            <h3>ℹ️ Açıklama</h3>
                            <p><?= nl2br(htmlspecialchars($company['description'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($company['logo']): ?>
                        <div class="company-panel-section">
                            <h3>🖼️ Firma Logosu</h3>
                            <img src="<?= asset('uploads/logos/' . $company['logo']) ?>" alt="<?= htmlspecialchars($company['name']) ?>" class="company-logo-preview">
                        </div>
                    <?php endif; ?>

                    <div class="company-panel-actions">
                        <a href="<?= url('/firma-duzenle/' . $company['id']) ?>" class="btn btn-primary">✏️ Hesap ve Firma Bilgilerini Düzenle</a>

                        <?php if ($company['is_approved']): ?>
                            <?php
                                $companyUrl = $company['city_slug'] . '/' . $company['district_slug'] . '/firma/' . $company['slug'];
                            ?>
                            <a href="<?= url($companyUrl) ?>" class="btn btn-secondary" target="_blank">🔗 Sayfayı Görüntüle</a>
                        <?php endif; ?>
                    </div>

                    <div class="danger-zone">
                        <h3>⚠️ Tehlikeli Alan</h3>
                        <p>Hesabınızı ve tüm verilerinizi kalıcı olarak silebilirsiniz. Bu işlem geri alınamaz!</p>
                        <div class="danger-actions">
                            <button onclick="showDeleteAccountModal()" class="btn btn-danger">🗑️ Hesabımı Kalıcı Olarak Sil</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <p>❌ Henüz firma kaydınız bulunmuyor.</p>
                <p>Giriş yaptığınız hesaba ait firma bulunamadı.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
.company-panel-card {
    background: #fff;
    border: 1px solid var(--border-color);
    border-radius: 0.75rem;
    padding: 2rem;
    margin-bottom: 2rem;
}

.company-panel-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding-bottom: 1.5rem;
    margin-bottom: 1.5rem;
    border-bottom: 2px solid var(--border-color);
}

.company-panel-header h2 {
    font-size: 1.75rem;
    margin-bottom: 0.5rem;
}

.company-panel-location {
    color: var(--text-secondary);
    font-size: 1rem;
}

.company-panel-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.info-box {
    display: flex;
    gap: 1rem;
    padding: 1.25rem;
    background: var(--bg-gray);
    border-radius: 0.5rem;
}

.info-box-icon {
    font-size: 2rem;
    flex-shrink: 0;
}

.info-box-content h4 {
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin-bottom: 0.25rem;
    font-weight: 600;
}

.info-box-content p {
    font-size: 1rem;
    color: var(--text-primary);
    margin: 0;
}

.info-box-content a {
    color: var(--primary-color);
    text-decoration: none;
}

.info-box-content a:hover {
    text-decoration: underline;
}

.company-panel-section {
    margin-bottom: 1.5rem;
    padding: 1.25rem;
    background: var(--bg-gray);
    border-radius: 0.5rem;
}

.company-panel-section h3 {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    color: var(--text-primary);
}

.company-panel-section p {
    color: var(--text-secondary);
    line-height: 1.6;
    margin: 0;
}

.company-panel-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border-color);
}

.company-logo-preview {
    max-width: 200px;
    max-height: 200px;
    border-radius: 0.5rem;
    border: 1px solid var(--border-color);
    object-fit: contain;
}

@media (max-width: 768px) {
    .company-panel-header {
        flex-direction: column;
        gap: 1rem;
    }

    .company-panel-grid {
        grid-template-columns: 1fr;
    }

    .company-panel-actions {
        flex-direction: column;
    }

    .company-panel-actions .btn {
        width: 100%;
    }
}

/* Danger Zone Styles */
.danger-zone {
    margin-top: 3rem;
    padding: 1.5rem;
    border: 2px solid #dc3545;
    border-radius: 0.5rem;
    background: #fff5f5;
}

.danger-zone h3 {
    color: #dc3545;
    font-size: 1.1rem;
    margin-bottom: 0.75rem;
}

.danger-zone p {
    color: var(--text-secondary);
    margin-bottom: 1rem;
    line-height: 1.6;
}

.btn-danger {
    background: #dc3545;
    color: white;
    border: none;
}

.btn-danger:hover {
    background: #c82333;
}

/* Delete Account Modal */
.delete-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}

.delete-modal.active {
    display: flex;
}

.delete-modal-content {
    background: white;
    border-radius: 0.75rem;
    max-width: 500px;
    width: 90%;
    padding: 2rem;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
}

.delete-modal-header {
    margin-bottom: 1.5rem;
}

.delete-modal-header h2 {
    color: #dc3545;
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.delete-modal-body {
    margin-bottom: 1.5rem;
}

.delete-modal-body p {
    margin-bottom: 1rem;
    line-height: 1.6;
    color: var(--text-secondary);
}

.delete-warning {
    background: #fff3cd;
    border: 1px solid #ffc107;
    padding: 1rem;
    border-radius: 0.5rem;
    margin: 1rem 0;
}

.delete-warning strong {
    color: #856404;
}

.delete-modal-footer {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

.delete-modal-footer .btn {
    flex: 1;
}
</style>

<!-- Delete Account Modal -->
<div id="deleteAccountModal" class="delete-modal">
    <div class="delete-modal-content">
        <div class="delete-modal-header">
            <h2>⚠️ Hesabı Kalıcı Olarak Sil</h2>
        </div>
        <div class="delete-modal-body">
            <p>Hesabınızı silmek üzeresiniz. Bu işlem geri alınamaz ve aşağıdaki verileriniz kalıcı olarak silinecektir:</p>
            <ul>
                <li>Kullanıcı hesabınız</li>
                <li>Firma bilgileriniz</li>
                <li>Firma logonuz</li>
                <li>KVKK onaylarınız</li>
                <li>Tüm istatistikler</li>
            </ul>

            <div class="delete-warning">
                <strong>⚠️ Uyarı:</strong> Bu işlem geri alınamaz! Devam etmek istediğinizden emin misiniz?
            </div>

            <form method="POST" action="<?= url('/firma-paneli/hesap-sil') ?>" id="deleteAccountForm">
                <div class="form-group">
                    <label for="delete_reason">Silme Nedeniniz (Opsiyonel)</label>
                    <textarea id="delete_reason" name="reason" class="form-input" rows="3" placeholder="Hesabınızı neden silmek istiyorsunuz?"></textarea>
                </div>

                <div class="form-group">
                    <label class="consent-label">
                        <input type="checkbox" name="confirm_delete" id="confirm_delete" required>
                        <span class="consent-text">
                            Hesabımı ve tüm verilerimi kalıcı olarak silmek istediğimi onaylıyorum.
                        </span>
                    </label>
                </div>
            </form>
        </div>
        <div class="delete-modal-footer">
            <button type="button" onclick="hideDeleteAccountModal()" class="btn btn-secondary">İptal</button>
            <button type="button" onclick="submitDeleteAccount()" class="btn btn-danger">Hesabı Sil</button>
        </div>
    </div>
</div>

<script>
function showDeleteAccountModal() {
    document.getElementById('deleteAccountModal').classList.add('active');
}

function hideDeleteAccountModal() {
    document.getElementById('deleteAccountModal').classList.remove('active');
}

function submitDeleteAccount() {
    const checkbox = document.getElementById('confirm_delete');
    if (!checkbox.checked) {
        alert('Lütfen onay kutusunu işaretleyin.');
        return;
    }

    if (confirm('Bu işlem geri alınamaz! Hesabınızı kalıcı olarak silmek istediğinizden emin misiniz?')) {
        document.getElementById('deleteAccountForm').submit();
    }
}

// Modal dışına tıklanınca kapat
document.getElementById('deleteAccountModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideDeleteAccountModal();
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
