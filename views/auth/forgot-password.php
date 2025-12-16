<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="auth-container">
    <div class="auth-card">
        <h1 class="auth-title">Şifremi Unuttum</h1>
        <p class="auth-description">E-posta adresinize şifre sıfırlama bağlantısı göndereceğiz.</p>

        <form method="POST" action="<?= $this->getConfig('base_path') ?>/forgot-password" class="auth-form">
            <div class="form-group">
                <label for="email">E-posta</label>
                <input type="email" id="email" name="email" class="form-input" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-block">Gönder</button>
            </div>

            <div class="form-links">
                <a href="<?= $this->getConfig('base_path') ?>/login">Giriş sayfasına dön</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
