<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-header">
    <div class="container">
        <h1 class="page-title">Hesap ve Firma Bilgileri</h1>
        <p class="page-description">Kullanıcı hesabınızı ve firma bilgilerinizi güncelleyin</p>
    </div>
</div>

<section class="content-section">
    <div class="container">
        <div class="form-container">
            <form method="POST" action="<?= url('/firma-duzenle/' . $company['id']) ?>" class="standard-form" enctype="multipart/form-data">

                <div class="form-section-title">
                    <h3>👤 Kullanıcı Bilgileri</h3>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="user_email">E-posta *</label>
                        <input type="email" id="user_email" name="user_email" class="form-input"
                            value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="full_name">Tam Ad *</label>
                        <input type="text" id="full_name" name="full_name" class="form-input"
                            value="<?= htmlspecialchars($user['full_name']) ?>" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="new_password">Yeni Şifre (Opsiyonel)</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="new_password" name="new_password" class="form-input" minlength="6"
                                placeholder="Değiştirmek istemiyorsanız boş bırakın">
                            <button type="button" class="password-toggle" onclick="togglePassword('new_password', 'eyeIcon1')">
                                <svg id="eyeIcon1" class="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                        <input type="text" id="company_name" name="name" class="form-input"
                            value="<?= htmlspecialchars($company['name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="city_id">İl *</label>
                        <select id="city_id" name="city_id" class="form-input" required>
                            <option value="">Seçiniz</option>
                            <?php foreach ($cities as $city): ?>
                                <option value="<?= $city['id'] ?>" <?= $city['id'] == $company['city_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($city['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="district_id">İlçe *</label>
                        <select id="district_id" name="district_id" class="form-input" required>
                            <option value="">Yükleniyor...</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="phone">Telefon</label>
                        <input type="tel" id="phone" name="phone" class="form-input"
                            placeholder="0XXX XXX XX XX" maxlength="14"
                            value="<?= htmlspecialchars($company['phone'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">E-posta</label>
                        <input type="email" id="email" name="email" class="form-input"
                            value="<?= htmlspecialchars($company['email'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="logo">Firma Logosu (Opsiyonel)</label>
                        <?php if ($company['logo']): ?>
                            <div class="current-logo">
                                <img src="<?= asset('uploads/logos/' . $company['logo']) ?>" alt="Mevcut Logo" class="logo-thumbnail">
                                <p>Mevcut logo - Yeni logo yüklerseniz değiştirilecek</p>
                            </div>
                        <?php endif; ?>
                        <input type="file" id="logo" name="logo" class="form-input" accept="image/*">
                        <small>JPG, PNG veya GIF formatında, maksimum 2MB</small>
                    </div>

                    <div class="form-group">
                        <label for="website">Website</label>
                        <input type="url" id="website" name="website" class="form-input"
                            placeholder="https://..."
                            value="<?= htmlspecialchars($company['website'] ?? '') ?>">
                    </div>

                    <div class="form-group full-width">
                        <label for="address">Adres</label>
                        <textarea id="address" name="address" class="form-input" rows="3"><?= htmlspecialchars($company['address'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="description">Açıklama</label>
                        <textarea id="description" name="description" class="form-input" rows="5"
                            placeholder="Firmanız hakkında bilgi verin..."><?= htmlspecialchars($company['description'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Değişiklikleri Kaydet</button>
                    <a href="<?= url('/firma-paneli') ?>" class="btn btn-secondary">İptal</a>
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
                    const selected = district.id == <?= $company['district_id'] ?? 'null' ?> ? 'selected' : '';
                    districtSelect.innerHTML += `<option value="${district.id}" ${selected}>${district.name}</option>`;
                });
            })
            .catch(error => {
                console.error('Error:', error);
                districtSelect.innerHTML = '<option value="">Hata oluştu</option>';
            });
    });

    // Sayfa yüklendiğinde ilçeleri getir
    window.addEventListener('DOMContentLoaded', function() {
        const cityId = document.getElementById('city_id').value;
        if (cityId) {
            // Trigger change event to load districts
            document.getElementById('city_id').dispatchEvent(new Event('change'));
        }
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
        margin: 0 0 1.5rem 0;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--border-color);
    }

    .form-section-title h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text-primary);
    }

    .current-logo {
        margin-bottom: 1rem;
        padding: 1rem;
        background: var(--bg-gray);
        border-radius: 0.5rem;
    }

    .logo-thumbnail {
        max-width: 150px;
        max-height: 150px;
        border-radius: 0.5rem;
        border: 1px solid var(--border-color);
        object-fit: contain;
        margin-bottom: 0.5rem;
    }

    .current-logo p {
        font-size: 0.875rem;
        color: var(--text-secondary);
        margin: 0;
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
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>