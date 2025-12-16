<?php $pageId = 'deletion-requests'; ?>
<?php require_once __DIR__ . '/../layouts/admin-header.php'; ?>

<div class="admin-header">
    <h1>Silme Talebi Detayı #<?= $request['id'] ?></h1>
    <a href="<?= url('/admin/silme-talepleri') ?>" class="btn btn-secondary">← Geri Dön</a>
</div>

<div class="detail-container">
    <div class="detail-card">
        <h2>Talep Bilgileri</h2>
        <div class="detail-grid">
            <div class="detail-item">
                <label>Talep ID:</label>
                <span>#<?= $request['id'] ?></span>
            </div>
            <div class="detail-item">
                <label>Talep Türü:</label>
                <span>
                    <?php if ($request['request_type'] === 'account_deletion'): ?>
                        <span class="badge badge-danger">Hesap Silme</span>
                    <?php else: ?>
                        <span class="badge badge-warning">Veri Silme</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="detail-item">
                <label>Durum:</label>
                <span>
                    <?php
                    $statusClass = 'badge-secondary';
                    $statusText = $request['status'];

                    switch ($request['status']) {
                        case 'pending':
                            $statusClass = 'badge-warning';
                            $statusText = 'Bekliyor';
                            break;
                        case 'approved':
                            $statusClass = 'badge-info';
                            $statusText = 'Onaylandı';
                            break;
                        case 'completed':
                            $statusClass = 'badge-success';
                            $statusText = 'Tamamlandı';
                            break;
                        case 'rejected':
                            $statusClass = 'badge-danger';
                            $statusText = 'Reddedildi';
                            break;
                    }
                    ?>
                    <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                </span>
            </div>
            <div class="detail-item">
                <label>Talep Tarihi:</label>
                <span><?= date('d.m.Y H:i:s', strtotime($request['requested_at'])) ?></span>
            </div>
            <div class="detail-item">
                <label>Talep IP Adresi:</label>
                <span><?= htmlspecialchars($request['requested_ip'] ?? '-') ?></span>
            </div>
        </div>
    </div>

    <div class="detail-card">
        <h2>Kullanıcı Bilgileri</h2>
        <div class="detail-grid">
            <div class="detail-item">
                <label>Ad Soyad:</label>
                <span><?= htmlspecialchars($request['user_name']) ?></span>
            </div>
            <div class="detail-item">
                <label>E-posta:</label>
                <span><?= htmlspecialchars($request['user_email']) ?></span>
            </div>
            <div class="detail-item">
                <label>Firma:</label>
                <span><?= $request['company_name'] ? htmlspecialchars($request['company_name']) : '-' ?></span>
            </div>
        </div>
    </div>

    <?php if ($request['reason']): ?>
        <div class="detail-card">
            <h2>Silme Nedeni</h2>
            <div class="reason-box">
                <?= nl2br(htmlspecialchars($request['reason'])) ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($request['processed_at']): ?>
        <div class="detail-card">
            <h2>İşlem Bilgileri</h2>
            <div class="detail-grid">
                <div class="detail-item">
                    <label>İşlem Tarihi:</label>
                    <span><?= date('d.m.Y H:i:s', strtotime($request['processed_at'])) ?></span>
                </div>
                <div class="detail-item">
                    <label>İşleyen Admin:</label>
                    <span><?= htmlspecialchars($request['processed_by_name'] ?? 'Sistem') ?></span>
                </div>
            </div>
            <?php if ($request['admin_notes']): ?>
                <div class="admin-notes">
                    <label>Admin Notları:</label>
                    <div class="notes-box">
                        <?= nl2br(htmlspecialchars($request['admin_notes'])) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="detail-actions">
        <?php if ($request['status'] === 'pending'): ?>
            <button onclick="approveRequest(<?= $request['id'] ?>)" class="btn btn-success">Onayla</button>
            <button onclick="rejectRequest(<?= $request['id'] ?>)" class="btn btn-danger">Reddet</button>
        <?php endif; ?>

        <?php if ($request['status'] === 'approved'): ?>
            <button onclick="completeRequest(<?= $request['id'] ?>)" class="btn btn-danger">Tamamla (Kullanıcıyı Sil)</button>
        <?php endif; ?>

        <a href="<?= url('/admin/silme-talepleri') ?>" class="btn btn-secondary">Geri Dön</a>
    </div>
</div>

