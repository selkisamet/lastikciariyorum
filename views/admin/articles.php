<?php $pageId = 'articles'; ?>
<?php require_once __DIR__ . '/../layouts/admin-header.php'; ?>

<div class="admin-header">
    <h1>Makale Yönetimi</h1>
    <a href="<?= $this->getConfig('base_path') ?>/admin/makale-ekle" class="btn btn-primary">+ Yeni Makale Ekle</a>
</div>

<div class="articles-controls">
    <div class="admin-search-box">
        <input type="text" id="searchInput" class="form-input" placeholder="Makale başlığı ile ara...">
    </div>

    <div class="articles-stats">
        <span class="stat-item">
            <strong>Toplam:</strong> <span id="totalCount"><?= count($articles) ?></span>
        </span>
        <span class="stat-item">
            <strong>Yayında:</strong>
            <span id="publishedCount" class="badge badge-success">
                <?= count(array_filter($articles, fn($a) => $a['is_published'] == 1)) ?>
            </span>
        </span>
        <span class="stat-item">
            <strong>Taslak:</strong>
            <span id="draftCount" class="badge badge-warning">
                <?= count(array_filter($articles, fn($a) => $a['is_published'] == 0)) ?>
            </span>
        </span>
    </div>
</div>

<?php if (!empty($articles)): ?>
    <div class="table-responsive">
        <table class="data-table" id="articlesTable">
            <thead>
                <tr>
                    <th>Başlık</th>
                    <th>İl / İlçe</th>
                    <th>Durum</th>
                    <th>Görüntülenme</th>
                    <th>Tarih</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articles as $article): ?>
                    <tr class="article-row" data-article-title="<?= strtolower(htmlspecialchars($article['title'])) ?>">
                        <td class="article-title">
                            <strong><?= htmlspecialchars($article['title']) ?></strong>
                        </td>
                        <td>
                            <?= htmlspecialchars($article['city_name']) ?>
                            <?= $article['district_name'] ? ' / ' . htmlspecialchars($article['district_name']) : '' ?>
                        </td>
                        <td>
                            <?php if ($article['is_published']): ?>
                                <span class="status-badge status-published">Yayında</span>
                            <?php else: ?>
                                <span class="status-badge status-draft">Taslak</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="view-count">👁 <?= number_format($article['view_count']) ?></span>
                        </td>
                        <td>
                            <?php
                            $date = new DateTime($article['created_at']);
                            echo $date->format('d.m.Y');
                            ?>
                        </td>
                        <td class="action-buttons">
                            <?php
                            // Generate canonical URL for article
                            // HUB articles (slug=NULL): /istanbul/ or /istanbul/sultanbeyli/
                            // Normal articles: /istanbul/sultanbeyli/makale-slug
                            $base = $this->getConfig('base_path');
                            if (is_null($article['slug']) || $article['slug'] === '') {
                                // HUB article - canonical URL without slug
                                $viewUrl = $base . '/' . $article['city_slug'];
                                if ($article['district_slug']) {
                                    $viewUrl .= '/' . $article['district_slug'];
                                }
                            } else {
                                // Normal article - include slug
                                $viewUrl = $base . '/' . $article['city_slug'];
                                if ($article['district_slug']) {
                                    $viewUrl .= '/' . $article['district_slug'];
                                }
                                $viewUrl .= '/' . $article['slug'];
                            }
                            ?>
                            <a href="<?= $viewUrl ?>"
                               class="btn btn-small btn-info btn-icon" title="Görüntüle" target="_blank">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </a>
                            <a href="<?= $this->getConfig('base_path') ?>/admin/makale-duzenle/<?= $article['id'] ?>"
                               class="btn btn-small btn-primary btn-icon" title="Düzenle">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                </svg>
                            </a>
                            <form method="POST" action="<?= $this->getConfig('base_path') ?>/admin/makale-sil/<?= $article['id'] ?>" style="display: inline;" class="delete-form">
                                <button type="submit" class="btn btn-small btn-danger btn-icon" title="Sil">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        <line x1="10" y1="11" x2="10" y2="17"></line>
                                        <line x1="14" y1="11" x2="14" y2="17"></line>
                                    </svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="table-info">
        <p id="noResults" style="display: none; text-align: center; padding: 2rem; color: var(--text-secondary);">
            Arama kriterlerine uygun makale bulunamadı.
        </p>
    </div>
