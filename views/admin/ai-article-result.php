<?php $pageId = 'ai-articles'; ?>
<?php require_once __DIR__ . '/../layouts/admin-header.php'; ?>

<div class="admin-header">
    <h1>Toplu Üretim İşlemi</h1>
    <a href="<?= $this->getConfig('base_path') ?>/admin/makaleler" class="btn btn-primary">Makaleleri Görüntüle</a>
</div>

<div class="result-container">
    <!-- Progress Section -->
    <div id="progress-section" class="progress-section">
        <div class="progress-header">
            <h2 id="status-title">
                <?php if ($job['status'] === 'pending'): ?>
                    ⏳ İşlem Sırada Bekliyor
                <?php elseif ($job['status'] === 'processing'): ?>
                    🔄 Makaleler Üretiliyor...
                <?php elseif ($job['status'] === 'completed'): ?>
                    ✓ İşlem Tamamlandı!
                <?php else: ?>
                    ❌ İşlem Başarısız
                <?php endif; ?>
            </h2>
        </div>

        <div class="progress-bar-container">
            <div class="progress-bar" id="progress-bar" style="width: <?= $job['progress'] ?>%">
                <span id="progress-text"><?= $job['progress'] ?>%</span>
            </div>
        </div>

        <div class="progress-stats">
            <div class="stat">
                <strong>İşlenen:</strong>
                <span id="processed-count"><?= $job['processed_items'] ?></span> / <span id="total-count"><?= $job['total_items'] ?></span>
            </div>
            <div class="stat">
                <strong>Durum:</strong>
                <span id="job-status" class="status-badge status-<?= $job['status'] ?>">
                    <?php
                    $statusLabels = [
                        'pending' => 'Bekliyor',
                        'processing' => 'İşleniyor',
                        'completed' => 'Tamamlandı',
                        'failed' => 'Başarısız'
                    ];
                    echo $statusLabels[$job['status']] ?? $job['status'];
                    ?>
                </span>
            </div>
        </div>

        <?php if ($job['status'] === 'pending'): ?>
            <div class="manual-trigger-section">
                <p class="help-text">İşlem bekliyor. Arka plan işleyicisi otomatik olarak çalışmıyorsa manuel olarak başlatabilirsiniz:</p>
                <button type="button" id="triggerJobBtn" class="btn btn-primary">
                    🚀 İşlemi Şimdi Başlat
                </button>
                <div id="trigger-result" style="margin-top: 10px;"></div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Results Section -->
    <div id="results-section" class="results-section">
        <h3>İşlem Detayları</h3>

        <?php if (!empty($results)): ?>
            <div class="results-grid">
                <?php
                $successCount = 0;
                $failedCount = 0;
                foreach ($results as $result) {
                    if ($result['status'] === 'success') $successCount++;
                    else $failedCount++;
                }
                ?>

                <div class="result-summary">
                    <div class="summary-item success">
                        <span class="count" id="success-count"><?= $successCount ?></span>
                        <span class="label">Başarılı</span>
                    </div>
                    <div class="summary-item failed">
                        <span class="count" id="failed-count"><?= $failedCount ?></span>
                        <span class="label">Başarısız</span>
                    </div>
                </div>

                <div class="results-list" id="results-list">
                    <?php foreach ($results as $result): ?>
                        <div class="result-item result-<?= $result['status'] ?>">
                            <div class="result-location">
                                <?php if ($result['district_name']): ?>
                                    <?= htmlspecialchars($result['district_name']) ?>, <?= htmlspecialchars($result['city_name']) ?>
                                <?php else: ?>
                                    <?= htmlspecialchars($result['city_name']) ?>
                                <?php endif; ?>
                            </div>
                            <div class="result-status">
                                <?php if ($result['status'] === 'success'): ?>
                                    <span class="badge badge-success">✓ Başarılı</span>
                                    <?php if ($result['article_id']): ?>
                                        <a href="<?= $this->getConfig('base_path') ?>/admin/makale-duzenle/<?= $result['article_id'] ?>"
                                           class="btn-link btn-sm">Düzenle</a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge badge-failed">✗ Başarısız</span>
                                    <?php if ($result['error_message']): ?>
                                        <small class="error-msg"><?= htmlspecialchars($result['error_message']) ?></small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="no-results">
                <p>Henüz sonuç yok. İşlem başladığında burada görünecek.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="action-buttons">
        <a href="<?= $this->getConfig('base_path') ?>/admin/ai-makale-uret" class="btn btn-secondary">
            ← Yeni Üretim Yap
        </a>
        <a href="<?= $this->getConfig('base_path') ?>/admin/makaleler" class="btn btn-primary">
            Makalelere Git →
        </a>
    </div>
