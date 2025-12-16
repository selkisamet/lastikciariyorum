// Admin sayfalarında reklamları engelle
(function() {
    // AdSense scriptlerini engelle
    if (window.adsbygoogle) {
        window.adsbygoogle = [];
    }

    // Tüm reklam elementlerini kaldır
    function removeAds() {
        const adElements = document.querySelectorAll('ins.adsbygoogle, .adsbygoogle, [data-ad-client], [data-ad-slot]');
        adElements.forEach(el => {
            el.remove();
        });
    }

    // Sayfa yüklendiğinde ve periyodik olarak reklamları kaldır
    setInterval(removeAds, 500);
    removeAds();
})();

// Admin sidebar toggle for mobile
document.addEventListener('DOMContentLoaded', function() {
    const adminMenuToggle = document.getElementById('adminMenuToggle');
    const adminSidebar = document.getElementById('adminSidebar');
    const adminSidebarOverlay = document.getElementById('adminSidebarOverlay');

    if (adminMenuToggle && adminSidebar && adminSidebarOverlay) {
        adminMenuToggle.addEventListener('click', function() {
            adminSidebar.classList.toggle('active');
            adminSidebarOverlay.classList.toggle('active');
        });

        adminSidebarOverlay.addEventListener('click', function() {
            adminSidebar.classList.remove('active');
            adminSidebarOverlay.classList.remove('active');
        });

        // Menü linklerine tıklandığında menüyü kapat (mobilde)
        document.querySelectorAll('.admin-nav-link').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    adminSidebar.classList.remove('active');
                    adminSidebarOverlay.classList.remove('active');
                }
            });
        });
    }
});
