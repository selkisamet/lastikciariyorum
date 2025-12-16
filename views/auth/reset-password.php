<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="auth-container">
    <div class="auth-card">
        <h1 class="auth-title">Şifre Sıfırla</h1>

        <form method="POST" action="<?= $this->getConfig('base_path') ?>/reset-password" class="auth-form">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <div class="form-group">
                <label for="password">Yeni Şifre</label>
                <input type="password" id="password" name="password" class="form-input" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Yeni Şifre Tekrar</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-block">Şifreyi Sıfırla</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
