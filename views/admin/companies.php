<?php $pageId = 'companies'; ?>
<?php require_once __DIR__ . '/../layouts/admin-header.php'; ?>

<div class="admin-header">
            <h1>Firma Yönetimi</h1>
            <div class="header-actions">
                <a href="<?= $this->getConfig('base_path') ?>/admin/firma-import" class="btn btn-success">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 0.5rem;">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="17 8 12 3 7 8"></polyline>
                        <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                    Excel ile İçe Aktar
                </a>
                <a href="<?= $this->getConfig('base_path') ?>/admin/firma-ekle" class="btn btn-primary">+ Yeni Firma Ekle</a>
            </div>
        </div>

        <div class="companies-controls">
            <div class="admin-search-box">
                <input type="text" id="searchInput" class="form-input" placeholder="Firma adı ile ara...">
            </div>

            <div class="companies-stats">
                <span class="stat-item">
                    <strong>Toplam:</strong> <span id="totalCount"><?= count($companies) ?></span>
                </span>
                <span class="stat-item">
                    <strong>Onay Bekleyen:</strong>
                    <span id="pendingCount" class="badge badge-warning">
                        <?= count(array_filter($companies, fn($c) => $c['is_approved'] == 0)) ?>
                    </span>
                </span>
                <span class="stat-item">
                    <strong>Onaylı:</strong>
                    <span id="approvedCount" class="badge badge-success">
                        <?= count(array_filter($companies, fn($c) => $c['is_approved'] == 1)) ?>
                    </span>
                </span>
            </div>
        </div>

        <?php if (!empty($companies)): ?>
            <div class="table-responsive">
                <table class="data-table" id="companiesTable">
                    <thead>
                        <tr>
                            <th>Firma Adı</th>
                            <th>İl / İlçe</th>
                            <th>Telefon</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($companies as $company): ?>
                            <tr class="company-row" data-company-name="<?= strtolower(htmlspecialchars($company['name'])) ?>">
                                <td class="company-name">
                                    <strong><?= htmlspecialchars($company['name']) ?></strong>
                                </td>
                                <td>
                                    <?= htmlspecialchars($company['city_name']) ?>
                                    <?= $company['district_name'] ? ' / ' . htmlspecialchars($company['district_name']) : '' ?>
                                </td>
                                <td><?= htmlspecialchars($company['phone'] ?? '-') ?></td>
                                <td>
                                    <?php if ($company['is_approved']): ?>
                                        <span class="status-badge status-approved">Onaylı</span>
                                    <?php else: ?>
                                        <span class="status-badge status-pending">Bekliyor</span>
                                    <?php endif; ?>
                                </td>
                                <td class="action-buttons">
                                    <a href="<?= $this->getConfig('base_path') ?>/admin/firma-detay/<?= $company['id'] ?>"
                                       class="btn btn-small btn-info btn-icon" title="Görüntüle">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </a>
                                    <a href="<?= $this->getConfig('base_path') ?>/admin/firma-duzenle/<?= $company['id'] ?>"
                                       class="btn btn-small btn-primary btn-icon" title="Düzenle">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                        </svg>
                                    </a>
                                    <form method="POST" action="<?= $this->getConfig('base_path') ?>/admin/firma-sil/<?= $company['id'] ?>" style="display: inline;" class="delete-form">
                                        <button type="submit" class="btn btn-small btn-danger btn-icon" title="Sil">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                                <line x1="14" y1="11" x2="14" y2="17"></line>
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="table-info">
                <p id="noResults" style="display: none; text-align: center; padding: 2rem; color: var(--text-secondary);">
                    Arama kriterlerine uygun firma bulunamadı.
                </p>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>Henüz hiç firma eklenmemiş.</p>
                <a href="<?= $this->getConfig('base_path') ?>/admin/firma-ekle" class="btn btn-primary">İlk Firmayı Ekle</a>
            </div>
        <?php endif; ?>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gerçek zamanlı arama
    const searchInput = document.getElementById('searchInput');
    const companiesTable = document.getElementById('companiesTable');
    const companyRows = document.querySelectorAll('.company-row');
    const noResults = document.getElementById('noResults');
    const totalCount = document.getElementById('totalCount');

    if (searchInput && companiesTable) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            let visibleCount = 0;

            companyRows.forEach(row => {
                const companyName = row.getAttribute('data-company-name');

                if (companyName.includes(searchTerm)) {
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
                companiesTable.style.display = 'none';
                noResults.style.display = 'block';
            } else {
                companiesTable.style.display = 'table';
                noResults.style.display = 'none';
            }
        });
    }

    // Silme işlemini modern modal ile doğrula
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formElement = this;

            if (window.modal) {
                window.modal.confirm(
                    'Bu firmayı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!',
                    function() {
                        // Onaylandı, formu gönder
                        formElement.submit();
                    },
                    null,
                    'Firma Silme Onayı'
                );
            }
        });
    });

    // Onay işlemini modern modal ile doğrula
    document.querySelectorAll('form[action*="firma-onayla"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formElement = this;

            if (window.modal) {
                window.modal.confirm(
                    'Bu firmayı onaylamak istediğinizden emin misiniz?',
                    function() {
                        // Onaylandı, formu gönder
                        formElement.submit();
                    },
                    null,
                    'Firma Onaylama'
                );
            }
        });
    });
});
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

.header-actions {
    display: flex;
    gap: 0.75rem;
}

.btn-success {
    background-color: #28a745;
    color: white;
}

.btn-success:hover {
    background-color: #218838;
}

.companies-controls {
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

.companies-stats {
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

.status-badge {
    display: inline-block;
    padding: 0.4rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.85rem;
    font-weight: 600;
    text-align: center;
}

.status-approved {
    background: #d4edda;
    color: #155724;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.company-name {
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

.btn-info {
    background-color: #17a2b8;
    color: white;
}

.btn-info:hover {
    background-color: #138496;
}

.text-muted {
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.table-info {
    margin-top: 1rem;
}

@media (max-width: 768px) {
    .companies-controls {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }

    .admin-search-box {
        flex: 1 1 auto;
        max-width: 100%;
    }

    .companies-stats {
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
