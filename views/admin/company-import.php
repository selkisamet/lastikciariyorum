<?php $pageId = 'companies'; ?>
<?php require_once __DIR__ . '/../layouts/admin-header.php'; ?>

<div class="admin-header">
    <h1>Excel ile Firma Ekle</h1>
    <a href="<?= $this->getConfig('base_path') ?>/admin/firmalar" class="btn btn-secondary">← Firma Listesine Dön</a>
</div>

<div class="import-container">
    <div class="import-card">
        <h2>Excel Dosyası Yükle</h2>
        <p class="text-muted">Excel dosyası kullanarak toplu firma ekleme işlemi yapabilirsiniz.</p>

        <div class="info-box info-primary">
            <h3>📋 Excel Dosyası Formatı</h3>
            <p>Excel dosyanızın ilk satırında aşağıdaki sütun başlıklarını kullanın:</p>
            <table class="example-table">
                <thead>
                    <tr>
                        <th>name</th>
                        <th>city_id</th>
                        <th>district_id</th>
                        <th>phone</th>
                        <th>address</th>
                        <th>website</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>ABC Lastik Tamiri</td>
                        <td>İstanbul</td>
                        <td>Kadıköy</td>
                        <td>0216 555 1234</td>
                        <td>Caferağa Mah. Örnek Sok. No:1</td>
                        <td>www.abclastik.com</td>
                    </tr>
                    <tr>
                        <td>XYZ Oto Lastik</td>
                        <td>İstanbul</td>
                        <td>Beşiktaş</td>
                        <td>0212 555 5678</td>
                        <td>Levent Mah. Lastik Cad. No:5</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>123 Lastik Servisi</td>
                        <td>Ankara</td>
                        <td>Çankaya</td>
                        <td>0312 555 9876</td>
                        <td>Kızılay Mah. Atatürk Blv. No:10</td>
                        <td>www.123lastik.com</td>
                    </tr>
                </tbody>
            </table>

            <div class="mt-2">
                <a href="<?= $this->getConfig('base_path') ?>/admin/firma-import-ornek-indir" class="btn btn-success">
                    📥 Örnek Excel Dosyasını İndir
                </a>
            </div>
        </div>

        <div class="info-box info-warning">
            <h3>⚠️ Önemli Notlar</h3>
            <ul>
                <li><strong>İlk satır:</strong> Sütun başlıkları olmalı (<code>name</code>, <code>city_id</code>, <code>district_id</code>, <code>phone</code>, <code>address</code>, <code>website</code>)</li>
                <li><strong>İkinci satırdan itibaren:</strong> Firma verileri</li>
                <li><strong>Zorunlu sütunlar:</strong> <code>name</code> (Firma Adı), <code>city_id</code> (İl Adı), <code>district_id</code> (İlçe Adı)</li>
                <li><strong>İsteğe bağlı sütunlar:</strong> <code>phone</code> (Telefon), <code>address</code> (Adres), <code>website</code> (Web Sitesi)</li>
                <li><strong>İl ve İlçe:</strong> Veritabanında kayıtlı olan tam adları kullanın (örn: "İstanbul", "Kadıköy")</li>
                <li><strong>Dosya formatı:</strong> .xlsx veya .xls</li>
                <li><strong>Maksimum dosya boyutu:</strong> 5 MB</li>
                <li><strong>Boş satırlar:</strong> Otomatik olarak atlanır</li>
            </ul>
        </div>

        <form action="<?= $this->getConfig('base_path') ?>/admin/firma-import-process" method="POST" enctype="multipart/form-data" class="import-form">
            <div class="form-group">
                <label for="excel_file" class="form-label">Excel Dosyası Seçin</label>
                <input type="file"
                    id="excel_file"
                    name="excel_file"
                    accept=".xlsx,.xls"
                    required
                    class="form-input">
                <small class="text-muted">Sadece .xlsx veya .xls formatında dosyalar kabul edilir.</small>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="auto_approve" value="1" checked>
                    <span>Firmaları otomatik olarak onayla</span>
                </label>
                <small class="text-muted">İşaretlenirse, eklenen firmalar onay beklemeden yayına alınır.</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-large">
                    Excel Dosyasını Yükle ve İşle
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .import-container {
        max-width: 900px;
        margin: 0 auto;
    }

    .import-card {
        background: white;
        border-radius: 0.5rem;
        padding: 2rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .import-card h2 {
        margin-top: 0;
        margin-bottom: 0.5rem;
        color: var(--text-primary);
    }

    .text-muted {
        color: var(--text-secondary);
        font-size: 0.9rem;
        margin-bottom: 1.5rem;
    }

    .info-box {
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border-left: 4px solid;
    }

    .info-box h3 {
        margin-top: 0;
        margin-bottom: 0.75rem;
        font-size: 1rem;
    }

    .info-box ul {
        margin: 0;
        padding-left: 1.5rem;
    }

    .info-box li {
        margin-bottom: 0.5rem;
        line-height: 1.6;
    }

    .info-box code {
        background: rgba(0, 0, 0, 0.05);
        padding: 0.2rem 0.4rem;
        border-radius: 0.25rem;
        font-family: monospace;
        font-size: 0.9em;
    }

    .info-warning {
        background: #fff3cd;
        border-left-color: #ffc107;
    }

    .info-primary {
        background: #d1ecf1;
        border-left-color: #17a2b8;
    }

    .info-success {
        background: #d4edda;
        border-left-color: #28a745;
    }

    .example-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
        font-size: 0.85rem;
    }

    .example-table th,
    .example-table td {
        padding: 0.5rem;
        border: 1px solid #dee2e6;
        text-align: left;
    }

    .example-table th {
        background: #f8f9fa;
        font-weight: 600;
    }

    .import-form {
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 2px solid #e9ecef;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    .form-input {
        display: block;
        width: 100%;
        padding: 0.75rem;
        border: 2px solid #e9ecef;
        border-radius: 0.5rem;
        font-size: 1rem;
        transition: all 0.2s ease;
    }

    .form-input:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(51, 102, 204, 0.1);
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        font-weight: 500;
    }

    .checkbox-label input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .form-actions {
        margin-top: 2rem;
    }

    .btn-large {
        padding: 1rem 2rem;
        font-size: 1.1rem;
        width: 100%;
    }

    .mt-2 {
        margin-top: 1rem;
    }

    @media (max-width: 768px) {
        .import-card {
            padding: 1rem;
        }

        .info-box {
            padding: 1rem;
        }

        .example-table {
            font-size: 0.75rem;
        }

        .example-table th,
        .example-table td {
            padding: 0.25rem;
        }
    }
</style>

<?php require_once __DIR__ . '/../layouts/admin-footer.php'; ?>