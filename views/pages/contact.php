<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="page-header">
    <div class="container">
        <h1 class="page-title">İletişim</h1>
        <p class="page-description">Bizimle iletişime geçin</p>
    </div>
</div>

<section class="content-section">
    <div class="container">
        <div class="contact-container">
            <div class="contact-info">
                <h2>İletişim Bilgileri</h2>
                <div class="info-item">
                    <div class="info-icon">📧</div>
                    <div class="info-content">
                        <h3>E-posta</h3>
                        <a href="mailto:info@lastikciariyorum.com">info@lastikciariyorum.com</a>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">🏢</div>
                    <div class="info-content">
                        <h3>Adres</h3>
                        <p>Sultanbeyli / İstanbul / Türkiye</p>
                    </div>
                </div>

                <div class="kvkk-notice">
                    <h3>Kişisel Verileriniz</h3>
                    <p>
                        İletişim formunu kullanarak paylaştığınız bilgiler KVKK kapsamında korunmaktadır.
                        Detaylı bilgi için <a href="<?= url('/kvkk-aydinlatma-metni') ?>">KVKK Aydınlatma Metni</a>'ni inceleyebilirsiniz.
                    </p>
                </div>
            </div>

            <div class="contact-form-wrapper">
                <h2>Mesaj Gönderin</h2>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($_SESSION['success']) ?>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <?= htmlspecialchars($_SESSION['error']) ?>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <form method="POST" action="<?= url('/iletisim') ?>" class="contact-form">
                    <div class="form-group">
                        <label for="name">Ad Soyad *</label>
                        <input type="text" id="name" name="name" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="email">E-posta *</label>
                        <input type="email" id="email" name="email" class="form-input" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Telefon</label>
                        <input type="tel" id="phone" name="phone" class="form-input" placeholder="0XXX XXX XX XX">
                    </div>

                    <div class="form-group">
                        <label for="subject">Konu *</label>
                        <select id="subject" name="subject" class="form-input" required>
                            <option value="">Seçiniz</option>
                            <option value="Genel Bilgi">Genel Bilgi</option>
                            <option value="Teknik Destek">Teknik Destek</option>
                            <option value="Firma Kaydı">Firma Kaydı</option>
                            <option value="KVKK / Veri Silme Talebi">KVKK / Veri Silme Talebi</option>
                            <option value="Şikayet">Şikayet</option>
                            <option value="Öneri">Öneri</option>
                            <option value="Diger">Diğer</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="message">Mesajınız *</label>
                        <textarea id="message" name="message" class="form-input" rows="6" required></textarea>
                    </div>

                    <div class="consent-section">
                        <label class="consent-label">
                            <input type="checkbox" name="contact_consent" required>
                            <span class="consent-text">
                                <a href="<?= url('/kvkk-aydinlatma-metni') ?>" target="_blank">KVKK Aydınlatma Metni</a>'ni okudum,
                                iletişim bilgilerimin işlenmesine onay veriyorum. *
                            </span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary">Gönder</button>
                </form>
            </div>
        </div>
    </div>
</section>

<style>
    .contact-container {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 3rem;
        margin-top: 2rem;
    }

    @media (max-width: 768px) {
        .contact-container {
            grid-template-columns: 1fr;
            gap: 2rem;
        }
    }

    .contact-info {
        background: #f8f9fa;
        padding: 2rem;
        border-radius: 0.5rem;
        height: fit-content;
    }

    .contact-info h2 {
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
        color: var(--text-primary);
    }

    .info-item {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid var(--border-color);
    }

    .info-item:last-of-type {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .info-icon {
        font-size: 2rem;
        flex-shrink: 0;
    }

    .info-content h3 {
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
        color: var(--text-primary);
    }

    .info-content p {
        margin: 0.25rem 0;
        color: var(--text-secondary);
    }

    .info-content a {
        color: var(--primary-color);
        text-decoration: none;
    }

    .info-content a:hover {
        text-decoration: underline;
    }

    .kvkk-notice {
        background: #e8f4fd;
        padding: 1.5rem;
        border-radius: 0.5rem;
        margin-top: 2rem;
    }

    .kvkk-notice h3 {
        font-size: 1rem;
        margin-bottom: 0.75rem;
        color: #1976D2;
    }

    .kvkk-notice p {
        margin: 0;
        font-size: 0.9rem;
        color: var(--text-secondary);
        line-height: 1.6;
    }

    .kvkk-notice a {
        color: var(--primary-color);
        text-decoration: underline;
    }

    .contact-form-wrapper {
        background: white;
        padding: 2rem;
        border-radius: 0.5rem;
        border: 1px solid var(--border-color);
    }

    .contact-form-wrapper h2 {
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
        color: var(--text-primary);
    }

    .contact-form .form-group {
        margin-bottom: 1.5rem;
    }

    .contact-form label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--text-primary);
    }

    .consent-section {
        margin: 1.5rem 0;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 0.5rem;
    }

    .consent-label {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        cursor: pointer;
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
        font-size: 0.9rem;
        line-height: 1.6;
        color: var(--text-primary);
    }

    .consent-text a {
        color: var(--primary-color);
        text-decoration: underline;
    }

    .alert {
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>