<?php
/**
 * AI Provider Settings Page
 * Çoklu AI sağlayıcı yönetimi
 */
$pageId = 'ai-providers';

// Helper function: Time ago
function timeAgo($timestamp) {
    if (empty($timestamp)) return 'Hiçbir zaman';

    $time = strtotime($timestamp);
    $diff = time() - $time;

    if ($diff < 60) return 'Az önce';
    if ($diff < 3600) return floor($diff / 60) . ' dakika önce';
    if ($diff < 86400) return floor($diff / 3600) . ' saat önce';
    if ($diff < 604800) return floor($diff / 86400) . ' gün önce';

    return date('d.m.Y H:i', $time);
}
?>
<?php require_once __DIR__ . '/../layouts/admin-header.php'; ?>

<div class="admin-header">
    <h1>AI Sağlayıcı Ayarları</h1>
    <a href="<?= $this->getConfig('base_path') ?>/admin/ai-makale-uret" class="btn btn-secondary">← Geri Dön</a>
</div>

<div class="providers-container">
    <div class="info-banner">
        <strong>📖 Nasıl Kullanılır?</strong>
        <p>Her bir AI sağlayıcısını yapılandırıp aktif edebilirsiniz. Sistem öncelik sırasına göre sağlayıcıları dener, birincisi başarısız olursa otomatik olarak ikinciye geçer.</p>
    </div>

    <?php foreach ($providers as $provider): ?>
    <div class="provider-card <?= $provider['is_active'] ? 'active' : '' ?>">
        <div class="provider-header">
            <div class="provider-title">
                <h3><?= htmlspecialchars($provider['display_name']) ?></h3>
                <?php if ($provider['is_default']): ?>
                    <span class="badge badge-primary">Varsayılan</span>
                <?php endif; ?>
                <?php if ($provider['is_active']): ?>
                    <span class="badge badge-success">Aktif</span>
                <?php else: ?>
                    <span class="badge badge-secondary">Pasif</span>
                <?php endif; ?>
            </div>
            <div class="provider-toggle">
                <label class="switch">
                    <input type="checkbox"
                           data-provider-id="<?= $provider['id'] ?>"
                           class="provider-toggle-checkbox"
                           <?= $provider['is_active'] ? 'checked' : '' ?>>
                    <span class="slider"></span>
                </label>
            </div>
        </div>

        <div class="provider-body">
            <div class="provider-info">
                <div class="info-row">
                    <span class="label">Model:</span>
                    <span class="value"><code><?= htmlspecialchars($provider['model_name']) ?></code></span>
                </div>
                <div class="info-row">
                    <span class="label">API URL:</span>
                    <span class="value small"><?= htmlspecialchars($provider['api_url']) ?></span>
                </div>
                <div class="info-row">
                    <span class="label">API Key:</span>
                    <span class="value">
                        <?php if (!empty($provider['api_key'])): ?>
                            <span style="color: green;">Yapılandırılmış</span>
                        <?php else: ?>
                            <span style="color: red;">Yapılandırılmamış</span>
                        <?php endif; ?>
                    </span>
                </div>
                <?php if ($provider['is_active'] && !empty($provider['api_key'])): ?>
                <div class="info-row">
                    <span class="label">Öncelik:</span>
                    <span class="value"><?= $provider['priority'] ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Son kullanım:</span>
                    <span class="value"><?= timeAgo($provider['last_used_at']) ?></span>
                </div>
                <div class="info-row">
                    <span class="label">İstatistik:</span>
                    <span class="value">
                        <span style="color: green;">✓ <?= $provider['success_count'] ?></span> |
                        <span style="color: red;">✗ <?= $provider['error_count'] ?></span>
                    </span>
                </div>
                <?php if ($provider['last_error']): ?>
                <div class="info-row">
                    <span class="label">Son hata:</span>
                    <span class="value error-text small"><?= htmlspecialchars(substr($provider['last_error'], 0, 100)) ?></span>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="provider-actions">
                <button class="btn btn-sm btn-primary" onclick="editProvider(<?= $provider['id'] ?>)">
                    ⚙️ Düzenle
                </button>
                <button class="btn btn-sm btn-info" onclick="testProvider(<?= $provider['id'] ?>)">
                    🔍 Test Et
                </button>
                <?php if ($provider['is_active'] && !$provider['is_default'] && !empty($provider['api_key'])): ?>
                <button class="btn btn-sm btn-success" onclick="setDefault(<?= $provider['id'] ?>)">
                    ⭐ Varsayılan Yap
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Edit Provider Modal -->
<div id="editProviderModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2 id="modal-title">Sağlayıcı Düzenle</h2>

        <form id="providerEditForm">
            <input type="hidden" id="provider_id" name="provider_id">

            <div class="form-group">
                <label>Sağlayıcı</label>
                <input type="text" id="display_name" class="form-input" readonly>
            </div>

            <div class="form-group">
                <label for="api_key">API Key <span class="required">*</span></label>
                <input type="password" id="api_key" name="api_key" class="form-input" placeholder="API key'inizi girin">
                <small class="form-help">Güvenlik nedeniyle mevcut key gösterilmez. Değiştirmek için yenisini girin.</small>
            </div>

            <div class="form-group">
                <label for="model_name">Model</label>
                <input type="text" id="model_name" name="model_name" class="form-input">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="max_tokens">Max Tokens</label>
                    <input type="number" id="max_tokens" name="max_tokens" class="form-input" min="100" max="16000">
                </div>

                <div class="form-group">
                    <label for="temperature">Temperature</label>
                    <input type="number" id="temperature" name="temperature" class="form-input" min="0" max="2" step="0.01">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="priority">Öncelik (Failover)</label>
                    <input type="number" id="priority" name="priority" class="form-input" min="0" max="999">
                    <small class="form-help">Yüksek öncelikli sağlayıcılar önce denenir.</small>
                </div>

                <div class="form-group">
                    <label for="timeout_seconds">Timeout (saniye)</label>
                    <input type="number" id="timeout_seconds" name="timeout_seconds" class="form-input" min="10" max="300">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Kaydet</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">❌ İptal</button>
            </div>
        </form>
    </div>
