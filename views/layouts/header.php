<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- SEO Meta Tags -->
    <title><?= $pageTitle ?? 'Lastikciariyorum.com - Türkiye\'nin Lastik Tamircisi Rehberi' ?></title>
    <meta name="description" content="<?= $metaDescription ?? 'Türkiye\'nin her ilinde güvenilir lastik tamircileri. Lastik tamir, lastik değişimi ve lastik bakım hizmetleri için kapsamlı rehber.' ?>">
    <meta name="keywords" content="lastik tamircisi, lastik tamir, lastik değişimi, <?= $pageTitle ?? 'lastik servisi' ?>">
    <meta name="author" content="Lastikciariyorum.com">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://www.lastikciariyorum.com<?= $_SERVER['REQUEST_URI'] ?>">

    <!-- Google Site Verification -->
    <meta name="google-site-verification" content="cVr1pk2cEtoApo4VQuRPzUz7u-w53oisqZCz2OaoZVI" />

    <?php
    // Admin sayfalarında reklam gösterme
    if (!isset($GLOBALS['isAdminPage'])) {
        $GLOBALS['isAdminPage'] = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin' &&
            (strpos($_SERVER['REQUEST_URI'], '/admin') !== false);
    }
    $isAdminPage = $GLOBALS['isAdminPage'];
    ?>

    <?php if (!$isAdminPage): ?>
        <!-- Google AdSense -->
        <meta name="google-adsense-account" content="ca-pub-6682213256253444">
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-6682213256253444"
                crossorigin="anonymous"></script>
    <?php endif; ?>

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://lastikciariyorum.com<?= $_SERVER['REQUEST_URI'] ?>">
    <meta property="og:title" content="<?= $pageTitle ?? 'Lastikciariyorum.com - Türkiye\'nin Lastik Tamircisi Rehberi' ?>">
    <meta property="og:description" content="<?= $metaDescription ?? 'Türkiye\'nin her ilinde güvenilir lastik tamircileri' ?>">
    <meta property="og:image" content="https://lastikciariyorum.com<?= asset('images/og-image.jpg') ?>">
    <meta property="og:locale" content="tr_TR">
    <meta property="og:site_name" content="Lastikciariyorum.com">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="https://lastikciariyorum.com<?= $_SERVER['REQUEST_URI'] ?>">
    <meta name="twitter:title" content="<?= $pageTitle ?? 'Lastikciariyorum.com - Türkiye\'nin Lastik Tamircisi Rehberi' ?>">
    <meta name="twitter:description" content="<?= $metaDescription ?? 'Türkiye\'nin her ilinde güvenilir lastik tamircileri' ?>">
    <meta name="twitter:image" content="https://lastikciariyorum.com<?= asset('images/og-image.jpg') ?>">

    <!-- Favicon -->
    <link rel="icon" href="<?= asset('images/favicon.svg') ?>" type="image/x-icon">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/cookie-consent.css') ?>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Schema.org Yapılandırılmış Veri -->
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "WebSite",
            "name": "Lastikciariyorum.com",
            "url": "https://lastikciariyorum.com",
            "description": "Türkiye'nin her ilinde güvenilir lastik tamircileri. Lastik tamir, lastik değişimi ve lastik bakım hizmetleri için kapsamlı rehber.",
            "potentialAction": {
                "@type": "SearchAction",
                "target": "https://lastikciariyorum.com/arama?q={search_term_string}",
                "query-input": "required name=search_term_string"
            },
            "publisher": {
                "@type": "Organization",
                "name": "Lastikciariyorum.com",
                "logo": {
                    "@type": "ImageObject",
                    "url": "https://lastikciariyorum.com<?= asset('images/logo.svg') ?>"
                }
            }
        }
    </script>

    <?php if (isset($organizationSchema) && $organizationSchema): ?>
        <!-- Firma Schema -->
        <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "LocalBusiness",
                "name": "<?= htmlspecialchars($organizationSchema['name']) ?>",
                "image": "<?= $organizationSchema['image'] ?? '' ?>",
                "telephone": "<?= htmlspecialchars($organizationSchema['phone']) ?>",
                "address": {
                    "@type": "PostalAddress",
                    "streetAddress": "<?= htmlspecialchars($organizationSchema['address']) ?>",
                    "addressLocality": "<?= htmlspecialchars($organizationSchema['district']) ?>",
                    "addressRegion": "<?= htmlspecialchars($organizationSchema['city']) ?>",
                    "addressCountry": "TR"
                },
                "geo": {
                    "@type": "GeoCoordinates",
                    "latitude": "<?= $organizationSchema['latitude'] ?? '' ?>",
                    "longitude": "<?= $organizationSchema['longitude'] ?? '' ?>"
                },
                "url": "<?= $organizationSchema['url'] ?>",
                "priceRange": "$$",
                "openingHoursSpecification": [{
                        "@type": "OpeningHoursSpecification",
                        "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
                        "opens": "<?= $organizationSchema['weekday_open'] ?? '08:00' ?>",
                        "closes": "<?= $organizationSchema['weekday_close'] ?? '19:00' ?>"
                    },
                    {
                        "@type": "OpeningHoursSpecification",
                        "dayOfWeek": "Saturday",
                        "opens": "<?= $organizationSchema['saturday_open'] ?? '09:00' ?>",
                        "closes": "<?= $organizationSchema['saturday_close'] ?? '18:00' ?>"
                    }
                ]
            }
        </script>
    <?php endif; ?>

    <?php if (isset($breadcrumbSchema) && $breadcrumbSchema): ?>
        <!-- Breadcrumb Schema -->
        <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "BreadcrumbList",
                "itemListElement": [
                    <?php foreach ($breadcrumbSchema as $index => $item): ?> {
                            "@type": "ListItem",
                            "position": <?= $index + 1 ?>,
                            "name": "<?= htmlspecialchars($item['name']) ?>",
                            "item": "<?= $item['url'] ?>"
                        }
                        <?= $index < count($breadcrumbSchema) - 1 ? ',' : '' ?>
                    <?php endforeach; ?>
                ]
            }
        </script>
    <?php endif; ?>

    <?php if (isset($articleSchema) && $articleSchema): ?>
        <!-- Makale Schema -->
        <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "Article",
                "headline": "<?= htmlspecialchars($articleSchema['headline']) ?>",
                "description": "<?= htmlspecialchars($articleSchema['description']) ?>",
                "image": "<?= $articleSchema['image'] ?? '' ?>",
                "datePublished": "<?= $articleSchema['datePublished'] ?>",
                "dateModified": "<?= $articleSchema['dateModified'] ?? $articleSchema['datePublished'] ?>",
                "author": {
                    "@type": "Organization",
                    "name": "Lastikciariyorum.com"
                },
                "publisher": {
                    "@type": "Organization",
                    "name": "Lastikciariyorum.com",
                    "logo": {
                        "@type": "ImageObject",
                        "url": "https://lastikciariyorum.com<?= asset('images/logo.svg') ?>"
                    }
                }
            }
        </script>
    <?php endif; ?>

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-CCQS82S888"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-CCQS82S888');
    </script>
