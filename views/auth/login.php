<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="auth-container">
    <div class="auth-card">
        <h1 class="auth-title">Giriş Yap</h1>

        <form method="POST" action="<?= $this->getConfig('base_path') ?>/login" class="auth-form">
            <div class="form-group">
                <label for="email">E-posta</label>
                <input type="email" id="email" name="email" class="form-input" required>
            </div>

            <div class="form-group">
                <label for="password">Şifre</label>
                <input type="password" id="password" name="password" class="form-input" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-block">Giriş Yap</button>
            </div>

            <div class="form-links">
                <a href="<?= $this->getConfig('base_path') ?>/forgot-password">Şifremi Unuttum</a>
                <a href="<?= $this->getConfig('base_path') ?>/firma-ekle">Firma Ekle</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>