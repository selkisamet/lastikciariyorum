<?php $pageId = 'districts'; ?>
<?php require_once __DIR__ . '/../layouts/admin-header.php'; ?>

<div class="admin-header">
    <h1>İlçe Yönetimi</h1>
</div>

<div class="articles-controls">
    <div class="admin-search-box">
        <input type="text" id="searchInput" class="form-input" placeholder="İlçe veya şehir adı ile ara...">
    </div>
</div>

<?php if (!empty($districts)): ?>
    <div class="table-responsive">
        <table class="data-table" id="districtsTable">
            <thead>
                <tr>
                    <th>İlçe Adı</th>
                    <th>Şehir</th>
                    <th>Makale Durumu</th>
                    <th>Firma Sayısı</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($districts as $district): ?>
                    <tr class="district-row"
                        data-district-name="<?= htmlspecialchars(normalizeTurkish($district['name'])) ?>"
                        data-city-name="<?= htmlspecialchars(normalizeTurkish($district['city_name'])) ?>">
                        <td>
                            <strong><?= htmlspecialchars($district['name']) ?></strong>
                            <br>
                            <small class="text-muted">Slug: <?= htmlspecialchars($district['slug']) ?></small>
                        </td>
                        <td>
                            <span class="text-muted"><?= htmlspecialchars($district['city_name']) ?></span>
                        </td>
                        <td>
                            <?php if ($district['has_article']): ?>
                                <span class="badge badge-success">✓ Makale Var</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Makale Yok</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="view-count"><?= $district['company_count'] ?? 0 ?></span>
                        </td>
                        <td class="action-buttons">
                            <a href="<?= $this->getConfig('base_path') ?>/<?= $district['city_slug'] ?>/<?= $district['slug'] ?>"
                                class="btn btn-small btn-info btn-icon" title="Görüntüle" target="_blank">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </a>
                            <a href="<?= $this->getConfig('base_path') ?>/admin/ilce-duzenle/<?= $district['id'] ?>"
                                class="btn btn-small btn-secondary btn-icon" title="İlçe Bilgisi Düzenle">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                </svg>
                            </a>
                            <?php if ($district['has_article']): ?>
                                <a href="<?= $this->getConfig('base_path') ?>/admin/makale-duzenle/<?= $district['article_id'] ?>"
                                    class="btn btn-small btn-primary btn-icon" title="Makaleyi Düzenle">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="16" y1="13" x2="8" y2="13"></line>
                                        <line x1="16" y1="17" x2="8" y2="17"></line>
                                        <polyline points="10 9 9 9 8 9"></polyline>
                                    </svg>
                                </a>
                            <?php else: ?>
                                <a href="<?= $this->getConfig('base_path') ?>/admin/ai-makale-uret?district_id=<?= $district['id'] ?>"
                                    class="btn btn-small btn-success btn-icon" title="Makale Oluştur">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="empty-state">
        <p>Hiç ilçe bulunamadı.</p>
    </div>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const districtsTable = document.getElementById('districtsTable');
        const districtRows = document.querySelectorAll('.district-row');
        const totalCount = document.getElementById('totalCount');

        // Türkçe karakterleri normalize et
        function normalizeTurkish(text) {
            if (!text) return '';
            return text
                .replace(/İ/g, 'i')
                .replace(/I/g, 'ı')
                .replace(/Ş/g, 'ş')
                .replace(/Ğ/g, 'ğ')
                .replace(/Ü/g, 'ü')
                .replace(/Ö/g, 'ö')
                .replace(/Ç/g, 'ç')
                .toLocaleLowerCase('tr-TR');
        }

        if (searchInput && districtsTable) {
            searchInput.addEventListener('input', function() {
                const searchTerm = normalizeTurkish(this.value.trim());
                let visibleCount = 0;

                districtRows.forEach(row => {
                    const districtName = row.getAttribute('data-district-name');
                    const cityName = row.getAttribute('data-city-name');

                    if (districtName.includes(searchTerm) || cityName.includes(searchTerm)) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                totalCount.textContent = visibleCount;
            });
        }
    });
</script>

<?php require_once __DIR__ . '/../layouts/admin-footer.php'; ?>