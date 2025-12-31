<?php $pageId = 'ai-settings'; ?>
<?php require_once __DIR__ . '/../layouts/admin-header.php'; ?>

<div class="admin-header">
    <h1>AI Ayarları</h1>
    <a href="<?= $this->getConfig('base_path') ?>/admin/ai-makale-uret" class="btn btn-secondary">← Geri Dön</a>
</div>

<div class="settings-container">
    <div class="card">
        <div class="card-header">
            <h3>Anthropic Claude API Ayarları</h3>
            <p>AI makale üretimi için gerekli API yapılandırması</p>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= $this->getConfig('base_path') ?>/admin/ai-ayarlar">
                <div class="form-group">
                    <label for="anthropic_api_key">Anthropic API Key <span class="required">*</span></label>
                    <input
                        type="password"
                        name="anthropic_api_key"
                        id="anthropic_api_key"
                        class="form-input"
                        value="<?= htmlspecialchars(env('ANTHROPIC_API_KEY') ?? '') ?>"
                        placeholder="sk-ant-api03-..."
                        required
                    >
                    <small class="form-help">
                        API key'inizi <a href="https://console.anthropic.com/" target="_blank">console.anthropic.com</a> adresinden alabilirsiniz.
                    </small>
                </div>

                <div class="info-box">
                    <strong>📌 API Key Nasıl Alınır?</strong>
                    <ol>
                        <li><a href="https://console.anthropic.com/" target="_blank">console.anthropic.com</a> adresine gidin</li>
                        <li>"API Keys" bölümüne tıklayın</li>
                        <li>"Create Key" butonuna basın</li>
                        <li>Oluşan key'i kopyalayıp buraya yapıştırın</li>
                    </ol>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card" style="margin-top: 20px;">
        <div class="card-header">
            <h3>API Bağlantı Testi</h3>
            <p>API key'inizin çalışıp çalışmadığını test edin</p>
        </div>
        <div class="card-body">
            <?php if (!empty(env('ANTHROPIC_API_KEY'))): ?>
                <form method="POST" action="<?= $this->getConfig('base_path') ?>/admin/ai-test-baglanti">
                    <p>API key yapılandırılmış. Test makalesi oluşturarak bağlantıyı test edebilirsiniz.</p>
                    <button type="submit" class="btn btn-info">
                        🔍 Bağlantıyı Test Et
                    </button>
                </form>
            <?php else: ?>
                <div class="alert alert-warning">
                    Önce API key yapılandırması yapmalısınız.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card" style="margin-top: 20px;">
        <div class="card-header">
            <h3>Model Bilgileri</h3>
        </div>
        <div class="card-body">
            <table class="info-table">
                <tr>
                    <th>Kullanılan Model:</th>
                    <td><code>claude-3-5-sonnet-20241022</code></td>
                </tr>
                <tr>
                    <th>Maksimum Token:</th>
                    <td>4096 token (output)</td>
                </tr>
                <tr>
                    <th>Ortalama Maliyet:</th>
                    <td>$0.02-0.08 / makale (800 kelime)</td>
                </tr>
                <tr>
                    <th>Ortalama Süre:</th>
                    <td>20-40 saniye / makale</td>
                </tr>
            </table>
        </div>
    </div>
</div>

<style>
.settings-container {
    max-width: 700px;
    margin: 0 auto;
}

.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.card-header {
    padding: 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #e0e0e0;
}

.card-header h3 {
    margin: 0 0 8px 0;
    font-size: 20px;
}

.card-header p {
    margin: 0;
    color: #666;
    font-size: 14px;
}

.card-body {
    padding: 24px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
}

.required {
    color: #dc3545;
}

.form-help {
    display: block;
    margin-top: 6px;
    font-size: 13px;
    color: #666;
}

.form-help a {
    color: #007bff;
    text-decoration: none;
}

.form-help a:hover {
    text-decoration: underline;
}

.info-box {
    padding: 16px;
    background: #e7f3ff;
    border-left: 4px solid #007bff;
    border-radius: 6px;
    margin-bottom: 20px;
}

.info-box ol {
    margin: 10px 0 0 20px;
}

.info-box li {
    margin-bottom: 6px;
}

.info-box a {
    color: #007bff;
    font-weight: 500;
    text-decoration: none;
}

.info-box a:hover {
    text-decoration: underline;
}

.form-actions {
    margin-top: 24px;
}

.info-table {
    width: 100%;
    border-collapse: collapse;
}

.info-table th,
.info-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
}

.info-table th {
    font-weight: 600;
    width: 40%;
}

.info-table code {
    background: #f0f0f0;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 13px;
}
</style>

<?php require_once __DIR__ . '/../layouts/admin-footer.php'; ?>