</head>

<body<?= $isAdminPage ? ' class="admin-page no-ads"' : '' ?>>
    <nav class="navbar">
        <div class="container">
            <div class="navbar-content">
                <a href="<?= url('/') ?>" class="logo">
                    <img src="<?= asset('images/logo.svg') ?>" alt="Lastikçi Arıyorum Logo" class="logo-img">
                </a>

                <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Menü">
                    <span class="hamburger"></span>
                    <span class="hamburger"></span>
                    <span class="hamburger"></span>
                </button>

                <div class="navbar-menu" id="navbarMenu">
                    <a href="<?= url('/') ?>" class="nav-link">Ana Sayfa</a>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                            <a href="<?= url('/admin') ?>" class="nav-link">Admin Panel</a>
                        <?php elseif ($_SESSION['user_role'] === 'company'): ?>
                            <a href="<?= url('/firma-paneli') ?>" class="nav-link">Firma Paneli</a>
                        <?php endif; ?>

                        <span class="nav-user">
                            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                👤 Admin
                            <?php else: ?>
                                👤 <?= htmlspecialchars($_SESSION['user_name']) ?>
                            <?php endif; ?>
                        </span>
                        <a href="<?= url('/logout') ?>" class="nav-link">Çıkış</a>
                    <?php else: ?>
                        <a href="<?= url('/login') ?>" class="nav-link">Giriş Yap</a>
                        <a href="<?= url('/firma-ekle') ?>" class="btn btn-primary">Firma Ekle</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <script>
        // Mobile menu toggle
        document.getElementById('mobileMenuToggle').addEventListener('click', function() {
            const menu = document.getElementById('navbarMenu');
            const toggle = this;
            menu.classList.toggle('active');
            toggle.classList.toggle('active');
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('navbarMenu');
            const toggle = document.getElementById('mobileMenuToggle');
            if (!menu.contains(event.target) && !toggle.contains(event.target)) {
                menu.classList.remove('active');
                toggle.classList.remove('active');
            }
        });
    </script>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <div class="container">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <div class="container">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>