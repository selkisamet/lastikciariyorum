<?php $pageId = 'users'; ?>
<?php require_once __DIR__ . '/../layouts/admin-header.php'; ?>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- Select2 JS (jQuery gerekli) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<div class="admin-header">
    <h1>Yeni Kullanıcı Ekle</h1>
    <p>Sistem üzerinden kullanıcı oluşturabilir ve opsiyonel olarak bir firmaya atayabilirsiniz.</p>
</div>

<div class="form-container">
    <form method="POST" action="<?= url('/admin/kullanici-ekle') ?>" class="standard-form">

        <div class="form-section-title">
            <h3>👤 Kullanıcı Bilgileri</h3>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="user_email">E-posta *</label>
                <input type="email" id="user_email" name="user_email" class="form-input" required>
            </div>

            <div class="form-group">
                <label for="full_name">Tam Ad *</label>
                <input type="text" id="full_name" name="full_name" class="form-input" required>
            </div>

            <div class="form-group full-width">
                <label for="password">Şifre *</label>
                <div class="password-input-wrapper">
                    <input type="password" id="password" name="password" class="form-input" minlength="6" required
                        placeholder="En az 6 karakter">
                    <button type="button" class="password-toggle" onclick="togglePassword('password', 'eyeIcon1')">
                        <svg id="eyeIcon1" class="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
                <small>En az 6 karakter olmalıdır.</small>
            </div>

            <div class="form-group full-width">
                <label for="user_is_active">
                    <input type="checkbox" id="user_is_active" name="user_is_active" value="1" checked>
                    Kullanıcıyı aktif olarak oluştur
                </label>
                <small>İşaretli ise kullanıcı hemen giriş yapabilir.</small>
            </div>
        </div>

        <div class="form-section-title">
            <h3>🏢 Firma Atama (Opsiyonel)</h3>
            <p style="font-size: 0.9rem; font-weight: normal; color: var(--text-secondary); margin-top: 0.5rem;">
                Bu kullanıcıyı mevcut bir firmaya atayabilirsiniz. Boş bırakırsanız kullanıcı firma bilgisi olmadan oluşturulur.
            </p>
        </div>

        <div class="form-grid">
            <div class="form-group full-width">
                <label for="company_id">Firma Seç</label>
                <select id="company_id" name="company_id" class="form-input">
                    <option value="">Firma seçmeyin (kullanıcı bir firmaya atanmayacak)</option>
                    <?php foreach ($companies as $company): ?>
                        <option value="<?= $company['id'] ?>">
                            <?= htmlspecialchars($company['name']) ?>
                            <?php if ($company['city_name']): ?>
                                - <?= htmlspecialchars($company['city_name']) ?>
                                <?php if ($company['district_name']): ?>
                                    / <?= htmlspecialchars($company['district_name']) ?>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if ($company['user_id']): ?>
                                <span style="color: #dc3545;">(Zaten kullanıcı var)</span>
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small>Firma seçerseniz, seçilen firmaya bu kullanıcı atanır.</small>
            </div>

            <div class="form-group full-width" id="warning-box" style="display: none;">
                <div class="alert alert-warning">
                    ⚠️ <strong>Uyarı:</strong> Seçtiğiniz firmanın zaten bir kullanıcısı var. Bu firmayı seçerseniz, mevcut kullanıcı değiştirilecektir.
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">💾 Kullanıcıyı Kaydet</button>
            <a href="<?= url('/admin/kullanicilar') ?>" class="btn btn-secondary">İptal</a>
        </div>
    </form>
</div>

