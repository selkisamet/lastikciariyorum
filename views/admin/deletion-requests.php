<?php $pageId = 'deletion-requests'; ?>
<?php require_once __DIR__ . '/../layouts/admin-header.php'; ?>

<div class="admin-header">
    <h1>Veri Silme Talepleri</h1>
    <p>Kullanıcıların hesap ve veri silme taleplerini yönetin</p>
</div>
        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-number"><?= $pendingCount ?></div>
                <div class="stat-label">Bekleyen Talepler</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $totalCount ?></div>
                <div class="stat-label">Toplam Talepler</div>
            </div>
        </div>

        <?php if (!empty($requests)): ?>
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kullanıcı</th>
                            <th>Firma</th>
                            <th>Talep Türü</th>
                            <th>Durum</th>
                            <th>Talep Tarihi</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
                            <tr>
                                <td>#<?= $request['id'] ?></td>
                                <td>
                                    <div class="user-info">
                                        <strong><?= htmlspecialchars($request['user_name']) ?></strong>
                                        <small><?= htmlspecialchars($request['user_email']) ?></small>
                                    </div>
                                </td>
                                <td><?= $request['company_name'] ? htmlspecialchars($request['company_name']) : '-' ?></td>
                                <td>
                                    <?php if ($request['request_type'] === 'account_deletion'): ?>
                                        <span class="badge badge-danger">Hesap Silme</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Veri Silme</span>
                                    <?php endif; ?>
                                </td>
                                <td>
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
                                </td>
                                <td><?= date('d.m.Y H:i', strtotime($request['requested_at'])) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="<?= url('/admin/silme-talebi/' . $request['id']) ?>" class="btn btn-sm btn-primary">Detay</a>

                                        <?php if ($request['status'] === 'pending'): ?>
                                            <button onclick="approveRequest(<?= $request['id'] ?>)" class="btn btn-sm btn-success">Onayla</button>
                                            <button onclick="rejectRequest(<?= $request['id'] ?>)" class="btn btn-sm btn-danger">Reddet</button>
                                        <?php endif; ?>

                                        <?php if ($request['status'] === 'approved'): ?>
                                            <button onclick="completeRequest(<?= $request['id'] ?>)" class="btn btn-sm btn-success">Tamamla (Sil)</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>Henüz silme talebi bulunmuyor.</p>
            </div>
        <?php endif; ?>

<!-- Approve Modal -->
<div id="approveModal" class="modal">
    <div class="modal-content">
        <h2>Silme Talebini Onayla</h2>
        <form method="POST" action="<?= url('/admin/silme-talebi/onayla') ?>" id="approveForm">
            <input type="hidden" name="request_id" id="approve_request_id">
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
            <input type="hidden" name="request_id" id="reject_request_id">
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
            <input type="hidden" name="request_id" id="complete_request_id">
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
.admin-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 0.5rem;
    border: 1px solid var(--border-color);
    text-align: center;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.stat-label {
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.table-container {
    background: white;
    border-radius: 0.5rem;
    border: 1px solid var(--border-color);
    overflow-x: auto;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
}

.admin-table th {
    background: var(--bg-gray);
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    border-bottom: 2px solid var(--border-color);
}

.admin-table td {
    padding: 1rem;
    border-bottom: 1px solid var(--border-color);
}

.admin-table tr:last-child td {
    border-bottom: none;
}

.user-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.user-info small {
    color: var(--text-secondary);
    font-size: 0.85rem;
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

.action-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.btn-sm {
    padding: 0.4rem 0.8rem;
    font-size: 0.85rem;
}

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
</style>

<script>
function approveRequest(requestId) {
    document.getElementById('approve_request_id').value = requestId;
    document.getElementById('approveModal').classList.add('active');
}

function rejectRequest(requestId) {
    document.getElementById('reject_request_id').value = requestId;
    document.getElementById('rejectModal').classList.add('active');
}

function completeRequest(requestId) {
    document.getElementById('complete_request_id').value = requestId;
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
