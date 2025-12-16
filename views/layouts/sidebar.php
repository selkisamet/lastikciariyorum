<aside class="sidebar">
    <div class="sidebar-card">
        <h3 class="sidebar-title">Firmalar</h3>
        <?php if (!empty($companies)): ?>
            <div class="company-list">
                <?php foreach ($companies as $company): ?>
                    <div class="company-info">
                        <h4 class="company-name"><a href="<?= $this->getConfig('base_path') ?>/<?= $city['slug'] ?><?= isset($company['district_slug']) && $company['district_slug'] ? '/' . $company['district_slug'] : '' ?>/firma/<?= $company['slug'] ?>"><?= htmlspecialchars($company['name']) ?></a></h4>
                        <?php if (isset($company['district_name']) && $company['district_name']): ?>
                            <span class="company-location"><?= htmlspecialchars($company['district_name']) ?></span>
                        <?php endif; ?>
                        <?php if ($company['phone']): ?>
                            <a href="tel:<?= htmlspecialchars($company['phone']) ?>" class="company-phone" onclick="event.stopPropagation();">📞 <?= htmlspecialchars($company['phone']) ?></a>
                        <?php endif; ?>
                        <?php if ($company['address']): ?>
                            <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($company['address']) ?>" target="_blank" rel="noopener" class="company-address" onclick="event.stopPropagation();">📍 <?= htmlspecialchars($company['address']) ?></a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="no-data">Henüz firma bulunmuyor</p>
        <?php endif; ?>
    </div>
</aside>