<?php

// Session güvenlik yapılandırması
ini_set('session.cookie_httponly', 1); // JavaScript ile erişimi engelle
ini_set('session.cookie_secure', 0);   // HTTPS için 1 yapın (development için 0)
ini_set('session.cookie_samesite', 'Lax'); // CSRF koruması
ini_set('session.use_strict_mode', 1); // Session ID güvenliği
ini_set('session.cookie_lifetime', 0); // Tarayıcı kapanınca silinsin

// Session parametrelerini ayarla
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => false, // Production'da true yapın
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

// Error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Load helper functions
require_once __DIR__ . '/../core/helpers.php';

// Autoload classes
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../core/',
        __DIR__ . '/../models/',
        __DIR__ . '/../controllers/',
    ];

    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Initialize router
$router = new Router();

// Home routes
$router->get('/', function () {
    $controller = new HomeController();
    $controller->index();
});

// Auth routes
$router->get('/login', function () {
    $controller = new AuthController();
    $controller->loginForm();
});

$router->post('/login', function () {
    $controller = new AuthController();
    $controller->login();
});

$router->get('/logout', function () {
    $controller = new AuthController();
    $controller->logout();
});

$router->get('/forgot-password', function () {
    $controller = new AuthController();
    $controller->forgotPasswordForm();
});

$router->post('/forgot-password', function () {
    $controller = new AuthController();
    $controller->forgotPassword();
});

$router->get('/reset-password/{token}', function ($token) {
    $controller = new AuthController();
    $controller->resetPasswordForm($token);
});

$router->post('/reset-password', function () {
    $controller = new AuthController();
    $controller->resetPassword();
});

// Page routes (Politika sayfaları)
$router->get('/cerez-politikasi', function () {
    $controller = new PageController();
    $controller->cookiePolicy();
});

$router->get('/gizlilik-politikasi', function () {
    $controller = new PageController();
    $controller->privacyPolicy();
});

$router->get('/kvkk-aydinlatma-metni', function () {
    $controller = new PageController();
    $controller->kvkkAydinlatma();
});

$router->get('/hakkimizda', function () {
    $controller = new PageController();
    $controller->about();
});

$router->get('/iletisim', function () {
    $controller = new PageController();
    $controller->contact();
});

$router->post('/iletisim', function () {
    $controller = new PageController();
    $controller->submitContact();
});

// Company routes
$router->get('/firma-ekle', function () {
    $controller = new CompanyController();
    $controller->createForm();
});

$router->post('/firma-ekle', function () {
    $controller = new CompanyController();
    $controller->create();
});

$router->get('/firma-paneli', function () {
    $controller = new CompanyController();
    $controller->dashboard();
});

$router->get('/firma-duzenle/{id}', function ($id) {
    $controller = new CompanyController();
    $controller->editForm($id);
});

$router->post('/firma-duzenle/{id}', function ($id) {
    $controller = new CompanyController();
    $controller->update($id);
});

$router->post('/firma-paneli/hesap-sil', function () {
    $controller = new CompanyController();
    $controller->deleteAccount();
});

// Admin routes
$router->get('/admin', function () {
    $controller = new AdminController();
    $controller->dashboard();
});

$router->get('/admin/firmalar', function () {
    $controller = new AdminController();
    $controller->companies();
});

$router->get('/admin/firma-detay/{id}', function ($id) {
    $controller = new AdminController();
    $controller->companyDetail($id);
});

$router->get('/admin/firma-duzenle/{id}', function ($id) {
    $controller = new AdminController();
    $controller->editCompanyForm($id);
});

$router->post('/admin/firma-duzenle/{id}', function ($id) {
    $controller = new AdminController();
    $controller->updateCompany($id);
});

$router->post('/admin/firma-onayla/{id}', function ($id) {
    $controller = new AdminController();
    $controller->approveCompany($id);
});

$router->post('/admin/firma-sil/{id}', function ($id) {
    $controller = new AdminController();
    $controller->deleteCompany($id);
});

$router->get('/admin/firma-ekle', function () {
    $controller = new AdminController();
    $controller->createCompanyForm();
});

$router->post('/admin/firma-ekle', function () {
    $controller = new AdminController();
    $controller->createCompany();
});

$router->get('/admin/firma-import', function () {
    $controller = new AdminController();
    $controller->companyImportForm();
});

$router->post('/admin/firma-import-process', function () {
    $controller = new AdminController();
    $controller->processCompanyImport();
});

$router->get('/admin/firma-import-sonuc', function () {
    $controller = new AdminController();
    $controller->companyImportResult();
});

$router->get('/admin/makaleler', function () {
    $controller = new AdminController();
    $controller->articles();
});

