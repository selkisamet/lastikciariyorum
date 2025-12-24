<?php $pageId = 'cities'; ?>
<?php require_once __DIR__ . '/../layouts/admin-header.php'; ?>

<div class="admin-header">
    <h1>Şehir Yönetimi</h1>
</div>

<div class="articles-controls">
    <div class="admin-search-box">
        <input type="text" id="searchInput" class="form-input" placeholder="Şehir adı ile ara...">
    </div>
</div>

<?php if (!empty($cities)): ?>
    <div class="table-responsive">
        <table class="data-table" id="citiesTable">
            <thead>
                <tr>
                    <th>Şehir Adı</th>
                    <th>H1 Başlık</th>
                    <th>İçerik Durumu</th>
                    <th>H2 Bölümleri</th>
                    <th>Firma Sayısı</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cities as $city): ?>
                    <tr class="city-row" data-city-name="<?= htmlspecialchars(normalizeTurkish($city['name'])) ?>">
                        <td>
                            <strong><?= htmlspecialchars($city['name']) ?></strong>
                        </td>
                        <td>
                            <?php if (!empty($city['h1'])): ?>
                                <span class="badge badge-success">✓</span>
                            <?php else: ?>
                                <span class="badge badge-warning">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($city['content'])): ?>
                                <span class="badge badge-success">✓ Var</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Yok</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $h2Count = 0;
                            if (!empty($city['h2_sections'])) {
                                $sections = json_decode($city['h2_sections'], true);
                                $h2Count = is_array($sections) ? count($sections) : 0;
                            }
                            ?>
                            <span class="badge badge-info"><?= $h2Count ?> Bölüm</span>
                        </td>
                        <td>
                            <span class="view-count"><?= $city['company_count'] ?? 0 ?></span>
                        </td>
                        <td class="action-buttons">
                            <a href="<?= $this->getConfig('base_path') ?>/<?= $city['slug'] ?>"
                                class="btn btn-small btn-info btn-icon" title="Görüntüle" target="_blank">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </a>
                            <a href="<?= $this->getConfig('base_path') ?>/admin/sehir-duzenle/<?= $city['id'] ?>"
                                class="btn btn-small btn-primary btn-icon" title="Düzenle">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                </svg>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="empty-state">
        <p>Hiç şehir bulunamadı.</p>
    </div>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const citiesTable = document.getElementById('citiesTable');
        const cityRows = document.querySelectorAll('.city-row');
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
                .toLowerCase();
        }

        if (searchInput && citiesTable) {
            searchInput.addEventListener('input', function() {
                const searchTerm = normalizeTurkish(this.value.trim());
                let visibleCount = 0;

                cityRows.forEach(row => {
                    const cityName = row.getAttribute('data-city-name');

                    if (cityName.includes(searchTerm)) {
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