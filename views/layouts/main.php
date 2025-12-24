<?php require_once __DIR__ . '/header.php'; ?>

<?php if (isset($pageHeader) && $pageHeader): ?>
<div class="page-header">
    <div class="container">
        <?php if (isset($breadcrumb) && $breadcrumb): ?>
        <nav class="breadcrumb">
            <?= $breadcrumb ?>
        </nav>
        <?php endif; ?>

        <?php if (isset($h1) && $h1): ?>
        <h1 class="page-title"><?= $h1 ?></h1>
        <?php endif; ?>

        <?php if (isset($pageDescription) && $pageDescription): ?>
        <p class="page-description"><?= $pageDescription ?></p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if (isset($hasSidebar) && $hasSidebar): ?>
<section class="content-section">
    <div class="container">
        <div class="two-column-layout">
            <?php require_once __DIR__ . '/sidebar.php'; ?>
            <main class="main-content">
                <?= $content ?>
            </main>
        </div>
    </div>
</section>
<?php else: ?>
    <?= $content ?>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