$router->get('/admin/makale-ekle', function () {
    $controller = new AdminController();
    $controller->createArticleForm();
});

$router->post('/admin/makale-ekle', function () {
    $controller = new AdminController();
    $controller->createArticle();
});

$router->get('/admin/makale-duzenle/{id}', function ($id) {
    $controller = new AdminController();
    $controller->editArticleForm($id);
});

$router->post('/admin/makale-duzenle/{id}', function ($id) {
    $controller = new AdminController();
    $controller->updateArticle($id);
});

$router->post('/admin/makale-sil/{id}', function ($id) {
    $controller = new AdminController();
    $controller->deleteArticle($id);
});

$router->get('/admin/silme-talepleri', function () {
    $controller = new AdminController();
    $controller->deletionRequests();
});

$router->get('/admin/silme-talebi/{id}', function ($id) {
    $controller = new AdminController();
    $controller->deletionRequestDetail($id);
});

$router->post('/admin/silme-talebi/onayla', function () {
    $controller = new AdminController();
    $controller->approveDeletionRequest();
});

$router->post('/admin/silme-talebi/reddet', function () {
    $controller = new AdminController();
    $controller->rejectDeletionRequest();
});

$router->post('/admin/silme-talebi/tamamla', function () {
    $controller = new AdminController();
    $controller->completeDeletionRequest();
});

$router->get('/admin/kullanicilar', function () {
    $controller = new AdminController();
    $controller->users();
});

$router->get('/admin/kullanici-ekle', function () {
    $controller = new AdminController();
    $controller->createUserForm();
});

$router->post('/admin/kullanici-ekle', function () {
    $controller = new AdminController();
    $controller->createUser();
});

$router->post('/admin/kullanici-sil/{id}', function ($id) {
    $controller = new AdminController();
    $controller->deleteUser($id);
});

// API routes
$router->get('/api/districts/{city_id}', function ($cityId) {
    // Make $cityId available to the required file
    $_GET['city_id'] = $cityId;
    require_once __DIR__ . '/api/districts.php';
});

// Search route
$router->get('/arama', function () {
    $controller = new HomeController();
    $controller->search();
});

// Dynamic routes - Process in order of specificity

// Company detail routes - Firma detay (İlçe seviyesi)
$router->get('/{city}/{district}/firma/{company}', function ($citySlug, $districtSlug, $companySlug) {
    $controller = new CompanyController();
    $controller->show($citySlug, $districtSlug, $companySlug);
});

// Article routes - Makale detay (İlçe seviyesi)
$router->get('/{city}/{district}/{article}', function ($citySlug, $districtSlug, $articleSlug) {
    // Check if it's a district page or article
    $cityModel = new City();
    $city = $cityModel->findBySlug($citySlug);

    if ($city) {
        $districtModel = new District();
        $district = $districtModel->findBySlug($city['id'], $districtSlug);

        if ($district) {
            // Check if article exists
            $articleModel = new Article();
            $article = $articleModel->findBySlug($articleSlug, $city['id'], $district['id']);

            if ($article) {
                $controller = new ArticleController();
                $controller->show($citySlug, $districtSlug, $articleSlug);
            } else {
                http_response_code(404);
                require_once __DIR__ . '/../views/errors/404.php';
            }
        } else {
            http_response_code(404);
            require_once __DIR__ . '/../views/errors/404.php';
        }
    } else {
        http_response_code(404);
        require_once __DIR__ . '/../views/errors/404.php';
    }
});

// District/Article routes - İlçe sayfası veya İl seviyesi makale
$router->get('/{city}/{slug}', function ($citySlug, $slug) {
    $cityModel = new City();
    $city = $cityModel->findBySlug($citySlug);

    if (!$city) {
        http_response_code(404);
        require_once __DIR__ . '/../views/errors/404.php';
        return;
    }

    // First check if it's a district
    $districtModel = new District();
    $district = $districtModel->findBySlug($city['id'], $slug);

    if ($district) {
        // It's a district page
        $controller = new CityController();
        $controller->showDistrict($citySlug, $slug);
        return;
    }

    // Check if it's an article (city level)
    $articleModel = new Article();
    $article = $articleModel->findBySlug($slug, $city['id'], null);

    if ($article) {
        // It's a city-level article
        $controller = new ArticleController();
        $controller->show($citySlug, null, $slug);
        return;
    }

    // Not found
    http_response_code(404);
    require_once __DIR__ . '/../views/errors/404.php';
});

// City routes - İl sayfası
$router->get('/{city}', function ($citySlug) {
    $controller = new CityController();
    $controller->show($citySlug);
});

// 404 handler
$router->notFound(function () {
    http_response_code(404);
    require_once __DIR__ . '/../views/errors/404.php';
});

// Run router
$router->run();