</div>

<!-- Test Result Modal -->
<div id="testResultModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeTestModal()">&times;</span>
        <h2>Test Sonucu</h2>
        <div id="test-result-content"></div>
        <div class="form-actions">
            <button class="btn btn-secondary" onclick="closeTestModal()">Kapat</button>
        </div>
    </div>
</div>

<style>
.providers-container {
    max-width: 900px;
    margin: 0 auto;
}

.info-banner {
    background: #e8f4fd;
    border-left: 4px solid #2196F3;
    padding: 15px 20px;
    margin-bottom: 30px;
    border-radius: 4px;
}

.info-banner strong {
    display: block;
    margin-bottom: 8px;
    color: #1976D2;
}

.info-banner p {
    margin: 0;
    color: #333;
}

.provider-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin-bottom: 20px;
    transition: all 0.3s;
}

.provider-card.active {
    border-color: #4CAF50;
    box-shadow: 0 2px 8px rgba(76, 175, 80, 0.1);
}

.provider-header {
    padding: 20px;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.provider-title {
    display: flex;
    align-items: center;
    gap: 10px;
}

.provider-title h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}

.badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.badge-primary {
    background: #2196F3;
    color: white;
}

.badge-success {
    background: #4CAF50;
    color: white;
}

.badge-secondary {
    background: #9E9E9E;
    color: white;
}

.provider-body {
    padding: 20px;
}

.provider-info {
    margin-bottom: 20px;
}

.info-row {
    display: flex;
    padding: 8px 0;
    border-bottom: 1px solid #f5f5f5;
}

.info-row:last-child {
    border-bottom: none;
}

.info-row .label {
    font-weight: 600;
    min-width: 150px;
    color: #666;
}

.info-row .value {
    flex: 1;
    color: #333;
}

.info-row .value.small {
    font-size: 13px;
    word-break: break-all;
}

.info-row .value code {
    background: #f5f5f5;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 13px;
}

.error-text {
    color: #d32f2f;
    font-style: italic;
}

.provider-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

/* Toggle Switch */
.switch {
    position: relative;
    display: inline-block;
    width: 54px;
    height: 28px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 28px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: #4CAF50;
}

input:checked + .slider:before {
    transform: translateX(26px);
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 30px;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    position: relative;
    max-height: 85vh;
    overflow-y: auto;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    line-height: 20px;
}

.close:hover,
.close:focus {
    color: black;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.required {
    color: red;
}

#test-result-content {
    padding: 20px 0;
}

.test-success {
    color: green;
    padding: 15px;
    background: #f1f8f4;
    border-left: 4px solid #4CAF50;
    border-radius: 4px;
    margin: 10px 0;
}

.test-error {
    color: #d32f2f;
    padding: 15px;
    background: #ffebee;
    border-left: 4px solid #f44336;
    border-radius: 4px;
    margin: 10px 0;
}

/* Responsive */
@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }

    .provider-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }

    .provider-actions {
        flex-direction: column;
    }

    .provider-actions .btn {
        width: 100%;
    }
}
</style>

