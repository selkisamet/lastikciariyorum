<?php $pageId = 'dashboard'; ?>
<?php require_once __DIR__ . '/../layouts/admin-header.php'; ?>

<div class="admin-header">
    <h1>Admin Dashboard</h1>
    <div class="admin-header-actions">
        <a href="<?= $this->getConfig('base_path') ?>/admin/firma-ekle" class="btn btn-primary btn-small">
            + Yeni Firma Ekle
        </a>
        <a href="<?= $this->getConfig('base_path') ?>/admin/makale-ekle" class="btn btn-secondary btn-small">
            <span class="btn-icon">📝</span> Yeni Makale Ekle
        </a>
    </div>
</div>

<div class="dashboard-stats">
    <div class="stat-card stat-card-primary">
        <div class="stat-icon">🏢</div>
        <div class="stat-content">
            <div class="stat-label">Toplam Firma</div>
            <div class="stat-value"><?= $totalCompanies ?></div>
            <div class="stat-detail"><?= $approvedCompanies ?> onaylı</div>
        </div>
    </div>

    <div class="stat-card stat-card-warning">
        <div class="stat-icon">⏳</div>
        <div class="stat-content">
            <div class="stat-label">Bekleyen Firmalar</div>
            <div class="stat-value"><?= $pendingCount ?></div>
            <a href="<?= $this->getConfig('base_path') ?>/admin/firmalar" class="stat-link">Tümünü Gör →</a>
        </div>
    </div>

    <div class="stat-card stat-card-success">
        <div class="stat-icon">📝</div>
        <div class="stat-content">
            <div class="stat-label">Toplam Makale</div>
            <div class="stat-value"><?= $totalArticles ?></div>
            <a href="<?= $this->getConfig('base_path') ?>/admin/makaleler" class="stat-link">Tümünü Gör →</a>
        </div>
    </div>

    <div class="stat-card stat-card-danger">
        <div class="stat-icon">🗑️</div>
        <div class="stat-content">
            <div class="stat-label">Silme Talepleri</div>
            <div class="stat-value"><?= $pendingDeletionRequests ?></div>
            <a href="<?= $this->getConfig('base_path') ?>/admin/silme-talepleri" class="stat-link">Tümünü Gör →</a>
        </div>
    </div>
</div>

<?php if (!empty($last7DaysStats)): ?>
    <div class="admin-section">
        <h2 class="section-title">Son 7 Günlük Firma Ekleme İstatistiği</h2>
        <div class="chart-container">
            <canvas id="statsChart"></canvas>
        </div>
    </div>
<?php endif; ?>

<div class="admin-grid">
    <?php if (!empty($recentCompanies)): ?>
        <div class="admin-section">
            <div class="section-header">
                <h2 class="section-title">Son Eklenen Firmalar</h2>
                <a href="<?= $this->getConfig('base_path') ?>/admin/firmalar" class="section-link">Tümünü Gör →</a>
            </div>
            <div class="activity-list">
                <?php foreach ($recentCompanies as $company): ?>
                    <div class="activity-item">
                        <div class="activity-icon <?= $company['is_approved'] ? 'activity-icon-success' : 'activity-icon-warning' ?>">
                            🏢
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">
                                <a href="<?= $this->getConfig('base_path') ?>/admin/firma-detay/<?= $company['id'] ?>">
                                    <?= htmlspecialchars($company['name']) ?>
                                </a>
                            </div>
                            <div class="activity-meta">
                                <?= htmlspecialchars($company['city_name']) ?><?= $company['district_name'] ? ' / ' . htmlspecialchars($company['district_name']) : '' ?>
                                <span class="activity-time">• <?= date('d.m.Y H:i', strtotime($company['created_at'])) ?></span>
                            </div>
                        </div>
                        <div class="activity-status">
                            <?php if ($company['is_approved']): ?>
                                <span class="badge badge-success">Onaylı</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Bekliyor</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($recentArticles)): ?>
        <div class="admin-section">
            <div class="section-header">
                <h2 class="section-title">Son Eklenen Makaleler</h2>
                <a href="<?= $this->getConfig('base_path') ?>/admin/makaleler" class="section-link">Tümünü Gör →</a>
            </div>
            <div class="activity-list">
                <?php foreach ($recentArticles as $article): ?>
                    <div class="activity-item">
                        <div class="activity-icon activity-icon-info">
                            📝
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">
                                <a href="<?= $this->getConfig('base_path') ?>/admin/makale-duzenle/<?= $article['id'] ?>">
                                    <?= htmlspecialchars($article['title']) ?>
                                </a>
                            </div>
                            <div class="activity-meta">
                                <?= htmlspecialchars($article['city_name']) ?><?= $article['district_name'] ? ' / ' . htmlspecialchars($article['district_name']) : '' ?>
                                <span class="activity-time">• <?= date('d.m.Y H:i', strtotime($article['created_at'])) ?></span>
                            </div>
                        </div>
                        <div class="activity-status">
                            <span class="badge badge-info">👁 <?= $article['view_count'] ?? 0 ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($pendingCompanies)): ?>
    <div class="admin-section">
        <h2 class="section-title">Onay Bekleyen Firmalar</h2>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Firma Adı</th>
                        <th>İl / İlçe</th>
                        <th>Telefon</th>
                        <th>Eklenme Tarihi</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingCompanies as $company): ?>
                        <tr>
                            <td>
                                <a href="<?= $this->getConfig('base_path') ?>/admin/firma-detay/<?= $company['id'] ?>">
                                    <?= htmlspecialchars($company['name']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($company['city_name']) ?><?= $company['district_name'] ? ' / ' . htmlspecialchars($company['district_name']) : '' ?></td>
                            <td><?= htmlspecialchars($company['phone'] ?? '-') ?></td>
                            <td><?= date('d.m.Y H:i', strtotime($company['created_at'])) ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="<?= $this->getConfig('base_path') ?>/admin/firma-detay/<?= $company['id'] ?>" class="btn btn-small btn-secondary">Detay</a>
                                    <form method="POST" action="<?= $this->getConfig('base_path') ?>/admin/firma-onayla/<?= $company['id'] ?>" style="display: inline;">
                                        <button type="submit" class="btn btn-small btn-success">Onayla</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($last7DaysStats)): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('statsChart');
            if (ctx) {
                const data = <?= json_encode($last7DaysStats) ?>;

                // Son 7 günü hazırla
                const dates = [];
                const counts = {};

                // Tüm günleri 0 ile başlat
                for (let i = 6; i >= 0; i--) {
                    const date = new Date();
                    date.setDate(date.getDate() - i);
                    const dateStr = date.toISOString().split('T')[0];
                    dates.push(dateStr);
                    counts[dateStr] = 0;
                }

                // Verileri doldur
                data.forEach(item => {
                    if (counts.hasOwnProperty(item.date)) {
                        counts[item.date] = parseInt(item.count);
                    }
                });

                const chartData = dates.map(date => counts[date]);
                const labels = dates.map(date => {
                    const d = new Date(date);
                    return d.toLocaleDateString('tr-TR', {
                        day: 'numeric',
                        month: 'short'
                    });
                });

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Eklenen Firma Sayısı',
                            data: chartData,
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37, 99, 235, 0.1)',
                            tension: 0.3,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/admin-footer.php'; ?>