<?php else: ?>
    <div class="empty-state">
        <p>Henüz hiç makale eklenmemiş.</p>
        <a href="<?= $this->getConfig('base_path') ?>/admin/makale-ekle" class="btn btn-primary">İlk Makaleyi Ekle</a>
    </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gerçek zamanlı arama
    const searchInput = document.getElementById('searchInput');
    const articlesTable = document.getElementById('articlesTable');
    const articleRows = document.querySelectorAll('.article-row');
    const noResults = document.getElementById('noResults');
    const totalCount = document.getElementById('totalCount');

    if (searchInput && articlesTable) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            let visibleCount = 0;

            articleRows.forEach(row => {
                const articleTitle = row.getAttribute('data-article-title');

                if (articleTitle.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Sonuç sayısını güncelle
            totalCount.textContent = visibleCount;

            // Sonuç bulunamadı mesajını göster/gizle
            if (visibleCount === 0) {
                articlesTable.style.display = 'none';
                noResults.style.display = 'block';
            } else {
                articlesTable.style.display = 'table';
                noResults.style.display = 'none';
            }
        });
    }

    // Silme işlemini modern modal ile doğrula
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formElement = this;

            if (window.modal) {
                window.modal.confirm(
                    'Bu makaleyi silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!',
                    function() {
                        // Onaylandı, formu gönder
                        formElement.submit();
                    },
                    null,
                    'Makale Silme Onayı'
                );
            }
        });
    });
});
</script>

<style>
.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.admin-header h1 {
    margin: 0;
}

.articles-controls {
    display: flex;
    justify-content: flex-start;
    align-items: center;
    gap: 2rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.admin-search-box {
    flex: 0 0 auto;
    width: 400px;
    max-width: 400px;
    margin-right: auto;
}

.admin-search-box input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid var(--border-color);
    border-radius: 0.5rem;
    font-size: 0.95rem;
    transition: all 0.2s ease;
    text-align: left;
}

.admin-search-box input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(51, 102, 204, 0.1);
}

.articles-stats {
    display: flex;
    gap: 1.5rem;
    align-items: center;
    flex-wrap: wrap;
}

.stat-item {
    font-size: 0.9rem;
    color: var(--text-secondary);
}

.stat-item strong {
    color: var(--text-primary);
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge-warning {
    background: #fff3cd;
    color: #856404;
}

.badge-success {
    background: #d4edda;
    color: #155724;
}

.status-badge {
    display: inline-block;
    padding: 0.4rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.85rem;
    font-weight: 600;
    text-align: center;
}

.status-published {
    background: #d4edda;
    color: #155724;
}

.status-draft {
    background: #fff3cd;
    color: #856404;
}

.article-title {
    font-size: 1rem;
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.article-title strong {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.action-buttons {
    white-space: nowrap;
}

.action-buttons .btn-small {
    padding: 0.4rem 0.75rem;
    font-size: 0.875rem;
    margin-right: 0.5rem;
}

.action-buttons .btn-small:last-child {
    margin-right: 0;
}

.btn-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem !important;
    width: 32px;
    height: 32px;
}

.btn-icon svg {
    display: block;
    color: white;
    margin: 0;
}

.btn-info {
    background-color: #17a2b8;
    color: white;
}

.btn-info:hover {
    background-color: #138496;
}

.view-count {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.table-info {
    margin-top: 1rem;
}

@media (max-width: 768px) {
    .articles-controls {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }

    .admin-search-box {
        flex: 1 1 auto;
        max-width: 100%;
    }

    .articles-stats {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }

    .admin-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .admin-header .btn {
        width: 100%;
    }

    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .action-buttons .btn-small {
        margin-right: 0;
        width: 100%;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/admin-footer.php'; ?>
