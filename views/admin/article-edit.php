<?php $pageId = 'article-edit'; ?>
<?php require_once __DIR__ . '/../layouts/admin-header.php'; ?>

<div class="admin-header">
    <h1>Makale Düzenle</h1>
</div>

<div class="form-container">
    <form method="POST" action="<?= $this->getConfig('base_path') ?>/admin/makale-duzenle/<?= $article['id'] ?>" class="standard-form">
        <div class="form-grid">
            <div class="form-group full-width">
                <label for="title">Başlık *</label>
                <input type="text" id="title" name="title" class="form-input" value="<?= htmlspecialchars($article['title']) ?>" required>
            </div>

            <div class="form-group">
                <label for="city_id">İl *</label>
                <select id="city_id" name="city_id" class="form-input" required>
                    <option value="">Seçiniz</option>
                    <?php foreach ($cities as $city): ?>
                        <option value="<?= $city['id'] ?>" <?= $article['city_id'] == $city['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($city['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="district_id">İlçe</label>
                <select id="district_id" name="district_id" class="form-input">
                    <option value="">Seçiniz</option>
                    <?php foreach ($districts as $district): ?>
                        <option value="<?= $district['id'] ?>" <?= $article['district_id'] == $district['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($district['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group full-width">
                <label for="excerpt">Özet</label>
                <textarea id="excerpt" name="excerpt" class="form-input" rows="3"><?= htmlspecialchars($article['excerpt'] ?? '') ?></textarea>
            </div>

            <div class="form-group full-width">
                <label for="content">İçerik *</label>
                <textarea id="content" name="content" class="form-input" rows="10" required><?= htmlspecialchars($article['content']) ?></textarea>
            </div>

            <div class="form-group full-width">
                <label for="meta_title">SEO Başlık</label>
                <input type="text" id="meta_title" name="meta_title" class="form-input" value="<?= htmlspecialchars($article['meta_title'] ?? '') ?>">
            </div>

            <div class="form-group full-width">
                <label for="meta_description">SEO Açıklama</label>
                <textarea id="meta_description" name="meta_description" class="form-input" rows="2"><?= htmlspecialchars($article['meta_description'] ?? '') ?></textarea>
            </div>

            <div class="form-group full-width">
                <label>
                    <input type="checkbox" name="is_published" value="1" <?= $article['is_published'] ? 'checked' : '' ?>>
                    Yayında
                </label>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Güncelle</button>
            <a href="<?= $this->getConfig('base_path') ?>/admin/makaleler" class="btn btn-secondary">İptal</a>
        </div>
    </form>
</div>

<script src="https://cdn.tiny.cloud/1/6mipkpcvwm5ho51bpyuryoyce97mx2yt0wyatei3l0wda6jo/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<script>
// İl seçilince ilçeleri getir
document.getElementById('city_id').addEventListener('change', function() {
    const cityId = this.value;
    const districtSelect = document.getElementById('district_id');
    const currentDistrictId = <?= $article['district_id'] ?? 'null' ?>;

    districtSelect.innerHTML = '<option value="">Yükleniyor...</option>';

    if (!cityId) {
        districtSelect.innerHTML = '<option value="">Seçiniz</option>';
        return;
    }

    fetch(`<?= $this->getConfig('base_path') ?>/api/districts/${cityId}`)
        .then(response => response.json())
        .then(data => {
            districtSelect.innerHTML = '<option value="">Seçiniz</option>';
            data.forEach(district => {
                const selected = district.id == currentDistrictId ? 'selected' : '';
                districtSelect.innerHTML += `<option value="${district.id}" ${selected}>${district.name}</option>`;
            });
        });
});

// TinyMCE Editor
tinymce.init({
    selector: '#content',
    height: 500,
    menubar: false,
    plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'help', 'wordcount'
    ],
    toolbar: 'undo redo | blocks | ' +
        'bold italic forecolor | alignleft aligncenter ' +
        'alignright alignjustify | bullist numlist outdent indent | ' +
        'removeformat | link image | code | help',
    content_style: 'body { font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; font-size: 14px }',
    promotion: false,
    branding: false,
    setup: function(editor) {
        editor.on('init', function() {
            console.log('TinyMCE editor yüklendi');
        });
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/admin-footer.php'; ?>
