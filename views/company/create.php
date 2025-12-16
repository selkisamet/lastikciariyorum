<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-header">
    <div class="container">
        <h1 class="page-title">Firma Ekle - Kayıt Ol</h1>
        <p class="page-description">Lastik tamirhane bilgilerinizi girerek sisteme kayıt olun</p>
    </div>
</div>

<section class="content-section">
    <div class="container">
        <div class="form-container">
            <form method="POST" action="<?= url('firma-ekle') ?>" class="standard-form" enctype="multipart/form-data">

                <div class="form-section-title">
                    <h3>📧 Kullanıcı Bilgileri</h3>
                </div>

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="full_name">Ad Soyad *</label>
                        <input type="text" id="full_name" name="full_name" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="email">E-posta *</label>
                        <input type="email" id="email" name="email" class="form-input" required>
                        <small>Bu e-posta ile giriş yapacaksınız</small>
                    </div>

                    <div class="form-group">
                        <label for="password">Şifre *</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="password" name="password" class="form-input" required minlength="6">
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <svg id="eyeIcon" class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                        <small>En az 6 karakter olmalıdır</small>
                    </div>
                </div>

                <div class="form-section-title">
                    <h3>🏢 Firma Bilgileri</h3>
                </div>

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="company_name">Firma Adı *</label>
                        <input type="text" id="company_name" name="company_name" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="city_id">İl *</label>
                        <select id="city_id" name="city_id" class="form-input" required>
                            <option value="">Seçiniz</option>
                            <?php foreach ($cities as $city): ?>
                                <option value="<?= $city['id'] ?>"><?= htmlspecialchars($city['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="district_id">İlçe *</label>
                        <select id="district_id" name="district_id" class="form-input" required>
                            <option value="">Önce il seçiniz</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="phone">Telefon</label>
                        <input type="tel" id="phone" name="phone" class="form-input" placeholder="0XXX XXX XX XX" maxlength="14">
                    </div>

                    <div class="form-group">
                        <label for="website">Website</label>
                        <input type="url" id="website" name="website" class="form-input" placeholder="https://...">
                    </div>

                    <div class="form-group full-width">
                        <label for="logo">Firma Logosu (Opsiyonel)</label>
                        <input type="file" id="logo" name="logo" class="form-input" accept="image/*">
                        <small>JPG, PNG veya GIF formatında, maksimum 2MB</small>
                    </div>

                    <div class="form-group full-width">
                        <label for="address">Adres</label>
                        <textarea id="address" name="address" class="form-input" rows="3"></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="description">Açıklama</label>
                        <textarea id="description" name="description" class="form-input" rows="5" placeholder="Firmanız hakkında bilgi verin..."></textarea>
                    </div>
                </div>

                <div class="form-notice">
                    <p><strong>ℹ️ Önemli Bilgi:</strong></p>
                    <ul>
                        <li>Kaydınız admin onayından sonra aktif olacaktır</li>
                        <li>Onay işlemi 1-2 iş günü içinde tamamlanır</li>
                        <li>Onaylandıktan sonra e-posta adresiniz ile giriş yapabilirsiniz</li>
                    </ul>
                </div>

                <div class="form-section-title">
                    <h3>📋 Kişisel Verilerin Korunması ve İzinler</h3>
                </div>

                <div class="consent-section">
                    <div class="consent-item required">
                        <label class="consent-label">
                            <input type="checkbox" name="kvkk_consent" id="kvkk_consent" required>
                            <span class="consent-text">
                                <a href="<?= url('/kvkk-aydinlatma-metni') ?>" target="_blank" class="consent-link">KVKK Aydınlatma Metni</a>'ni okudum,
                                anladım ve kişisel verilerimin işlenmesine <strong>açık rıza</strong> veriyorum. <span class="required-badge">*</span>
                            </span>
                        </label>
                        <small class="consent-description">
                            Ad, soyad, e-posta, telefon, adres ve firma bilgilerinizin işlenmesi için zorunludur.
                        </small>
                    </div>

                    <div class="consent-item">
                        <label class="consent-label">
                            <input type="checkbox" name="marketing_consent" id="marketing_consent">
                            <span class="consent-text">
                                Kampanya, duyuru ve bilgilendirme amaçlı ticari elektronik ileti almak istiyorum.
                            </span>
                        </label>
                        <small class="consent-description">
                            Bu izin opsiyoneldir. İstediğiniz zaman geri çekebilirsiniz.
                        </small>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Firma Ekle ve Kayıt Ol</button>
                    <a href="<?= url('/') ?>" class="btn btn-secondary">İptal</a>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
    // İl seçilince ilçeleri getir
    document.getElementById('city_id').addEventListener('change', function() {
        const cityId = this.value;
        const districtSelect = document.getElementById('district_id');

        districtSelect.innerHTML = '<option value="">Yükleniyor...</option>';

        if (!cityId) {
            districtSelect.innerHTML = '<option value="">Önce il seçiniz</option>';
            return;
        }

        fetch(`<?= url('api/districts') ?>/${cityId}`)
            .then(response => response.json())
            .then(data => {
                districtSelect.innerHTML = '<option value="">Seçiniz</option>';
                data.forEach(district => {
                    districtSelect.innerHTML += `<option value="${district.id}">${district.name}</option>`;
                });
            })
            .catch(error => {
                console.error('Error:', error);
                districtSelect.innerHTML = '<option value="">Hata oluştu</option>';
            });
    });

    // Şifre göster/gizle
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            // Göz kapalı ikonu (eye-off)
            eyeIcon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
        } else {
            passwordInput.type = 'password';
            // Göz açık ikonu (eye)
            eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
        }
    }

    // Telefon numarası maskeleme
    document.getElementById('phone').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, ''); // Sadece rakamlar

        // 0 ile başlamazsa başına 0 ekle
        if (value.length > 0 && value[0] !== '0') {
            value = '0' + value;
        }

        // Maksimum 11 rakam (0XXX XXX XX XX)
        value = value.substring(0, 11);

        // Formatlama: 0XXX XXX XX XX
        let formatted = '';
        if (value.length > 0) {
            formatted = value[0]; // 0
            if (value.length > 1) {
                formatted += value.substring(1, 4); // XXX
            }
            if (value.length > 4) {
                formatted += ' ' + value.substring(4, 7); // XXX
            }
            if (value.length > 7) {
                formatted += ' ' + value.substring(7, 9); // XX
            }
            if (value.length > 9) {
                formatted += ' ' + value.substring(9, 11); // XX
            }
        }

        e.target.value = formatted;
    });
