<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="error-page">
    <div class="container">
        <div class="error-content">
            <div class="error-code">410</div>
            <h1 class="error-title">İçerik Kaldırıldı</h1>
            <p class="error-description">Aradığınız sayfa kalıcı olarak kaldırılmıştır ve artık mevcut değildir.</p>

            <div class="error-actions">
                <a href="<?= url('/') ?>" class="btn btn-primary btn-large">Ana Sayfaya Dön</a>
                <a href="<?= url('/firma-ekle') ?>" class="btn btn-secondary btn-large">Firma Ekle</a>
            </div>

            <div class="error-suggestions">
                <h3>Size yardımcı olabilecek diğer sayfalar</h3>
                <ul>
                    <li><a href="<?= url('/') ?>">Tüm illerdeki lastik tamircilerini keşfedin</a></li>
                    <li><a href="<?= url('/firma-ekle') ?>">Firmanızı sitemize ekleyin</a></li>
                    <li><a href="<?= url('/iletisim') ?>">Bizimle iletişime geçin</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.error-page {
    min-height: 60vh;
    display: flex;
    align-items: center;
    padding: 4rem 0;
}

.error-content {
    text-align: center;
    max-width: 600px;
    margin: 0 auto;
}

.error-code {
    font-size: 8rem;
    font-weight: 700;
    color: var(--primary-color);
    line-height: 1;
    margin-bottom: 1rem;
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.error-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: var(--text-primary);
}

.error-description {
    font-size: 1.25rem;
    color: var(--text-secondary);
    margin-bottom: 2rem;
}

.error-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-bottom: 3rem;
    flex-wrap: wrap;
}

.error-suggestions {
    background: var(--bg-gray);
    border-radius: 0.75rem;
    padding: 2rem;
    text-align: left;
}

.error-suggestions h3 {
    font-size: 1.25rem;
    margin-bottom: 1rem;
    color: var(--text-primary);
}

.error-suggestions ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.error-suggestions li {
    margin-bottom: 0.75rem;
}

.error-suggestions a {
    color: var(--primary-color);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
}

.error-suggestions a:hover {
    color: var(--accent-color);
    padding-left: 0.5rem;
}

.error-suggestions a::before {
    content: '→';
    font-weight: bold;
}

@media (max-width: 768px) {
    .error-code {
        font-size: 5rem;
    }

    .error-title {
        font-size: 2rem;
    }

    .error-description {
        font-size: 1rem;
    }

    .error-actions {
        flex-direction: column;
    }

    .error-actions .btn {
        width: 100%;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
