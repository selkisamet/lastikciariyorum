<?php $pageId = 'ai-articles'; ?>
<?php require_once __DIR__ . '/../layouts/admin-header.php'; ?>

<div class="admin-header">
    <h1>Makale Önizleme</h1>
    <div class="header-actions">
        <a href="<?= $this->getConfig('base_path') ?>/admin/ai-makale-uret" class="btn btn-secondary">← Geri Dön</a>
    </div>
</div>

<div class="preview-container">
    <div class="preview-card">
        <div class="preview-header">
            <span class="badge badge-info"><?= htmlspecialchars($city['name']) ?><?= $district ? ' / ' . htmlspecialchars($district['name']) : '' ?></span>
            <span class="badge badge-secondary">Anahtar Kelime: <?= htmlspecialchars($article['keyword']) ?></span>
        </div>

        <div class="preview-meta">
            <div class="meta-item">
                <strong>Meta Title:</strong>
                <div class="meta-preview"><?= htmlspecialchars($article['meta_title']) ?></div>
                <small class="char-count"><?= mb_strlen($article['meta_title']) ?> karakter</small>
            </div>

            <div class="meta-item">
                <strong>Meta Description:</strong>
                <div class="meta-preview"><?= htmlspecialchars($article['meta_description']) ?></div>
                <small class="char-count"><?= mb_strlen($article['meta_description']) ?> karakter</small>
            </div>

            <div class="meta-item">
                <strong>Excerpt:</strong>
                <div class="meta-preview"><?= htmlspecialchars($article['excerpt']) ?></div>
                <small class="char-count"><?= mb_strlen($article['excerpt']) ?> karakter</small>
            </div>

            <div class="meta-item">
                <strong>Kelime Sayısı:</strong>
                <div class="meta-preview">
                    <?php
                    // HTML etiketlerini temizle ve kelime sayısını hesapla
                    $plainText = strip_tags($article['content']);
                    $wordCount = str_word_count($plainText, 0, 'ÇçĞğİıÖöŞşÜü0123456789');
                    $badge = 'success';
                    if ($wordCount < 800) {
                        $badge = 'danger';
                    } elseif ($wordCount < 1300) {
                        $badge = 'warning';
                    }
                    ?>
                    <span class="badge badge-<?= $badge ?>"><?= number_format($wordCount) ?> kelime</span>
                </div>
            </div>

            <div class="meta-item">
                <strong>URL Slug:</strong>
                <div class="meta-preview">
                    <?php
                    $previewUrl = $this->getConfig('site_url') . '/' . $city['slug'];
                    if ($district) {
                        $previewUrl .= '/' . $district['slug'];
                    }
                    $previewUrl .= '/' . $article['slug'];
                    ?>
                    <code><?= htmlspecialchars($previewUrl) ?></code>
                </div>
            </div>
        </div>

        <div class="preview-content">
            <h1 class="preview-title"><?= htmlspecialchars($article['title']) ?></h1>
            <div class="preview-article-content">
                <?= $article['content'] ?>
            </div>
        </div>

        <div class="preview-actions">
            <form method="POST" action="<?= $this->getConfig('base_path') ?>/admin/ai-makale-kaydet" style="display: inline;">
                <button type="submit" class="btn btn-success btn-large">
                    ✓ Makaleyi Kaydet ve Yayınla
                </button>
            </form>
            <a href="<?= $this->getConfig('base_path') ?>/admin/ai-makale-uret" class="btn btn-secondary">
                ✗ İptal Et
            </a>
        </div>
    </div>
</div>

<style>
.preview-container {
    max-width: 900px;
    margin: 0 auto;
}

.preview-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
}

.preview-header {
    padding: 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    gap: 12px;
    align-items: center;
}

.preview-meta {
    padding: 20px;
    background: #f8f9fa;
    border-bottom: 2px solid #e0e0e0;
}

.meta-item {
    margin-bottom: 16px;
}

.meta-item:last-child {
    margin-bottom: 0;
}

.meta-item strong {
    display: block;
    margin-bottom: 6px;
    color: #333;
}

.meta-preview {
    padding: 10px;
    background: white;
    border-left: 3px solid #007bff;
    border-radius: 4px;
    margin-bottom: 4px;
}

.meta-preview code {
    background: #f0f0f0;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 13px;
}

.char-count {
    color: #666;
    font-size: 12px;
}

.preview-content {
    padding: 32px;
}

.preview-title {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 24px;
    line-height: 1.3;
    color: #222;
}

.preview-article-content {
    line-height: 1.8;
    color: #333;
}

.preview-article-content h2 {
    font-size: 24px;
    font-weight: 600;
    margin: 32px 0 16px 0;
    color: #222;
}

.preview-article-content h3 {
    font-size: 20px;
    font-weight: 600;
    margin: 24px 0 12px 0;
    color: #333;
}

.preview-article-content p {
    margin-bottom: 16px;
}

.preview-article-content ul, .preview-article-content ol {
    margin: 16px 0;
    padding-left: 32px;
}

.preview-article-content li {
    margin-bottom: 8px;
}

.preview-article-content strong {
    font-weight: 600;
    color: #222;
}

.preview-actions {
    padding: 24px;
    background: #f8f9fa;
    border-top: 2px solid #e0e0e0;
    text-align: center;
    display: flex;
    gap: 12px;
    justify-content: center;
}

.btn-large {
    padding: 14px 32px;
    font-size: 16px;
}

@media (max-width: 768px) {
    .preview-content {
        padding: 20px;
    }

    .preview-title {
        font-size: 24px;
    }

    .preview-actions {
        flex-direction: column;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/admin-footer.php'; ?>