</div>

<script>
const jobId = <?= $job['id'] ?>;
const jobStatus = '<?= $job['status'] ?>';
let pollInterval;

function updateProgress(data) {
    const job = data.job;
    const results = data.results;

    // Update progress bar
    document.getElementById('progress-bar').style.width = job.progress + '%';
    document.getElementById('progress-text').textContent = job.progress + '%';

    // Update counts
    document.getElementById('processed-count').textContent = job.processed_items;
    document.getElementById('total-count').textContent = job.total_items;

    // Update status
    const statusLabels = {
        'pending': 'Bekliyor',
        'processing': 'İşleniyor',
        'completed': 'Tamamlandı',
        'failed': 'Başarısız'
    };
    const statusBadge = document.getElementById('job-status');
    statusBadge.textContent = statusLabels[job.status] || job.status;
    statusBadge.className = 'status-badge status-' + job.status;

    // Update title
    const statusTitle = document.getElementById('status-title');
    if (job.status === 'pending') {
        statusTitle.textContent = '⏳ İşlem Sırada Bekliyor';
    } else if (job.status === 'processing') {
        statusTitle.textContent = '🔄 Makaleler Üretiliyor...';
    } else if (job.status === 'completed') {
        statusTitle.textContent = '✓ İşlem Tamamlandı!';
    } else if (job.status === 'failed') {
        statusTitle.textContent = '❌ İşlem Başarısız';
    }

    // Update results list
    const resultsSection = document.getElementById('results-section');

    if (results && results.length > 0) {
        const successCount = results.filter(r => r.status === 'success').length;
        const failedCount = results.filter(r => r.status === 'failed').length;

        // Hide "no results" message
        const noResults = resultsSection.querySelector('.no-results');
        if (noResults) {
            noResults.style.display = 'none';
        }

        // Check if results-grid already exists
        let resultsGrid = resultsSection.querySelector('.results-grid');

        if (!resultsGrid) {
            // Create results grid dynamically if it doesn't exist
            resultsGrid = document.createElement('div');
            resultsGrid.className = 'results-grid';
            resultsGrid.innerHTML = `
                <div class="result-summary">
                    <div class="summary-item success">
                        <span class="count" id="success-count">0</span>
                        <span class="label">Başarılı</span>
                    </div>
                    <div class="summary-item failed">
                        <span class="count" id="failed-count">0</span>
                        <span class="label">Başarısız</span>
                    </div>
                </div>
                <div class="results-list" id="results-list"></div>
            `;
            resultsSection.appendChild(resultsGrid);
        }

        // Update counts
        document.getElementById('success-count').textContent = successCount;
        document.getElementById('failed-count').textContent = failedCount;

        // Update results list
        const resultsList = document.getElementById('results-list');
        resultsList.innerHTML = results.map(result => `
            <div class="result-item result-${result.status}">
                <div class="result-location">
                    ${result.district_name ?
                        `${result.district_name}, ${result.city_name}` :
                        result.city_name}
                </div>
                <div class="result-status">
                    ${result.status === 'success' ? `
                        <span class="badge badge-success">✓ Başarılı</span>
                        ${result.article_id ?
                            `<a href="<?= $this->getConfig('base_path') ?>/admin/makale-duzenle/${result.article_id}" class="btn-link btn-sm">Düzenle</a>` :
                            ''}
                    ` : `
                        <span class="badge badge-failed">✗ Başarısız</span>
                        ${result.error_message ?
                            `<small class="error-msg">${result.error_message}</small>` :
                            ''}
                    `}
                </div>
            </div>
        `).join('');
    }

    // Stop polling if completed or failed
    if (job.status === 'completed' || job.status === 'failed') {
        clearInterval(pollInterval);
    }
}

function pollJobStatus() {
    fetch('<?= $this->getConfig('base_path') ?>/admin/job-status?job_id=' + jobId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateProgress(data);
            }
        })
        .catch(error => {
            console.error('Error polling job status:', error);
        });
}

// Start polling if job is pending or processing
if (jobStatus === 'pending' || jobStatus === 'processing') {
    // Poll every 2 seconds
    pollInterval = setInterval(pollJobStatus, 2000);

    // Poll immediately
    pollJobStatus();
}

