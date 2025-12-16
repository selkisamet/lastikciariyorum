<?php $pageId = 'companies'; ?>
<?php require_once __DIR__ . '/../layouts/admin-header.php'; ?>

<div class="admin-header">
            <h1>Yeni Firma Ekle</h1>
            <p>Admin olarak sistem üzerinden firma ekleyebilirsiniz. Kullanıcı bilgileri opsiyoneldir.</p>
        </div>

        <div class="form-container">
            <form method="POST" action="<?= url('/admin/firma-ekle') ?>" class="standard-form" enctype="multipart/form-data">

                <div class="form-section-title">
                    <h3>🏢 Firma Bilgileri</h3>
                </div>

                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="company_name">Firma Adı *</label>
                        <input type="text" id="company_name" name="name" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="city_id">İl *</label>
                        <select id="city_id" name="city_id" class="form-input" required>
                            <option value="">Seçiniz</option>
                            <?php foreach ($cities as $city): ?>
                                <option value="<?= $city['id'] ?>">
                                    <?= htmlspecialchars($city['name']) ?>
                                </option>
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
                        <input type="tel" id="phone" name="phone" class="form-input"
                            placeholder="0XXX XXX XX XX" maxlength="14">
                    </div>

                    <div class="form-group">
                        <label for="email">Firma E-posta</label>
                        <input type="email" id="email" name="email" class="form-input">
                    </div>

                    <div class="form-group">
                        <label for="logo">Firma Logosu (Opsiyonel)</label>
                        <input type="file" id="logo" name="logo" class="form-input" accept="image/*">
                        <small>JPG, PNG veya GIF formatında, maksimum 2MB</small>
                    </div>

                    <div class="form-group">
                        <label for="website">Website</label>
                        <input type="url" id="website" name="website" class="form-input"
                            placeholder="https://...">
                    </div>

                    <div class="form-group full-width">
                        <label for="address">Adres</label>
                        <textarea id="address" name="address" class="form-input" rows="3"></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="description">Açıklama</label>
                        <textarea id="description" name="description" class="form-input" rows="5"
                            placeholder="Firma hakkında bilgi verin..."></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="is_approved">
                            <input type="checkbox" id="is_approved" name="is_approved" value="1" checked>
                            Firmayı onaylı olarak ekle
                        </label>
                    </div>
                </div>

                <div class="form-section-title">
                    <h3>👤 Kullanıcı Bilgileri (Opsiyonel)</h3>
                    <p style="font-size: 0.9rem; font-weight: normal; color: var(--text-secondary); margin-top: 0.5rem;">
                        Bu firmaya ait bir kullanıcı hesabı oluşturmak istiyorsanız aşağıdaki bilgileri doldurun.
                    </p>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="user_email">Kullanıcı E-posta</label>
                        <input type="email" id="user_email" name="user_email" class="form-input">
                    </div>

                    <div class="form-group">
                        <label for="full_name">Tam Ad</label>
                        <input type="text" id="full_name" name="full_name" class="form-input">
                    </div>

                    <div class="form-group full-width">
                        <label for="password">Şifre</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="password" name="password" class="form-input" minlength="6"
                                placeholder="Kullanıcı oluşturmak için şifre girin">
                            <button type="button" class="password-toggle" onclick="togglePassword('password', 'eyeIcon1')">
                                <svg id="eyeIcon1" class="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                        <small>En az 6 karakter olmalıdır. Boş bırakırsanız kullanıcı oluşturulmaz.</small>
                    </div>

                    <div class="form-group full-width">
                        <label for="user_is_active">
                            <input type="checkbox" id="user_is_active" name="user_is_active" value="1" checked>
                            Kullanıcıyı aktif olarak oluştur
                        </label>
                        <small>İşaretli ise kullanıcı hemen giriş yapabilir.</small>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Firmayı Kaydet</button>
                    <a href="<?= url('/admin/firmalar') ?>" class="btn btn-secondary">İptal</a>
                </div>
            </form>
        </div>
    </main>
</div>

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

    // Kullanıcı bilgileri validasyonu
    document.querySelector('form').addEventListener('submit', function(e) {
        const userEmail = document.getElementById('user_email').value.trim();
        const fullName = document.getElementById('full_name').value.trim();
        const password = document.getElementById('password').value.trim();

        // Eğer kullanıcı bilgilerinden biri girilmişse diğerleri de gerekli
        if (userEmail || fullName || password) {
            if (!userEmail || !fullName || !password) {
                e.preventDefault();
                alert('Kullanıcı oluşturmak için E-posta, Tam Ad ve Şifre alanlarını doldurmanız gerekiyor.');
                return false;
            }
        }
    });
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
    }
</style>

<?php require_once __DIR__ . '/../layouts/admin-footer.php'; ?>