</script>

<style>
    .form-section-title {
        margin: 2rem 0 1.5rem 0;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--border-color);
    }

    .form-section-title h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    .form-notice {
        background: #f0f9ff;
        border-left: 4px solid var(--primary-color);
        padding: 1.5rem;
        margin: 2rem 0;
        border-radius: 0.5rem;
    }

    .form-notice p {
        margin-bottom: 0.75rem;
        color: var(--text-primary);
    }

    .form-notice ul {
        margin-left: 1.5rem;
        color: var(--text-secondary);
    }

    .form-notice li {
        margin-bottom: 0.5rem;
    }

    small {
        display: block;
        margin-top: 0.25rem;
        font-size: 0.875rem;
        color: var(--text-secondary);
    }

    .password-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .password-input-wrapper .form-input {
        padding-right: 3rem;
        flex: 1;
    }

    .password-toggle {
        position: absolute;
        right: 0.75rem;
        background: none;
        border: none;
        cursor: pointer;
        padding: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: opacity 0.2s;
    }

    .password-toggle:hover {
        opacity: 0.7;
    }

    .eye-icon {
        width: 20px;
        height: 20px;
        color: #666;
        transition: color 0.2s;
    }

    .password-toggle:hover .eye-icon {
        color: #333;
    }

    /* KVKK Consent Styles */
    .consent-section {
        background: #f8f9fa;
        border: 2px solid var(--border-color);
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin: 1.5rem 0;
    }

    .consent-item {
        margin-bottom: 1.5rem;
    }

    .consent-item:last-child {
        margin-bottom: 0;
    }

    .consent-item.required {
        padding-bottom: 1.5rem;
        border-bottom: 1px solid var(--border-color);
    }

    .consent-label {
        display: flex;
        align-items: flex-start;
        cursor: pointer;
        gap: 0.75rem;
    }

    .consent-label input[type="checkbox"] {
        margin-top: 0.25rem;
        width: 18px;
        height: 18px;
        cursor: pointer;
        flex-shrink: 0;
    }

    .consent-text {
        flex: 1;
        font-size: 0.95rem;
        line-height: 1.6;
        color: var(--text-primary);
    }

    .consent-link {
        color: var(--primary-color);
        text-decoration: underline;
        font-weight: 600;
    }

    .consent-link:hover {
        color: #1976D2;
    }

    .required-badge {
        color: #dc3545;
        font-weight: bold;
        font-size: 1.1rem;
    }

    .consent-description {
        display: block;
        margin-top: 0.5rem;
        margin-left: 1.75rem;
        font-size: 0.85rem;
        color: var(--text-secondary);
        line-height: 1.4;
    }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>