<script>
// Toggle Active/Inactive
document.querySelectorAll('.provider-toggle-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const providerId = this.getAttribute('data-provider-id');
        const isActive = this.checked;

        fetch('<?= $this->getConfig('base_path') ?>/admin/ai-provider-toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `provider_id=${providerId}&is_active=${isActive ? 1 : 0}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Durum güncellendi', 'success');
                setTimeout(() => location.reload(), 500);
            } else {
                showNotification('Hata oluştu', 'error');
                this.checked = !isActive; // Revert
            }
        })
        .catch(error => {
            showNotification('Bağlantı hatası', 'error');
            this.checked = !isActive; // Revert
        });
    });
});

// Edit Provider
function editProvider(id) {
    fetch('<?= $this->getConfig('base_path') ?>/admin/ai-provider-get/' + id)
        .then(response => response.json())
        .then(provider => {
            document.getElementById('provider_id').value = provider.id;
            document.getElementById('display_name').value = provider.display_name;
            document.getElementById('api_key').value = ''; // Don't show existing key
            document.getElementById('api_key').placeholder = provider.api_key ? 'Mevcut key değiştirmek için yenisini girin' : 'API key girin';
            document.getElementById('model_name').value = provider.model_name;
            document.getElementById('max_tokens').value = provider.max_tokens;
            document.getElementById('temperature').value = provider.temperature;
            document.getElementById('priority').value = provider.priority;
            document.getElementById('timeout_seconds').value = provider.timeout_seconds;

            document.getElementById('editProviderModal').style.display = 'block';
        })
        .catch(error => {
            showNotification('Sağlayıcı bilgileri yüklenemedi', 'error');
        });
}

// Save Provider
document.getElementById('providerEditForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const providerId = document.getElementById('provider_id').value;
    const formData = new FormData(this);

    fetch('<?= $this->getConfig('base_path') ?>/admin/ai-provider-update/' + providerId, {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Kaydedildi!', 'success');
            closeModal();
            setTimeout(() => location.reload(), 500);
        } else {
            showNotification(data.error || 'Kaydetme hatası', 'error');
        }
    })
    .catch(error => {
        showNotification('Bağlantı hatası', 'error');
    });
});

// Test Provider
function testProvider(id) {
    showNotification('Test başlatıldı...', 'info');

    fetch('<?= $this->getConfig('base_path') ?>/admin/ai-provider-test', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `provider_id=${id}`
    })
    .then(response => response.json())
    .then(data => {
        const resultDiv = document.getElementById('test-result-content');

        if (data.success) {
            resultDiv.innerHTML = `
                <div class="test-success">
                    <strong>✅ Bağlantı Başarılı!</strong>
                    <p>${data.message}</p>
                </div>
            `;
        } else {
            resultDiv.innerHTML = `
                <div class="test-error">
                    <strong>❌ Bağlantı Başarısız</strong>
                    <p>${data.message || data.error}</p>
                </div>
            `;
        }

        document.getElementById('testResultModal').style.display = 'block';
    })
    .catch(error => {
        showNotification('Test sırasında hata oluştu', 'error');
    });
}

// Set Default Provider
function setDefault(id) {
    if (!confirm('Bu sağlayıcıyı varsayılan yapmak istediğinizden emin misiniz?')) {
        return;
    }

    fetch('<?= $this->getConfig('base_path') ?>/admin/ai-provider-set-default', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `provider_id=${id}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Varsayılan sağlayıcı güncellendi', 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            showNotification('Hata oluştu', 'error');
        }
    })
    .catch(error => {
        showNotification('Bağlantı hatası', 'error');
    });
}

// Close Modals
function closeModal() {
    document.getElementById('editProviderModal').style.display = 'none';
}

function closeTestModal() {
    document.getElementById('testResultModal').style.display = 'none';
}

// Close on outside click
window.onclick = function(event) {
    const editModal = document.getElementById('editProviderModal');
    const testModal = document.getElementById('testResultModal');

    if (event.target == editModal) {
        closeModal();
    }
    if (event.target == testModal) {
        closeTestModal();
    }
}

// Notification
function showNotification(message, type) {
    const colors = {
        success: '#4CAF50',
        error: '#f44336',
        info: '#2196F3'
    };

    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${colors[type] || colors.info};
        color: white;
        padding: 15px 20px;
        border-radius: 4px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        z-index: 10000;
        animation: slideIn 0.3s ease-out;
    `;
    notification.textContent = message;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Add animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
</script>

<?php require_once __DIR__ . '/../layouts/admin-footer.php'; ?>
