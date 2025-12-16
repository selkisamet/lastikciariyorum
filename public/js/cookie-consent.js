/**
 * KVKK/GDPR Uyumlu Çerez Onay Yöneticisi
 */
class CookieConsentManager {
    constructor() {
        this.consentCookieName = 'cookie_consent_id';
        this.consentDuration = 365; // 12 ay
        this.init();
    }

    init() {
        // Sayfa yüklendiğinde çerez onayı kontrolü
        if (!this.hasConsent()) {
            this.showBanner();
        }

        // Event listener'ları ekle
        this.attachEventListeners();
    }

    /**
     * Çerez onayı var mı kontrol eder
     */
    hasConsent() {
        return this.getCookie(this.consentCookieName) !== null;
    }

    /**
     * Banner'ı gösterir
     */
    showBanner() {
        setTimeout(() => {
            const banner = document.getElementById('cookieConsentBanner');
            if (banner) {
                banner.classList.add('show');
            }
        }, 500);
    }

    /**
     * Banner'ı gizler
     */
    hideBanner() {
        const banner = document.getElementById('cookieConsentBanner');
        if (banner) {
            banner.classList.remove('show');
        }
    }

    /**
     * Modal'ı gösterir
     */
    showModal() {
        const modal = document.getElementById('cookieSettingsModal');
        if (modal) {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    }

    /**
     * Modal'ı gizler
     */
    hideModal() {
        const modal = document.getElementById('cookieSettingsModal');
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    }

    /**
     * Tüm çerezleri kabul et
     */
    acceptAll() {
        const preferences = {
            necessary: true,
            analytics: true,
            marketing: true,
            preferences: true
        };

        this.saveConsent(preferences);
    }

    /**
     * Sadece zorunlu çerezleri kabul et
     */
    acceptNecessary() {
        const preferences = {
            necessary: true,
            analytics: false,
            marketing: false,
            preferences: false
        };

        this.saveConsent(preferences);
    }

    /**
     * Özel tercihleri kaydet
     */
    saveCustomPreferences() {
        const preferences = {
            necessary: true, // Her zaman true
            analytics: document.getElementById('analyticsConsent')?.checked || false,
            marketing: document.getElementById('marketingConsent')?.checked || false,
            preferences: document.getElementById('preferencesConsent')?.checked || false
        };

        this.saveConsent(preferences);
        this.hideModal();
    }

    /**
     * Çerez onayını sunucuya kaydeder
     */
    async saveConsent(preferences) {
        try {
            // Sunucuya kaydet
            const response = await fetch('/api/cookie-consent.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(preferences)
            });

            if (response.ok) {
                const data = await response.json();

                // Consent ID'yi çereze kaydet
                if (data.consent_id) {
                    this.setCookie(this.consentCookieName, data.consent_id, this.consentDuration);
                }

                // Tercihleri localStorage'a da kaydet (hızlı erişim için)
                localStorage.setItem('cookie_preferences', JSON.stringify(preferences));

                // Banner'ı gizle
                this.hideBanner();

                // Analitik scriptlerini yükle (eğer kabul edilmişse)
                if (preferences.analytics) {
                    this.loadAnalytics();
                }

                // Marketing scriptlerini yükle (eğer kabul edilmişse)
                if (preferences.marketing) {
                    this.loadMarketing();
                }
            }
        } catch (error) {
            console.error('Çerez onayı kaydedilemedi:', error);

            // Hata durumunda da yerel olarak kaydet
            this.setCookie(this.consentCookieName, 'local_' + Date.now(), this.consentDuration);
            localStorage.setItem('cookie_preferences', JSON.stringify(preferences));
            this.hideBanner();
        }
    }

    /**
     * Analitik scriptlerini yükler (örn. Google Analytics)
     */
    loadAnalytics() {
        // Örnek: Google Analytics
        // window.dataLayer = window.dataLayer || [];
        // function gtag(){dataLayer.push(arguments);}
        // gtag('js', new Date());
        // gtag('config', 'GA_MEASUREMENT_ID');
    }

    /**
     * Marketing scriptlerini yükler
     */
    loadMarketing() {
        // Örnek: Facebook Pixel, Google Ads, vb.
    }

    /**
     * Event listener'ları ekler
     */
    attachEventListeners() {
        // Tümünü kabul et
        const acceptAllBtn = document.getElementById('acceptAllCookies');
        if (acceptAllBtn) {
            acceptAllBtn.addEventListener('click', () => this.acceptAll());
        }

        // Sadece zorunlu çerezler
        const acceptNecessaryBtn = document.getElementById('acceptNecessaryCookies');
        if (acceptNecessaryBtn) {
            acceptNecessaryBtn.addEventListener('click', () => this.acceptNecessary());
        }

        // Ayarları göster
        const settingsBtn = document.getElementById('showCookieSettings');
        if (settingsBtn) {
            settingsBtn.addEventListener('click', () => this.showModal());
        }

        // Modal'ı kapat
        const closeModalBtn = document.getElementById('closeCookieSettings');
        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', () => this.hideModal());
        }

        // Tercihleri kaydet
        const savePreferencesBtn = document.getElementById('saveCookiePreferences');
        if (savePreferencesBtn) {
            savePreferencesBtn.addEventListener('click', () => this.saveCustomPreferences());
        }

        // Modal dışına tıklama
        const modal = document.getElementById('cookieSettingsModal');
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.hideModal();
                }
            });
        }
    }

    /**
     * Çerez okuma
     */
    getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) {
            return parts.pop().split(';').shift();
        }
        return null;
    }

    /**
     * Çerez yazma
     */
    setCookie(name, value, days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        const expires = `expires=${date.toUTCString()}`;
        document.cookie = `${name}=${value};${expires};path=/;SameSite=Lax`;
    }

    /**
     * Belirli bir çerez türü için onay kontrolü
     */
    hasConsentFor(type) {
        if (!this.hasConsent()) {
            return false;
        }

        const preferences = localStorage.getItem('cookie_preferences');
        if (preferences) {
            try {
                const prefs = JSON.parse(preferences);
                return prefs[type] === true;
            } catch (e) {
                return false;
            }
        }

        return false;
    }
}

// Sayfa yüklendiğinde başlat
document.addEventListener('DOMContentLoaded', () => {
    window.cookieConsent = new CookieConsentManager();
});

// Global helper fonksiyon
window.hasConsentFor = function(type) {
    return window.cookieConsent ? window.cookieConsent.hasConsentFor(type) : false;
};
