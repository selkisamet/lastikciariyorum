<?php
// Admin sayfası olduğunu işaretle
$GLOBALS['isAdminPage'] = true;
$isAdminPage = true;
require_once __DIR__ . '/header.php';
?>

<!-- Modern Modal System -->
<link rel="stylesheet" href="<?= asset('css/modal.css') ?>">
<script src="<?= asset('js/modal.js') ?>" defer></script>

<div class="admin-sidebar-overlay" id="adminSidebarOverlay"></div>

<button class="admin-menu-toggle" id="adminMenuToggle" aria-label="Menü">
    ☰
</button>

<div class="admin-container">
    <aside class="admin-sidebar" id="adminSidebar">
        <nav class="admin-nav">
            <a href="<?= $this->getConfig('base_path') ?>/admin" class="admin-nav-link <?= $pageId === 'dashboard' ? 'active' : '' ?>">Anasayfa</a>
            <a href="<?= $this->getConfig('base_path') ?>/admin/firmalar" class="admin-nav-link <?= $pageId === 'companies' ? 'active' : '' ?>">Firmalar</a>
            <a href="<?= $this->getConfig('base_path') ?>/admin/kullanicilar" class="admin-nav-link <?= $pageId === 'users' ? 'active' : '' ?>">Kullanıcılar</a>
            <a href="<?= $this->getConfig('base_path') ?>/admin/makaleler" class="admin-nav-link <?= $pageId === 'articles' ? 'active' : '' ?>">Makaleler</a>
            <a href="<?= $this->getConfig('base_path') ?>/admin/sehirler" class="admin-nav-link <?= $pageId === 'cities' || $pageId === 'city-edit' ? 'active' : '' ?>">Şehirler</a>
            <a href="<?= $this->getConfig('base_path') ?>/admin/ilceler" class="admin-nav-link <?= $pageId === 'districts' || $pageId === 'district-edit' ? 'active' : '' ?>">İlçeler</a>
            <a href="<?= $this->getConfig('base_path') ?>/admin/silme-talepleri" class="admin-nav-link <?= $pageId === 'deletion-requests' ? 'active' : '' ?>">Silme Talepleri</a>
        </nav>
    </aside>

    <main class="admin-main">