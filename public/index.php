<?php

// Character encoding configuration - MUST BE FIRST
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
ini_set('default_charset', 'UTF-8');

// Timeout configuration for long-running operations
ini_set('max_execution_time', 300); // 5 minutes max for web requests
ini_set('max_input_time', 300); // 5 minutes for input processing

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
        __DIR__ . '/../services/',
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

$router->get('/admin/firma-import-ornek-indir', function () {
    $controller = new AdminController();
    $controller->downloadExampleExcel();
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

// City (Şehir) management routes
$router->get('/admin/sehirler', function () {
    $controller = new AdminController();
    $controller->cities();
});

$router->get('/admin/sehir-duzenle/{id}', function ($id) {
    $controller = new AdminController();
    $controller->editCityForm($id);
});

$router->post('/admin/sehir-duzenle/{id}', function ($id) {
    $controller = new AdminController();
    $controller->updateCity($id);
});

// District (İlçe) management routes
$router->get('/admin/ilceler', function () {
    $controller = new AdminController();
    $controller->districts();
});

$router->get('/admin/ilce-duzenle/{id}', function ($id) {
    $controller = new AdminController();
    $controller->editDistrictForm($id);
});

$router->post('/admin/ilce-duzenle/{id}', function ($id) {
    $controller = new AdminController();
    $controller->updateDistrict($id);
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

// AI Article Generator routes
$router->get('/admin/ai-makale-uret', function () {
    $controller = new AdminController();
    $controller->aiArticleGeneratorForm();
});

$router->post('/admin/ai-makale-uret', function () {
    $controller = new AdminController();
    $controller->generateAIArticlePreview();
});

$router->get('/admin/ai-makale-onizle', function () {
    $controller = new AdminController();
    $controller->aiArticlePreview();
});

$router->post('/admin/ai-makale-kaydet', function () {
    $controller = new AdminController();
    $controller->saveAIArticle();
});

$router->post('/admin/ai-makale-toplu-uret', function () {
    $controller = new AdminController();
    $controller->bulkGenerateAIArticles();
});

$router->get('/admin/ai-makale-sonuc', function () {
    $controller = new AdminController();
    $controller->bulkGenerationResult();
});

$router->get('/admin/job-status', function () {
    $controller = new AdminController();
    $controller->getJobStatus();
});

$router->post('/admin/trigger-job-processor', function () {
    $controller = new AdminController();
    $controller->triggerJobProcessor();
});

$router->post('/admin/cancel-job', function () {
    $controller = new AdminController();
    $controller->cancelJob();
});

$router->get('/admin/ai-ayarlar', function () {
    $controller = new AdminController();
    $controller->aiSettings();
});

$router->post('/admin/ai-ayarlar', function () {
    $controller = new AdminController();
    $controller->saveAISettings();
});

$router->post('/admin/ai-test-baglanti', function () {
    $controller = new AdminController();
    $controller->testAIConnection();
});

$router->get('/admin/generate-keyword-suggestions', function () {
    $controller = new AdminController();
    $controller->generateKeywordSuggestions();
});

$router->post('/admin/get-districts-for-cities', function () {
    $controller = new AdminController();
    $controller->getDistrictsForCities();
});

// AI Provider Management Routes (Multi-Provider)
$router->get('/admin/ai-saglaiycilar', function () {
    $controller = new AdminController();
    $controller->aiProviderSettings();
});

$router->post('/admin/ai-provider-toggle', function () {
    $controller = new AdminController();
    $controller->aiProviderToggle();
});

$router->post('/admin/ai-provider-test', function () {
    $controller = new AdminController();
    $controller->aiProviderTest();
});

$router->get('/admin/ai-provider-get/{id}', function ($id) {
    $controller = new AdminController();
    $controller->aiProviderGet($id);
});

$router->post('/admin/ai-provider-update/{id}', function ($id) {
    $controller = new AdminController();
    $controller->aiProviderUpdate($id);
});

$router->post('/admin/ai-provider-set-default', function () {
    $controller = new AdminController();
    $controller->aiProviderSetDefault();
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
        // VALIDATION: HUB articles should not be accessible via slug
        // Redirect to canonical URL (SEO duplicate content prevention)
        if (is_null($article['slug'])) {
            header("Location: /{$citySlug}", true, 301);
            exit;
        }

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
