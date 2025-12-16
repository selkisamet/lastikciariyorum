<?php $pageId = 'users'; ?>
<?php require_once __DIR__ . '/../layouts/admin-header.php'; ?>

<div class="admin-header">
    <h1>Kullanıcı Yönetimi</h1>
    <a href="<?= $this->getConfig('base_path') ?>/admin/kullanici-ekle" class="btn btn-primary">+ Yeni Kullanıcı Ekle</a>
</div>

<div class="users-controls">
    <div class="admin-search-box">
        <input type="text" id="searchInput" class="form-input" placeholder="Kullanıcı adı veya e-posta ile ara...">
    </div>

    <div class="users-stats">
        <span class="stat-item">
            <strong>Toplam:</strong> <span id="totalCount"><?= count($users) ?></span>
        </span>
        <span class="stat-item">
            <strong>Aktif:</strong>
            <span id="activeCount" class="badge badge-success">
                <?= count(array_filter($users, fn($u) => $u['is_active'] == 1)) ?>
            </span>
        </span>
        <span class="stat-item">
            <strong>Pasif:</strong>
            <span id="inactiveCount" class="badge badge-warning">
                <?= count(array_filter($users, fn($u) => $u['is_active'] == 0)) ?>
            </span>
        </span>
    </div>
</div>

<?php if (!empty($users)): ?>
    <div class="table-responsive">
        <table class="data-table" id="usersTable">
            <thead>
                <tr>
                    <th>Ad Soyad</th>
                    <th>E-posta</th>
                    <th>Firma</th>
                    <th>Rol</th>
                    <th>Durum</th>
                    <th>Kayıt Tarihi</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr class="user-row" data-user-info="<?= strtolower(htmlspecialchars($user['full_name'] . ' ' . $user['email'])) ?>">
                        <td class="user-name">
                            <strong><?= htmlspecialchars($user['full_name']) ?></strong>
                        </td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <?php if ($user['company_name']): ?>
                                <a href="<?= $this->getConfig('base_path') ?>/admin/firma-detay/<?= $user['company_id'] ?>">
                                    <?= htmlspecialchars($user['company_name']) ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($user['role'] === 'admin'): ?>
                                <span class="badge badge-danger">Admin</span>
                            <?php else: ?>
                                <span class="badge badge-info">Firma</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($user['is_active']): ?>
                                <span class="status-badge status-active">Aktif</span>
                            <?php else: ?>
                                <span class="status-badge status-inactive">Pasif</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $date = new DateTime($user['created_at']);
                            echo $date->format('d.m.Y');
                            ?>
                        </td>
                        <td class="action-buttons">
                            <?php if ($user['role'] !== 'admin'): ?>
                                <form method="POST" action="<?= $this->getConfig('base_path') ?>/admin/kullanici-sil/<?= $user['id'] ?>" style="display: inline;" onsubmit="return confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!');">
                                    <button type="submit" class="btn btn-small btn-danger btn-icon" title="Sil">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            <line x1="10" y1="11" x2="10" y2="17"></line>
                                            <line x1="14" y1="11" x2="14" y2="17"></line>
                                        </svg>
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="table-info">
        <p id="noResults" style="display: none; text-align: center; padding: 2rem; color: var(--text-secondary);">
            Arama kriterlerine uygun kullanıcı bulunamadı.
        </p>
    </div>
<?php else: ?>
    <div class="empty-state">
        <p>Henüz hiç kullanıcı eklenmemiş.</p>
        <a href="<?= $this->getConfig('base_path') ?>/admin/kullanici-ekle" class="btn btn-primary">İlk Kullanıcıyı Ekle</a>
    </div>
<?php endif; ?>

<script>
// Gerçek zamanlı arama
const searchInput = document.getElementById('searchInput');
const usersTable = document.getElementById('usersTable');
const userRows = document.querySelectorAll('.user-row');
const noResults = document.getElementById('noResults');
const totalCount = document.getElementById('totalCount');

if (searchInput && usersTable) {
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        let visibleCount = 0;

        userRows.forEach(row => {
            const userInfo = row.getAttribute('data-user-info');

            if (userInfo.includes(searchTerm)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Sonuç sayısını güncelle
        totalCount.textContent = visibleCount;

        // Sonuç bulunamadı mesajını göster/gizle
        if (visibleCount === 0) {
            usersTable.style.display = 'none';
            noResults.style.display = 'block';
        } else {
            usersTable.style.display = 'table';
            noResults.style.display = 'none';
        }
    });
}
</script>

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

.users-controls {
    display: flex;
    justify-content: flex-start;
    align-items: center;
    gap: 2rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.admin-search-box {
    flex: 0 0 auto;
    width: 400px;
    max-width: 400px;
    margin-right: auto;
}

.admin-search-box input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid var(--border-color);
    border-radius: 0.5rem;
    font-size: 0.95rem;
    transition: all 0.2s ease;
    text-align: left;
}

.admin-search-box input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(51, 102, 204, 0.1);
}

.users-stats {
    display: flex;
    gap: 1.5rem;
    align-items: center;
    flex-wrap: wrap;
}

.stat-item {
    font-size: 0.9rem;
    color: var(--text-secondary);
}

.stat-item strong {
    color: var(--text-primary);
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge-warning {
    background: #fff3cd;
    color: #856404;
}

.badge-success {
    background: #d4edda;
    color: #155724;
}

.badge-info {
    background: #d1ecf1;
    color: #0c5460;
}

.badge-danger {
    background: #f8d7da;
    color: #721c24;
}

.status-badge {
    display: inline-block;
    padding: 0.4rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.85rem;
    font-weight: 600;
    text-align: center;
}

.status-active {
    background: #d4edda;
    color: #155724;
}

.status-inactive {
    background: #f8d7da;
    color: #721c24;
}

.user-name {
    font-size: 1rem;
}

.action-buttons {
    white-space: nowrap;
}

.action-buttons .btn-small {
    padding: 0.4rem 0.75rem;
    font-size: 0.875rem;
    margin-right: 0.5rem;
}

.action-buttons .btn-small:last-child {
    margin-right: 0;
}

.btn-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem !important;
    width: 32px;
    height: 32px;
}

.btn-icon svg {
    display: block;
    color: white;
    margin: 0;
}

.text-muted {
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.table-info {
    margin-top: 1rem;
}

@media (max-width: 768px) {
    .users-controls {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }

    .admin-search-box {
        flex: 1 1 auto;
        max-width: 100%;
    }

    .users-stats {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .admin-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .admin-header .btn {
        width: 100%;
    }

    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .action-buttons .btn-small {
        margin-right: 0;
        width: 100%;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/admin-footer.php'; ?>