// Manual trigger button
const triggerBtn = document.getElementById('triggerJobBtn');
if (triggerBtn) {
    triggerBtn.addEventListener('click', function() {
        const resultDiv = document.getElementById('trigger-result');
        const btn = this;

        btn.disabled = true;
        btn.textContent = '⏳ İşlem başlatılıyor...';
        resultDiv.innerHTML = '';

        fetch('<?= $this->getConfig('base_path') ?>/admin/trigger-job-processor', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ job_id: jobId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = '<div class="alert alert-success">✓ İşlem başlatıldı! Sayfa otomatik güncellenecek...</div>';
                // Hide the manual trigger section after 2 seconds
                setTimeout(() => {
                    document.querySelector('.manual-trigger-section').style.display = 'none';
                }, 2000);
                // Start polling immediately
                if (!pollInterval) {
                    pollInterval = setInterval(pollJobStatus, 2000);
                }
                pollJobStatus();
            } else {
                resultDiv.innerHTML = '<div class="alert alert-danger">❌ ' + (data.error || 'İşlem başlatılamadı') + '</div>';
                btn.disabled = false;
                btn.textContent = '🚀 İşlemi Şimdi Başlat';
            }
        })
        .catch(error => {
            console.error('Error triggering job:', error);
            resultDiv.innerHTML = '<div class="alert alert-danger">❌ Bir hata oluştu</div>';
            btn.disabled = false;
            btn.textContent = '🚀 İşlemi Şimdi Başlat';
        });
    });
}
</script>

<style>
.result-container {
    max-width: 900px;
    margin: 0 auto;
}

.progress-section {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 24px;
}

.progress-header h2 {
    margin: 0 0 24px 0;
    font-size: 24px;
    text-align: center;
}

.progress-bar-container {
    width: 100%;
    height: 40px;
    background: #e9ecef;
    border-radius: 20px;
    overflow: hidden;
    margin-bottom: 20px;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #28a745, #20c997);
    transition: width 0.5s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    min-width: 60px;
}

.progress-stats {
    display: flex;
    justify-content: space-around;
    gap: 20px;
}

.stat {
    text-align: center;
    font-size: 16px;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
}

.status-pending { background: #ffc107; color: #000; }
.status-processing { background: #17a2b8; color: #fff; }
.status-completed { background: #28a745; color: #fff; }
.status-failed { background: #dc3545; color: #fff; }

.results-section {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 24px;
}

.results-section h3 {
    margin: 0 0 20px 0;
    font-size: 20px;
}

.result-summary {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.summary-item {
    padding: 20px;
    border-radius: 8px;
    text-align: center;
}

.summary-item.success {
    background: #d4edda;
    border: 2px solid #28a745;
}

.summary-item.failed {
    background: #f8d7da;
    border: 2px solid #dc3545;
}

.summary-item .count {
    display: block;
    font-size: 36px;
    font-weight: bold;
    margin-bottom: 8px;
}

.summary-item.success .count { color: #28a745; }
.summary-item.failed .count { color: #dc3545; }

.summary-item .label {
    font-size: 14px;
    text-transform: uppercase;
    font-weight: 600;
}

.results-list {
    max-height: 400px;
    overflow-y: auto;
}

.result-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    margin-bottom: 8px;
    border-radius: 6px;
    border-left: 4px solid;
}

.result-item.result-success {
    background: #d4edda;
    border-left-color: #28a745;
}

.result-item.result-failed {
    background: #f8d7da;
    border-left-color: #dc3545;
}

.result-location {
    font-weight: 600;
    font-size: 15px;
}

.result-status {
    display: flex;
    align-items: center;
    gap: 8px;
}

.badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
}

.badge-success {
    background: #28a745;
    color: white;
}

.badge-failed {
    background: #dc3545;
    color: white;
}

.btn-link {
    color: #007bff;
    text-decoration: none;
    font-size: 13px;
}

.btn-link:hover {
    text-decoration: underline;
}

.error-msg {
    display: block;
    color: #721c24;
    font-size: 12px;
    margin-top: 4px;
}

.no-results {
    text-align: center;
    padding: 40px;
    color: #6c757d;
}

.action-buttons {
    display: flex;
    gap: 12px;
    justify-content: center;
}

.manual-trigger-section {
    margin-top: 20px;
    padding: 20px;
    background: #fff3cd;
    border: 2px solid #ffc107;
    border-radius: 8px;
    text-align: center;
}

.manual-trigger-section .help-text {
    margin-bottom: 15px;
    color: #856404;
    font-size: 14px;
}

.manual-trigger-section .btn {
    font-size: 16px;
    padding: 12px 24px;
}

.alert {
    padding: 12px 16px;
    border-radius: 6px;
    font-size: 14px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

@media (max-width: 768px) {
    .result-summary {
        grid-template-columns: 1fr;
    }

    .action-buttons {
        flex-direction: column;
    }

    .result-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/admin-footer.php'; ?>
