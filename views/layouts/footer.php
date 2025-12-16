    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Lastikciariyorum.com</h3>
                    <p>Türkiye'nin en kapsamlı lastik tamir rehberi</p>
                </div>

                <div class="footer-section">
                    <h4>Hızlı Linkler</h4>
                    <ul>
                        <li><a href="<?= url('/') ?>">Ana Sayfa</a></li>
                        <li><a href="<?= url('/hakkimizda') ?>">Hakkımızda</a></li>
                        <li><a href="<?= url('/iletisim') ?>">İletişim</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>Firmalar İçin</h4>
                    <ul>
                        <li><a href="<?= url('/firma-ekle') ?>">Firma Ekle</a></li>
                        <li><a href="<?= url('/firma-paneli') ?>">Firma Paneli</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Lastikciariyorum.com - Tüm hakları saklıdır.</p>
                <div class="footer-links">
                    <a href="<?= url('/kvkk-aydinlatma-metni') ?>">KVKK Aydınlatma Metni</a>
                    <span>|</span>
                    <a href="<?= url('/cerez-politikasi') ?>">Çerez Politikası</a>
                    <span>|</span>
                    <a href="<?= url('/gizlilik-politikasi') ?>">Gizlilik Politikası</a>
                    <span>|</span>
                    <a href="#" id="manageCookies">Çerez Ayarları</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Çerez Onay Banner -->
    <div id="cookieConsentBanner" class="cookie-consent-banner">
        <div class="cookie-consent-container">
            <div class="cookie-consent-content">
                <div class="cookie-consent-text">
                    <h3>🍪 Çerez Kullanımı</h3>
                    <p>
                        Web sitemizde deneyiminizi geliştirmek için çerezler kullanıyoruz.
                        <a href="<?= url('/cerez-politikasi') ?>">Çerez Politikamızı</a> inceleyebilirsiniz.
                    </p>
                </div>
                <div class="cookie-consent-actions">
                    <button id="acceptAllCookies" class="cookie-consent-btn cookie-consent-btn-primary">
                        Tümünü Kabul Et
                    </button>
                    <button id="acceptNecessaryCookies" class="cookie-consent-btn cookie-consent-btn-secondary">
                        Sadece Gerekli
                    </button>
                    <button id="showCookieSettings" class="cookie-consent-btn cookie-consent-btn-settings">
                        Ayarlar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Çerez Ayarları Modal -->
    <div id="cookieSettingsModal" class="cookie-settings-modal">
        <div class="cookie-settings-content">
            <div class="cookie-settings-header">
                <h2>Çerez Tercihleri</h2>
                <button id="closeCookieSettings" class="cookie-settings-close" aria-label="Kapat">×</button>
            </div>
            <div class="cookie-settings-body">
                <div class="cookie-category">
                    <div class="cookie-category-header">
                        <h3>Zorunlu Çerezler</h3>
                        <label class="toggle-switch">
                            <input type="checkbox" checked disabled>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <p class="cookie-category-description">
                        Bu çerezler web sitesinin çalışması için zorunludur ve kapatılamaz.
                        Genellikle oturum yönetimi ve güvenlik için kullanılır.
                    </p>
                </div>

                <div class="cookie-category">
                    <div class="cookie-category-header">
                        <h3>Analitik Çerezler</h3>
                        <label class="toggle-switch">
                            <input type="checkbox" id="analyticsConsent">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <p class="cookie-category-description">
                        Web sitesinin nasıl kullanıldığını anlamamıza yardımcı olur.
                        Ziyaretçi istatistikleri ve sayfa performansı için kullanılır.
                    </p>
                </div>

                <div class="cookie-category">
                    <div class="cookie-category-header">
                        <h3>Pazarlama Çerezleri</h3>
                        <label class="toggle-switch">
                            <input type="checkbox" id="marketingConsent">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <p class="cookie-category-description">
                        Size daha ilgili içerik göstermek için kullanılır.
                        Üçüncü taraf reklam platformları ile paylaşılabilir.
                    </p>
                </div>

                <div class="cookie-category">
                    <div class="cookie-category-header">
                        <h3>Tercih Çerezleri</h3>
                        <label class="toggle-switch">
                            <input type="checkbox" id="preferencesConsent">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <p class="cookie-category-description">
                        Tercihlerinizi (dil, tema vb.) hatırlamak için kullanılır.
                    </p>
                </div>
            </div>
            <div class="cookie-settings-footer">
                <button id="saveCookiePreferences" class="cookie-consent-btn cookie-consent-btn-primary">
                    Tercihleri Kaydet
                </button>
            </div>
        </div>
    </div>

    <script src="<?= asset('js/main.js') ?>"></script>
    <script src="<?= asset('js/cookie-consent.js') ?>"></script>

    <script>
    // Çerez ayarlarını footer'dan aç
    document.getElementById('manageCookies')?.addEventListener('click', function(e) {
        e.preventDefault();
        if (window.cookieConsent) {
            window.cookieConsent.showModal();
        }
    });
    </script>
</body>
</html>
