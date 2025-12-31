<?php $pageId = 'ai-articles'; ?>
<?php require_once __DIR__ . '/../layouts/admin-header.php'; ?>

<div class="admin-header">
    <h1>AI Makale Üretici</h1>
    <a href="<?= $this->getConfig('base_path') ?>/admin/ai-ayarlar" class="btn btn-secondary">⚙️ AI Ayarları</a>
</div>

<?php if (!$apiKeyConfigured): ?>
    <div class="alert alert-warning">
        <strong>Uyarı:</strong> AI makale üretmek için önce Anthropic API key'inizi yapılandırmalısınız.
        <a href="<?= $this->getConfig('base_path') ?>/admin/ai-ayarlar" class="btn btn-sm btn-primary" style="margin-left: 10px;">API Ayarlarına Git</a>
    </div>
<?php else: ?>

    <div class="ai-generator-container">
        <!-- Tab Navigation -->
        <div class="tab-navigation">
            <button class="tab-btn active" data-tab="single">Tekli Üretim</button>
            <button class="tab-btn" data-tab="bulk">Toplu Üretim</button>
        </div>

        <!-- Single Generation Tab -->
        <div id="single-tab" class="tab-content active">
            <div class="card">
                <div class="card-header">
                    <h3>Tek Makale Üret</h3>
                    <p>Bir il veya ilçe için AI destekli SEO-optimized makale oluşturun</p>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= $this->getConfig('base_path') ?>/admin/ai-makale-uret" id="singleGenerationForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="city_id">İl <span class="required">*</span></label>
                                <select name="city_id" id="city_id" class="form-input" required>
                                    <option value="">Seçiniz...</option>
                                    <?php foreach ($cities as $city): ?>
                                        <option value="<?= $city['id'] ?>"><?= htmlspecialchars($city['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="district_id">İlçe (Opsiyonel)</label>
                                <select name="district_id" id="district_id" class="form-input">
                                    <option value="">İl seviyesi makale</option>
                                </select>
                                <small class="form-help">Boş bırakırsanız il seviyesi makale oluşturulur</small>
                            </div>
                        </div>

                        <!-- Ana Anahtar Kelime -->
                        <div class="form-group">
                            <label for="primary_keyword">Ana Anahtar Kelime <span class="required">*</span></label>
                            <input type="text" name="primary_keyword" id="primary_keyword" class="form-input" placeholder="Örn: lastikçi" required>
                            <small class="form-help">H1, URL ve ilk paragrafta mutlaka kullanılacak. Lokasyon adı otomatik eklenecektir. Örn: "lastikçi" → "Sultanbeyli Lastikçi"</small>
                        </div>

                        <!-- Diğer Anahtar Kelimeler -->
                        <div class="form-group">
                            <label for="keywords_manual">Diğer Anahtar Kelimeler (Her satıra bir tane - Opsiyonel)</label>
                            <div class="keyword-actions">
                                <button type="button" id="generateKeywords" class="btn btn-sm btn-secondary">
                                    ✨ Otomatik Keyword Önerileri Al
                                </button>
                                <span id="keywordLoading" style="display: none; margin-left: 10px;">
                                    <span class="spinner"></span> Öneriler alınıyor...
                                </span>
                            </div>
                            <textarea name="keywords_manual" id="keywords_manual" class="form-input" rows="8" placeholder="7/24 lastikçi&#10;mobil lastikçi&#10;açık lastikçi&#10;lastik tamiri&#10;lastik değişimi&#10;lastik patlaması&#10;oto lastik&#10;araç lastik&#10;lastik bakımı"></textarea>
                            <small class="form-help">Lokasyon adı otomatik eklenecektir. Boş bırakırsanız sadece ana anahtar kelime kullanılır.</small>
                        </div>
                        <input type="hidden" name="keyword_option" value="manual">

                        <!-- Word Count -->
                        <div class="form-group">
                            <label for="word_count">Hedef Kelime Sayısı</label>
                            <select name="word_count" id="word_count" class="form-input">
                                <option value="800">800-1000 kelime (Kısa)</option>
                                <option value="1500" selected>1500-2000 kelime (Orta - HUB Önerilen)</option>
                                <option value="2500">2500-3000 kelime (Uzun)</option>
                            </select>
                            <small class="form-help">HUB mimarisi için 1500+ kelime önerilir (SEO value)</small>
                        </div>

                        <!-- Auto Publish -->
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="auto_publish">
                                <strong>Otomatik Yayınla</strong> - Makaleyi hemen yayına al
                            </label>
                            <small class="form-help">İşaretlenmezse taslak olarak kaydedilir</small>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-large">
                                <span class="btn-icon">✨</span> Makale Üret ve Önizle
                            </button>
                        </div>

                        <div class="info-box">
                            <strong>📝 Oluşturulacak İçerikler:</strong>
                            <ul>
                                <li>Makale Başlığı (SEO-optimized)</li>
                                <li>HTML Formatında İçerik (H2, H3 başlıklarıyla)</li>
                                <li>Meta Title ve Description</li>
                                <li>Kısa Özet (Excerpt)</li>
                            </ul>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bulk Generation Tab -->
        <div id="bulk-tab" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h3>Toplu Makale Üret</h3>
                    <p>Birden fazla il/ilçe için otomatik makale oluşturun</p>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= $this->getConfig('base_path') ?>/admin/ai-makale-toplu-uret" id="bulkGenerationForm">

                        <div class="form-group">
                            <label>İl Seçimi <span class="required">*</span></label>
                            <div class="checkbox-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" id="select_all_cities"> <strong>Tümünü Seç</strong>
                                </label>
                            </div>
                            <div class="checkbox-grid">
                                <?php foreach ($cities as $city): ?>
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="city_ids[]" value="<?= $city['id'] ?>" class="city-checkbox">
                                        <?= htmlspecialchars($city['name']) ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>İlçe Seçeneği <span class="required">*</span></label>
                            <div class="radio-group">
                                <label class="radio-label">
                                    <input type="radio" name="district_option" value="none" checked>
                                    <strong>Sadece İl Seviyesi</strong> - İlçe makalesi üretme, sadece il makalesi
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="district_option" value="all">
                                    <strong>Tüm İlçeler</strong> - Seçili illerin tüm ilçeleri için makale üret
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="district_option" value="selected">
                                    <strong>Belirli İlçeler</strong> - Manuel seçim yap (gelişmiş)
                                </label>
                            </div>
                        </div>

                        <!-- Ana Anahtar Kelime -->
                        <div class="form-group">
                            <label for="bulk_primary_keyword">Ana Anahtar Kelime <span class="required">*</span></label>
                            <input type="text" name="primary_keyword" id="bulk_primary_keyword" class="form-input"
                                placeholder="Örn: lastikçi" required>
                            <small class="form-help">H1, URL ve ilk paragrafta mutlaka kullanılacak. Lokasyon adı otomatik eklenecektir.</small>
                        </div>

                        <!-- Word Count -->
                        <div class="form-group">
                            <label for="bulk_word_count">Hedef Kelime Sayısı</label>
                            <select name="word_count" id="bulk_word_count" class="form-input">
                                <option value="800">800-1000 kelime (Kısa)</option>
                                <option value="1500" selected>1500-2000 kelime (Orta - HUB Önerilen)</option>
                                <option value="2500">2500-3000 kelime (Uzun)</option>
                            </select>
                            <small class="form-help">HUB mimarisi için 1500+ kelime önerilir (SEO value)</small>
                        </div>

                        <!-- Diğer Anahtar Kelimeler -->
                        <div class="form-group">
                            <label for="bulk_keywords_manual">Diğer Anahtar Kelimeler (Her satıra bir tane - Opsiyonel)</label>
                            <div class="keyword-actions">
                                <button type="button" id="generateKeywordsBulk" class="btn btn-sm btn-secondary">
                                    ✨ Otomatik Keyword Önerileri Al
                                </button>
                                <span id="keywordLoadingBulk" style="display: none; margin-left: 10px;">
                                    <span class="spinner"></span> Öneriler alınıyor...
                                </span>
                            </div>
                            <textarea name="keywords_manual" id="bulk_keywords_manual" class="form-input" rows="8"
                                placeholder="7/24 lastikçi&#10;mobil lastikçi&#10;açık lastikçi&#10;lastik tamiri&#10;lastik değişimi&#10;lastik patlaması&#10;oto lastik&#10;araç lastik&#10;lastik bakımı"></textarea>
                            <small class="form-help">Lokasyon adı otomatik eklenecektir. Boş bırakırsanız sadece ana anahtar kelime kullanılır.</small>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="auto_publish">
                                <strong>Otomatik Yayınla</strong> - Oluşturulan makaleleri hemen yayına al
                            </label>
                            <small class="form-help">İşaretlenmezse taslak olarak kaydedilir</small>
                        </div>

                        <div class="warning-box">
                            <strong>⚠️ Dikkat:</strong> Toplu üretim uzun sürebilir ve API maliyeti oluşturur.
                            <br>Ortalama süre: ~30 saniye/makale
                            <br>API maliyeti: ~$0.02-0.08/makale (800 kelime için)
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-success btn-large" id="bulkGenerateBtn">
                                <span class="btn-icon">🚀</span> Toplu Üretimi Başlat
                            </button>
                        </div>

                        <div class="estimation-box" id="estimationBox" style="display: none;">
                            <h4>Tahmin:</h4>
                            <p>
                                <strong id="estimatedArticles">0</strong> makale üretilecek<br>
                                Tahmini süre: <strong id="estimatedTime">0</strong> dakika<br>
                                Tahmini maliyet: <strong id="estimatedCost">$0</strong>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .ai-generator-container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .tab-navigation {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }

        .tab-btn {
            padding: 12px 24px;
            background: none;
            border: none;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }

        .tab-btn:hover {
            background: #f5f5f5;
        }

        .tab-btn.active {
            border-bottom-color: #007bff;
            color: #007bff;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
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

        .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 12px;
            margin-top: 12px;
            padding: 16px;
            background: #f8f9fa;
            border-radius: 6px;
            max-height: 300px;
            overflow-y: auto;
        }

        .checkbox-label,
        .radio-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            padding: 6px;
        }

        .checkbox-label input,
        .radio-label input {
            cursor: pointer;
        }

        .radio-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 16px;
            background: #f8f9fa;
            border-radius: 6px;
        }

        .radio-option {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            cursor: pointer;
            padding: 12px;
            background: white;
            border-radius: 4px;
            border: 2px solid transparent;
            transition: all 0.2s;
        }

        .radio-option:hover {
            border-color: #007bff;
            background: #f0f8ff;
        }

        .radio-option input[type="radio"] {
            margin-top: 4px;
        }

        .radio-label {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .radio-label strong {
            display: block;
            margin-bottom: 4px;
        }

        .radio-label small {
            color: #666;
            font-size: 13px;
            line-height: 1.4;
        }

        .info-box,
        .warning-box,
        .estimation-box {
            padding: 16px;
            border-radius: 6px;
            margin-top: 20px;
        }

        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #007bff;
        }

        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
        }

        .estimation-box {
            background: #d4edda;
            border-left: 4px solid #28a745;
        }

        .info-box ul {
            margin: 10px 0 0 20px;
        }

        .form-actions {
            margin-top: 24px;
            text-align: center;
        }

        .btn-large {
            padding: 14px 32px;
            font-size: 16px;
        }

        .btn-icon {
            margin-right: 8px;
        }

        .keyword-actions {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            gap: 10px;
        }

        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .checkbox-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <script>
        // Tab switching
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const targetTab = btn.dataset.tab;

                // Update buttons
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                // Update content
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });
                document.getElementById(targetTab + '-tab').classList.add('active');
            });
        });

        // City selection for district loading
        document.getElementById('city_id').addEventListener('change', function() {
            const cityId = this.value;
            const districtSelect = document.getElementById('district_id');

            districtSelect.innerHTML = '<option value="">Yükleniyor...</option>';

            if (!cityId) {
                districtSelect.innerHTML = '<option value="">İl seviyesi makale</option>';
                return;
            }

            fetch(`<?= $this->getConfig('base_path') ?>/api/districts/${cityId}`)
                .then(response => response.json())
                .then(data => {
                    districtSelect.innerHTML = '<option value="">İl seviyesi makale</option>';
                    data.forEach(district => {
                        const option = document.createElement('option');
                        option.value = district.id;
                        option.textContent = district.name;
                        districtSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error loading districts:', error);
                    districtSelect.innerHTML = '<option value="">İl seviyesi makale</option>';
                });
        });

        // Select all cities
        document.getElementById('select_all_cities').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.city-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateEstimation();
        });

        // Update estimation
        function updateEstimation() {
            const selectedCities = document.querySelectorAll('.city-checkbox:checked').length;
            const districtOption = document.querySelector('input[name="district_option"]:checked').value;

            let estimatedArticles = 0;

            if (districtOption === 'none') {
                estimatedArticles = selectedCities;
            } else if (districtOption === 'all') {
                // Rough estimate: average 30 districts per city (İstanbul has 39, smaller cities have ~10)
                estimatedArticles = selectedCities * 25;
            }

            if (estimatedArticles > 0) {
                document.getElementById('estimationBox').style.display = 'block';
                document.getElementById('estimatedArticles').textContent = estimatedArticles;
                document.getElementById('estimatedTime').textContent = Math.ceil(estimatedArticles * 0.5); // 30 sec per article
                document.getElementById('estimatedCost').textContent = (estimatedArticles * 0.05).toFixed(2);
            } else {
                document.getElementById('estimationBox').style.display = 'none';
            }
        }

        document.querySelectorAll('.city-checkbox').forEach(cb => {
            cb.addEventListener('change', updateEstimation);
        });

        document.querySelectorAll('input[name="district_option"]').forEach(radio => {
            radio.addEventListener('change', updateEstimation);
        });

        // Form submission confirmation
        document.getElementById('bulkGenerationForm').addEventListener('submit', function(e) {
            const selectedCities = document.querySelectorAll('.city-checkbox:checked').length;

            if (selectedCities === 0) {
                e.preventDefault();
                alert('Lütfen en az bir il seçin.');
                return;
            }

            const estimatedArticles = parseInt(document.getElementById('estimatedArticles').textContent);

            if (estimatedArticles > 100) {
                if (!confirm(`${estimatedArticles} makale oluşturulacak. Bu işlem uzun sürebilir. Devam etmek istiyor musunuz?`)) {
                    e.preventDefault();
                }
            }
        });

        // Keyword auto-suggestion
        document.getElementById('generateKeywords').addEventListener('click', function() {
            const cityId = document.getElementById('city_id').value;
            const districtId = document.getElementById('district_id').value;
            const primaryKeyword = document.getElementById('primary_keyword').value.trim();
            const loadingSpan = document.getElementById('keywordLoading');
            const keywordsTextarea = document.getElementById('keywords_manual');
            const button = this;

            if (!cityId) {
                alert('Lütfen önce bir il seçin.');
                return;
            }

            if (!primaryKeyword) {
                alert('Lütfen önce Ana Anahtar Kelime girin.');
                return;
            }

            // Show loading state
            button.disabled = true;
            loadingSpan.style.display = 'inline';

            // Build URL with parameters
            const params = new URLSearchParams({
                city_id: cityId,
                primary_keyword: primaryKeyword
            });
            if (districtId) {
                params.append('district_id', districtId);
            }

            fetch(`<?= $this->getConfig('base_path') ?>/admin/generate-keyword-suggestions?${params}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.keywords) {
                        keywordsTextarea.value = data.keywords.join('\n');
                    } else {
                        alert('Keyword önerileri alınamadı: ' + (data.error || 'Bilinmeyen hata'));
                    }
                })
                .catch(error => {
                    console.error('Error generating keywords:', error);
                    alert('Keyword önerileri alınırken bir hata oluştu.');
                })
                .finally(() => {
                    button.disabled = false;
                    loadingSpan.style.display = 'none';
                });
        });

        // Keyword auto-suggestion for BULK generation
        document.getElementById('generateKeywordsBulk').addEventListener('click', function() {
            const primaryKeyword = document.getElementById('bulk_primary_keyword').value.trim();
            const selectedCities = document.querySelectorAll('.city-checkbox:checked');
            const loadingSpan = document.getElementById('keywordLoadingBulk');
            const keywordsTextarea = document.getElementById('bulk_keywords_manual');
            const button = this;

            if (selectedCities.length === 0) {
                alert('Lütfen önce en az bir il seçin.');
                return;
            }

            if (!primaryKeyword) {
                alert('Lütfen önce Ana Anahtar Kelime girin.');
                return;
            }

            // Use first selected city for keyword suggestions (location doesn't matter much)
            const firstCityId = selectedCities[0].value;

            // Show loading state
            button.disabled = true;
            loadingSpan.style.display = 'inline';

            // Build URL with parameters
            const params = new URLSearchParams({
                city_id: firstCityId,
                primary_keyword: primaryKeyword
            });

            fetch(`<?= $this->getConfig('base_path') ?>/admin/generate-keyword-suggestions?${params}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.keywords) {
                        keywordsTextarea.value = data.keywords.join('\n');
                    } else {
                        alert('Keyword önerileri alınamadı: ' + (data.error || 'Bilinmeyen hata'));
                    }
                })
                .catch(error => {
                    console.error('Error generating keywords:', error);
                    alert('Keyword önerileri alınırken bir hata oluştu.');
                })
                .finally(() => {
                    button.disabled = false;
                    loadingSpan.style.display = 'none';
                });
        });
    </script>

<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/admin-footer.php'; ?>