<?php $pageId = 'district-edit'; ?>
<?php require_once __DIR__ . '/../layouts/admin-header.php'; ?>

<div class="admin-header">
    <h1>İlçe Düzenle: <?= htmlspecialchars($district['name']) ?> (<?= htmlspecialchars($city['name']) ?>)</h1>
</div>

<div class="form-container">
    <form method="POST" action="<?= $this->getConfig('base_path') ?>/admin/ilce-duzenle/<?= $district['id'] ?>" class="standard-form">
        <div class="form-grid">
            <!-- H1 Custom Header -->
            <div class="form-group full-width">
                <label for="h1">H1 Başlık</label>
                <input type="text" id="h1" name="h1" class="form-input"
                    value="<?= htmlspecialchars($district['h1'] ?? '') ?>"
                    placeholder="Örn: <?= htmlspecialchars($district['name']) ?> Lastik Tamircileri">
                <small>Sayfa başlığı olarak görünecek H1 etiketi (boş bırakılırsa otomatik oluşturulur)</small>
            </div>

            <!-- SEO Meta Title -->
            <div class="form-group full-width">
                <label for="meta_title">SEO Başlık (Meta Title)</label>
                <input type="text" id="meta_title" name="meta_title" class="form-input"
                    value="<?= htmlspecialchars($district['meta_title'] ?? '') ?>">
                <small>Arama motorlarında görünecek başlık (max 60 karakter)</small>
            </div>

            <!-- SEO Meta Description -->
            <div class="form-group full-width">
                <label for="meta_description">SEO Açıklama (Meta Description)</label>
                <textarea id="meta_description" name="meta_description" class="form-input" rows="2"><?= htmlspecialchars($district['meta_description'] ?? '') ?></textarea>
                <small>Arama motorlarında görünecek açıklama (max 160 karakter)</small>
            </div>

            <!-- Page Description -->
            <div class="form-group full-width">
                <label for="page_description">Sayfa Açıklaması</label>
                <textarea id="page_description" name="page_description" class="form-input" rows="2"><?= htmlspecialchars($district['page_description'] ?? '') ?></textarea>
                <small>Sayfa başlığı altında görünecek açıklama (meta description'dan farklı olabilir)</small>
            </div>

            <!-- Main Content (600-900 words) -->
            <div class="form-group full-width">
                <label for="content">Ana İçerik (600-900 kelime önerilir)</label>
                <textarea id="content" name="content" class="form-input" rows="10"><?= htmlspecialchars($district['content'] ?? '') ?></textarea>
                <small>HUB sayfasının ana içeriği. H2 bölümlerinden ÖNCE görünecek.</small>
            </div>

            <!-- H2 Sections (Dynamic) -->
            <div class="form-group full-width">
                <label>H2 İçerik Bölümleri</label>
                <p class="field-description">Her H2 bölümü, ilgili makaleye bağlamsal link sağlar. H2 başlıkları detay sayfa H1'leriyle birebir aynı olmamalıdır.</p>

                <div id="h2-sections-container">
                    <?php
                    $h2Sections = $district['h2_sections'] ?? [];
                    if (empty($h2Sections)) {
                        // Show one empty section as template
                        $h2Sections = [['title' => '', 'description' => '', 'linked_article_id' => null]];
                    }
                    foreach ($h2Sections as $index => $section):
                    ?>
                        <div class="h2-section-item" data-index="<?= $index ?>">
                            <div class="h2-section-header">
                                <h4>H2 Bölüm #<?= $index + 1 ?></h4>
                                <?php if (count($h2Sections) > 1 || !empty($section['title'])): ?>
                                    <button type="button" class="btn btn-danger btn-small remove-h2-section">Sil</button>
                                <?php endif; ?>
                            </div>
                            <div class="form-grid">
                                <div class="form-group full-width">
                                    <label>H2 Başlık *</label>
                                    <input type="text" name="h2_titles[]" class="form-input"
                                        value="<?= htmlspecialchars($section['title'] ?? '') ?>"
                                        placeholder="Örn: <?= htmlspecialchars($district['name']) ?>'de Mobil Lastikçi">
                                    <small>Detay sayfa H1'inden farklı olmalı</small>
                                </div>
                                <div class="form-group full-width">
                                    <label>Açıklama (1-2 cümle) *</label>
                                    <textarea name="h2_descriptions[]" class="form-input" rows="2"
                                        placeholder="Bu bölüm hakkında kısa açıklama (max 500 karakter)"><?= htmlspecialchars($section['description'] ?? '') ?></textarea>
                                </div>
                                <div class="form-group full-width">
                                    <label>Bağlantılı Makale (İsteğe Bağlı)</label>
                                    <select name="h2_article_ids[]" class="form-input">
                                        <option value="">Makale Seçiniz</option>
                                        <?php foreach ($articles as $article): ?>
                                            <option value="<?= $article['id'] ?>"
                                                <?= ($section['linked_article_id'] ?? null) == $article['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($article['title']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small>Bu H2 bölümünden link verilecek makale</small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="add-h2-section" class="btn btn-secondary" style="margin-top: 1rem;">+ H2 Bölüm Ekle</button>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Güncelle</button>
            <a href="<?= $this->getConfig('base_path') ?>/admin/ilceler" class="btn btn-secondary">İptal</a>
        </div>
    </form>
</div>

<!-- TinyMCE -->
<script src="https://cdn.tiny.cloud/1/6mipkpcvwm5ho51bpyuryoyce97mx2yt0wyatei3l0wda6jo/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<script>
    // TinyMCE Editor for main content
    tinymce.init({
        selector: '#content',
        height: 500,
        menubar: false,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'charmap', 'preview',
            'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'table', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | blocks | ' +
            'bold italic | alignleft aligncenter alignright alignjustify | ' +
            'bullist numlist outdent indent | removeformat | link | code | help',
        content_style: 'body { font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; font-size: 14px }',
        promotion: false,
        branding: false,
    });

    // Dynamic H2 Sections
    let h2SectionIndex = <?= count($h2Sections) ?>;
    const articlesOptions = `<?php foreach ($articles as $article): ?><option value="<?= $article['id'] ?>"><?= htmlspecialchars($article['title']) ?></option><?php endforeach; ?>`;

    document.getElementById('add-h2-section').addEventListener('click', function() {
        const container = document.getElementById('h2-sections-container');
        const newSection = document.createElement('div');
        newSection.className = 'h2-section-item';
        newSection.setAttribute('data-index', h2SectionIndex);
        newSection.innerHTML = `
        <div class="h2-section-header">
            <h4>H2 Bölüm #${h2SectionIndex + 1}</h4>
            <button type="button" class="btn btn-danger btn-small remove-h2-section">Sil</button>
        </div>
        <div class="form-grid">
            <div class="form-group full-width">
                <label>H2 Başlık *</label>
                <input type="text" name="h2_titles[]" class="form-input" placeholder="Örn: <?= htmlspecialchars($district['name']) ?>'de Mobil Lastikçi">
                <small>Detay sayfa H1'inden farklı olmalı</small>
            </div>
            <div class="form-group full-width">
                <label>Açıklama (1-2 cümle) *</label>
                <textarea name="h2_descriptions[]" class="form-input" rows="2" placeholder="Bu bölüm hakkında kısa açıklama (max 500 karakter)"></textarea>
            </div>
            <div class="form-group full-width">
                <label>Bağlantılı Makale (İsteğe Bağlı)</label>
                <select name="h2_article_ids[]" class="form-input">
                    <option value="">Makale Seçiniz</option>
                    ${articlesOptions}
                </select>
                <small>Bu H2 bölümünden link verilecek makale</small>
            </div>
        </div>
    `;
        container.appendChild(newSection);
        h2SectionIndex++;
        updateH2Numbers();
    });

    // Remove H2 Section
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-h2-section')) {
            if (confirm('Bu H2 bölümünü silmek istediğinizden emin misiniz?')) {
                e.target.closest('.h2-section-item').remove();
                updateH2Numbers();
            }
        }
    });

    function updateH2Numbers() {
        const sections = document.querySelectorAll('.h2-section-item');
        sections.forEach((section, index) => {
            section.querySelector('h4').textContent = `H2 Bölüm #${index + 1}`;
        });
    }
</script>

<?php require_once __DIR__ . '/../layouts/admin-footer.php'; ?>