<script>
    $(document).ready(function() {
        // Select2 ile firma seçim kutusunu arama özellikli yap
        $('#company_id').select2({
            placeholder: 'Firma seçmeyin (kullanıcı bir firmaya atanmayacak)',
            allowClear: true,
            width: '100%',
            language: {
                noResults: function() {
                    return "Sonuç bulunamadı";
                },
                searching: function() {
                    return "Aranıyor...";
                },
                inputTooShort: function() {
                    return "Lütfen daha fazla karakter girin";
                }
            },
            // Arama fonksiyonunu özelleştir
            matcher: function(params, data) {
                // Arama terimi yoksa tüm sonuçları göster
                if ($.trim(params.term) === '') {
                    return data;
                }

                // Boş seçenek ise gösterme
                if (data.id === '') {
                    return null;
                }

                // Küçük harfe çevirerek Türkçe karakterlere duyarlı arama yap
                var searchTerm = params.term.toLowerCase()
                    .replace(/ı/g, 'i')
                    .replace(/ğ/g, 'g')
                    .replace(/ü/g, 'u')
                    .replace(/ş/g, 's')
                    .replace(/ö/g, 'o')
                    .replace(/ç/g, 'c');

                var text = data.text.toLowerCase()
                    .replace(/ı/g, 'i')
                    .replace(/ğ/g, 'g')
                    .replace(/ü/g, 'u')
                    .replace(/ş/g, 's')
                    .replace(/ö/g, 'o')
                    .replace(/ç/g, 'c');

                // Eğer arama terimi metinde varsa göster
                if (text.indexOf(searchTerm) > -1) {
                    return data;
                }

                return null;
            }
        });

        // Firma seçildiğinde uyarı göster
        $('#company_id').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const warningBox = $('#warning-box');

            // Seçilen firmada zaten kullanıcı var mı kontrol et
            if (selectedOption.text().includes('(Zaten kullanıcı var)')) {
                warningBox.show();
            } else {
                warningBox.hide();
            }
        });
    });

    // Şifre göster/gizle
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);

        if (input.type === 'password') {
            input.type = 'text';
            icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
        } else {
            input.type = 'password';
            icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
        }
    }
</script>

<style>
    .form-section-title {
        margin: 2rem 0 1.5rem 0;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--border-color);
    }

    .form-section-title:first-child {
        margin-top: 0;
    }

    .form-section-title h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    .password-input-wrapper {
        position: relative;
        width: 100%;
    }

    .password-input-wrapper input {
        width: 100%;
        padding-right: 3rem;
    }

    .password-toggle {
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        padding: 0.5rem 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-secondary);
        transition: color 0.2s ease;
        height: 100%;
    }

    .password-toggle:hover {
        color: var(--primary-color);
    }

    .eye-icon {
        width: 20px;
        height: 20px;
    }

    .form-group label input[type="checkbox"] {
        margin-right: 0.5rem;
    }

    .alert {
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
        border: 1px solid transparent;
    }

    .alert-warning {
        background-color: #fff3cd;
        border-color: #ffc107;
        color: #856404;
    }

    /* Select2 özel stilleri */
    .select2-container--default .select2-selection--single {
        height: 42px;
        border: 1px solid var(--border-color);
        border-radius: 0.375rem;
        padding: 0.5rem;
        display: flex;
        align-items: center;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 30px;
        padding-left: 0;
        color: var(--text-primary);
    }

    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: var(--text-secondary);
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
        right: 5px;
    }

    .select2-dropdown {
        border: 1px solid var(--border-color);
        border-radius: 0.375rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid var(--border-color);
        border-radius: 0.375rem;
        padding: 0.5rem;
        font-size: 0.95rem;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: var(--primary-color);
        color: white;
    }

    .select2-container--default .select2-results__option[aria-selected="true"] {
        background-color: #e8f4fd;
        color: var(--text-primary);
    }

    .select2-container--default .select2-selection--single .select2-selection__clear {
        font-size: 1.2rem;
        color: var(--text-secondary);
        margin-right: 10px;
    }

    .select2-results__option {
        padding: 8px 12px;
        font-size: 0.95rem;
    }

    @media (max-width: 768px) {
        .form-section-title {
            margin: 1.5rem 0 1rem 0;
        }

        .form-section-title h3 {
            font-size: 1.1rem;
        }

        .form-section-title p {
            font-size: 0.85rem;
        }

        .password-input-wrapper input {
            font-size: 16px;
        }

        /* Mobilde Select2 arama kutusunu daha büyük yap */
        .select2-container--default .select2-search--dropdown .select2-search__field {
            font-size: 16px;
            padding: 0.75rem;
        }

        .select2-results__option {
            padding: 10px 12px;
            font-size: 14px;
        }
    }
</style>

<?php require_once __DIR__ . '/../layouts/admin-footer.php'; ?>