<!-- Approve Modal -->
<div id="approveModal" class="modal">
    <div class="modal-content">
        <h2>Silme Talebini Onayla</h2>
        <form method="POST" action="<?= url('/admin/silme-talebi/onayla') ?>" id="approveForm">
            <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
            <div class="form-group">
                <label for="approve_notes">Admin Notu (Opsiyonel)</label>
                <textarea name="admin_notes" id="approve_notes" class="form-input" rows="3"></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" onclick="closeModal('approveModal')" class="btn btn-secondary">İptal</button>
                <button type="submit" class="btn btn-success">Onayla</button>
            </div>
        </form>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="modal">
    <div class="modal-content">
        <h2>Silme Talebini Reddet</h2>
        <form method="POST" action="<?= url('/admin/silme-talebi/reddet') ?>" id="rejectForm">
            <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
            <div class="form-group">
                <label for="reject_notes">Red Nedeni *</label>
                <textarea name="admin_notes" id="reject_notes" class="form-input" rows="3" required></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" onclick="closeModal('rejectModal')" class="btn btn-secondary">İptal</button>
                <button type="submit" class="btn btn-danger">Reddet</button>
            </div>
        </form>
    </div>
</div>

<!-- Complete Modal -->
<div id="completeModal" class="modal">
    <div class="modal-content">
        <h2>⚠️ Hesabı Kalıcı Olarak Sil</h2>
        <div class="warning-box">
            <p><strong>Dikkat!</strong> Bu işlem geri alınamaz. Kullanıcının tüm verileri kalıcı olarak silinecektir:</p>
            <ul>
                <li>Kullanıcı hesabı</li>
                <li>Firma bilgileri</li>
                <li>Firma logosu</li>
                <li>KVKK onayları</li>
                <li>Tüm istatistikler</li>
            </ul>
        </div>
        <form method="POST" action="<?= url('/admin/silme-talebi/tamamla') ?>" id="completeForm">
            <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
            <div class="form-group">
                <label>
                    <input type="checkbox" name="confirm_deletion" required>
                    Kullanıcı verilerini kalıcı olarak silmek istediğimi onaylıyorum.
                </label>
            </div>
            <div class="modal-actions">
                <button type="button" onclick="closeModal('completeModal')" class="btn btn-secondary">İptal</button>
                <button type="submit" class="btn btn-danger">Hesabı Kalıcı Olarak Sil</button>
            </div>
        </form>
    </div>
</div>

<style>
.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.detail-container {
    width: 100%;
}

.detail-card {
    background: white;
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    padding: 2rem;
    margin-bottom: 1.5rem;
}

.detail-card h2 {
    font-size: 1.25rem;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid var(--border-color);
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

.detail-item label {
    font-weight: 600;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.detail-item span {
    color: var(--text-primary);
    font-size: 1rem;
}

.reason-box, .notes-box {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 0.5rem;
    border: 1px solid var(--border-color);
    line-height: 1.6;
    color: var(--text-secondary);
}

.admin-notes {
    margin-top: 1.5rem;
}

.admin-notes label {
    font-weight: 600;
    color: var(--text-secondary);
    font-size: 0.9rem;
    display: block;
    margin-bottom: 0.5rem;
}

.detail-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.85rem;
    font-weight: 500;
}

.badge-warning { background: #fff3cd; color: #856404; }
.badge-danger { background: #f8d7da; color: #721c24; }
.badge-success { background: #d4edda; color: #155724; }
.badge-info { background: #d1ecf1; color: #0c5460; }
.badge-secondary { background: #e2e3e5; color: #383d41; }

.modal {
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

.modal.active {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 0.75rem;
    max-width: 500px;
    width: 90%;
    padding: 2rem;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-content h2 {
    margin-bottom: 1.5rem;
}

.modal-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
    justify-content: flex-end;
}

.warning-box {
    background: #fff3cd;
    border: 1px solid #ffc107;
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}

.warning-box ul {
    margin: 0.5rem 0 0 1.5rem;
}

.warning-box li {
    margin-bottom: 0.25rem;
}

@media (max-width: 768px) {
    .admin-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .detail-grid {
        grid-template-columns: 1fr;
    }

    .detail-actions {
        flex-direction: column;
    }

    .detail-actions .btn {
        width: 100%;
    }
}
</style>

<script>
function approveRequest(requestId) {
    document.getElementById('approveModal').classList.add('active');
}

function rejectRequest(requestId) {
    document.getElementById('rejectModal').classList.add('active');
}

function completeRequest(requestId) {
    document.getElementById('completeModal').classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

// Modal dışına tıklanınca kapat
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal(this.id);
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/admin-footer.php'; ?>
