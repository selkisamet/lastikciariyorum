<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="auth-container">
    <div class="auth-card">
        <h1 class="auth-title">Kayıt Ol</h1>

        <form method="POST" action="<?= $this->getConfig('base_path') ?>/register" class="auth-form">
            <div class="form-group">
                <label for="full_name">Ad Soyad</label>
                <input type="text" id="full_name" name="full_name" class="form-input" required>
            </div>

            <div class="form-group">
                <label for="email">E-posta</label>
                <input type="email" id="email" name="email" class="form-input" required>
            </div>

            <div class="form-group">
                <label for="password">Şifre</label>
                <input type="password" id="password" name="password" class="form-input" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Şifre Tekrar</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-block">Kayıt Ol</button>
            </div>

            <div class="form-links">
                <a href="<?= $this->getConfig('base_path') ?>/login">Zaten hesabınız var mı? Giriş yapın</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
