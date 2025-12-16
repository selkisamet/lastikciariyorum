<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/City.php';
require_once __DIR__ . '/../models/District.php';
require_once __DIR__ . '/../models/Company.php';
require_once __DIR__ . '/../models/Article.php';
require_once __DIR__ . '/../models/DeletionRequest.php';

class AdminController extends Controller
{
    private $cityModel;
    private $districtModel;
    private $companyModel;
    private $articleModel;
    private $deletionRequestModel;
    private $userModel;

    public function __construct()
    {
        $this->cityModel = new City();
        $this->districtModel = new District();
        $this->companyModel = new Company();
        $this->articleModel = new Article();
        $this->deletionRequestModel = new DeletionRequest();
        $this->userModel = new User();

        // Check if user is admin
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
    }

    public function dashboard()
    {
        $pendingCompanies = $this->companyModel->getPending(5);

        // İstatistikler
        $totalCompanies = $this->companyModel->getTotalCount();
        $approvedCompanies = $this->companyModel->getApprovedCount();
        $pendingCount = $this->companyModel->getPendingCount();
        $totalArticles = $this->articleModel->getTotalCount();
        $pendingDeletionRequests = $this->deletionRequestModel->getPendingCount();

        // Son 7 günlük aktiviteler
        $recentCompanies = $this->companyModel->getRecent(5);
        $recentArticles = $this->articleModel->getRecentArticles(5);

        // Son 7 günlük istatistikler (grafik için)
        $last7DaysStats = $this->companyModel->getLast7DaysStats();

        $this->view('admin.dashboard', [
            'pageTitle' => 'Admin Paneli',
            'pendingCompanies' => $pendingCompanies,
            'totalCompanies' => $totalCompanies,
            'approvedCompanies' => $approvedCompanies,
            'pendingCount' => $pendingCount,
            'totalArticles' => $totalArticles,
            'pendingDeletionRequests' => $pendingDeletionRequests,
            'recentCompanies' => $recentCompanies,
            'recentArticles' => $recentArticles,
            'last7DaysStats' => $last7DaysStats,
        ]);
    }

    public function companies()
    {
        $companies = $this->companyModel->getAll();

        $this->view('admin.companies', [
            'pageTitle' => 'Firma Yönetimi',
            'companies' => $companies,
        ]);
    }

    public function companyDetail($id)
    {
        $company = $this->companyModel->find($id);

        if (!$company) {
            $_SESSION['error'] = 'Firma bulunamadı.';
            $this->redirect('/admin/firmalar');
            return;
        }

        // Şehir ve ilçe bilgilerini ekle
        $city = $this->cityModel->find($company['city_id']);
        $company['city_name'] = $city['name'];

        if ($company['district_id']) {
            $district = $this->districtModel->find($company['district_id']);
            $company['district_name'] = $district['name'] ?? null;
        } else {
            $company['district_name'] = null;
        }

        // Kullanıcı bilgilerini getir
        $user = null;
        if ($company['user_id']) {
            require_once __DIR__ . '/../models/User.php';
            $userModel = new User();
            $user = $userModel->find($company['user_id']);
        }

        $this->view('admin.company-detail', [
            'pageTitle' => 'Firma Detayları - ' . $company['name'],
            'company' => $company,
            'user' => $user,
        ]);
    }

    public function editCompanyForm($id)
    {
        $company = $this->companyModel->find($id);

        if (!$company) {
            $_SESSION['error'] = 'Firma bulunamadı.';
            $this->redirect('/admin/firmalar');
            return;
        }

        $cities = $this->cityModel->all();
        $districts = [];

        if ($company['city_id']) {
            $districts = $this->districtModel->getByCity($company['city_id']);
        }

        // Kullanıcı bilgilerini getir
        $user = null;
        if ($company['user_id']) {
            require_once __DIR__ . '/../models/User.php';
            $userModel = new User();
            $user = $userModel->find($company['user_id']);
        }

        $this->view('admin.company-edit', [
            'pageTitle' => 'Firma Düzenle',
            'company' => $company,
            'user' => $user,
            'cities' => $cities,
            'districts' => $districts,
        ]);
    }

    public function updateCompany($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/firmalar');
            return;
        }

        $company = $this->companyModel->find($id);

        if (!$company) {
            $_SESSION['error'] = 'Firma bulunamadı.';
            $this->redirect('/admin/firmalar');
            return;
        }

        // Kullanıcı bilgilerini güncelle (varsa)
        if ($company['user_id']) {
            require_once __DIR__ . '/../models/User.php';
            $userModel = new User();

            $userData = [
                'email' => $_POST['user_email'] ?? '',
                'full_name' => $_POST['full_name'] ?? '',
            ];

            // E-posta değiştirilmişse, başka kullanıcı tarafından kullanılmadığını kontrol et
            $existingUser = $userModel->findByEmail($_POST['user_email']);
            if ($existingUser && $existingUser['id'] != $company['user_id']) {
                $_SESSION['error'] = 'Bu e-posta adresi zaten kullanılıyor.';
                $this->redirect('/admin/firma-duzenle/' . $id);
                return;
            }

            // Yeni şifre girildiyse güncelle
            if (!empty($_POST['new_password'])) {
                if (strlen($_POST['new_password']) < 6) {
                    $_SESSION['error'] = 'Şifre en az 6 karakter olmalıdır.';
                    $this->redirect('/admin/firma-duzenle/' . $id);
                    return;
                }
                $userData['password'] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            }

            $userModel->update($company['user_id'], $userData);
        }

        // Firma bilgilerini güncelle
        $data = [
            'name' => $_POST['name'],
            'slug' => $this->generateSlug($_POST['name']),
            'city_id' => $_POST['city_id'],
            'district_id' => $_POST['district_id'] ?? null,
            'phone' => $_POST['phone'] ?? null,
            'address' => $_POST['address'] ?? null,
            'description' => $_POST['description'] ?? null,
            'website' => $_POST['website'] ?? null,
            'email' => $_POST['email'] ?? null,
            'is_approved' => isset($_POST['is_approved']) ? 1 : 0,
        ];

        // Logo upload işlemi
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../public/uploads/logos/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileExtension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($fileExtension, $allowedExtensions)) {
                if ($_FILES['logo']['size'] <= 2 * 1024 * 1024) {
                    $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
                    $targetPath = $uploadDir . $fileName;

                    if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath)) {
                        // Eski logoyu sil (varsa)
                        if ($company['logo'] && file_exists($uploadDir . $company['logo'])) {
                            unlink($uploadDir . $company['logo']);
                        }

                        $data['logo'] = $fileName;
                    }
                } else {
                    $_SESSION['error'] = 'Logo dosyası çok büyük. Maksimum 2MB olmalıdır.';
                    $this->redirect('/admin/firma-duzenle/' . $id);
                    return;
                }
            } else {
                $_SESSION['error'] = 'Geçersiz dosya formatı. Sadece JPG, PNG veya GIF kabul edilir.';
                $this->redirect('/admin/firma-duzenle/' . $id);
                return;
            }
        }

        $this->companyModel->update($id, $data);

        $_SESSION['success'] = 'Firma bilgileri başarıyla güncellendi.';
        $this->redirect('/admin/firmalar');
    }

    public function approveCompany($id)
    {
        // Firmayı bul
        $company = $this->companyModel->find($id);

        if (!$company) {
            $_SESSION['error'] = 'Firma bulunamadı.';
            $this->redirect('/admin/firmalar');
            return;
        }

        // Firmayı onayla
        $this->companyModel->approve($id);

        // Kullanıcıyı aktif et
        if ($company['user_id']) {
            $userModel = new User();
            $userModel->update($company['user_id'], ['is_active' => 1]);
        }

        $_SESSION['success'] = 'Firma ve kullanıcı onaylandı. Artık giriş yapabilir.';

        // Hangi sayfadan geldiğine göre yönlendir
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (strpos($referer, 'firma-detay') !== false) {
            $this->redirect('/admin/firma-detay/' . $id);
        } else {
            $this->redirect('/admin/firmalar');
        }
    }

    public function createCompanyForm()
    {
        $cities = $this->cityModel->all();

        $this->view('admin.company-create', [
            'pageTitle' => 'Firma Ekle',
            'cities' => $cities,
        ]);
    }

    public function createCompany()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/firma-ekle');
            return;
        }

        // Firma bilgileri
        $companyName = $_POST['name'] ?? '';
        $cityId = $_POST['city_id'] ?? '';
        $districtId = $_POST['district_id'] ?? '';

        if (empty($companyName) || empty($cityId) || empty($districtId)) {
            $_SESSION['error'] = 'Firma adı, il ve ilçe alanları zorunludur.';
            $this->redirect('/admin/firma-ekle');
            return;
        }

        $userId = null;

        // Kullanıcı bilgileri opsiyonel olarak kontrol et
        $userEmail = $_POST['user_email'] ?? '';
        $fullName = $_POST['full_name'] ?? '';
        $password = $_POST['password'] ?? '';

        // Eğer kullanıcı bilgilerinden biri girilmişse, diğerleri de gerekli
        if (!empty($userEmail) || !empty($fullName) || !empty($password)) {
            if (empty($userEmail) || empty($fullName) || empty($password)) {
                $_SESSION['error'] = 'Kullanıcı oluşturmak için E-posta, Tam Ad ve Şifre alanlarını doldurmanız gerekiyor.';
                $this->redirect('/admin/firma-ekle');
                return;
            }

            // Şifre uzunluğu kontrolü
            if (strlen($password) < 6) {
                $_SESSION['error'] = 'Şifre en az 6 karakter olmalıdır.';
                $this->redirect('/admin/firma-ekle');
                return;
            }

            // E-posta kontrolü
            require_once __DIR__ . '/../models/User.php';
            $userModel = new User();

            if ($userModel->findByEmail($userEmail)) {
                $_SESSION['error'] = 'Bu e-posta adresi zaten kullanılıyor.';
                $this->redirect('/admin/firma-ekle');
                return;
            }

            // Kullanıcı oluştur
            $userData = [
                'email' => $userEmail,
                'password' => $password,
                'full_name' => $fullName,
                'role' => 'company',
                'is_active' => isset($_POST['user_is_active']) ? 1 : 0,
            ];

            $userId = $userModel->register($userData);
        }

        // Firma oluştur
        $companyData = [
            'user_id' => $userId,
            'city_id' => $cityId,
            'district_id' => $districtId,
            'name' => $companyName,
            'slug' => $this->generateSlug($companyName),
            'phone' => $_POST['phone'] ?? null,
            'address' => $_POST['address'] ?? null,
            'description' => $_POST['description'] ?? null,
            'website' => $_POST['website'] ?? null,
            'email' => $_POST['email'] ?? null,
            'is_approved' => isset($_POST['is_approved']) ? 1 : 0,
        ];

        // Logo upload işlemi
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../public/uploads/logos/';

            // Klasörü oluştur (yoksa)
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileExtension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

            // Dosya uzantısı kontrolü
            if (in_array($fileExtension, $allowedExtensions)) {
                // Dosya boyutu kontrolü (2MB)
                if ($_FILES['logo']['size'] <= 2 * 1024 * 1024) {
                    // Benzersiz dosya adı oluştur
                    $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
                    $targetPath = $uploadDir . $fileName;

                    // Dosyayı yükle
                    if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath)) {
                        $companyData['logo'] = $fileName;
                    }
                } else {
                    $_SESSION['error'] = 'Logo dosyası çok büyük. Maksimum 2MB olmalıdır.';
                    $this->redirect('/admin/firma-ekle');
                    return;
                }
            } else {
                $_SESSION['error'] = 'Geçersiz dosya formatı. Sadece JPG, PNG veya GIF kabul edilir.';
                $this->redirect('/admin/firma-ekle');
                return;
            }
        }

        $this->companyModel->create($companyData);

        $successMessage = 'Firma başarıyla eklendi.';
        if ($userId) {
            $successMessage .= ' Kullanıcı hesabı da oluşturuldu.';
        }

        $_SESSION['success'] = $successMessage;
        $this->redirect('/admin/firmalar');
    }

    public function articles()
    {
        $articles = $this->articleModel->getAll();

        $this->view('admin.articles', [
            'pageTitle' => 'Makale Yönetimi',
            'articles' => $articles,
        ]);
    }

    public function createArticleForm()
    {
        $cities = $this->cityModel->all();

        $this->view('admin.article-create', [
            'pageTitle' => 'Makale Ekle',
            'cities' => $cities,
        ]);
    }

    public function createArticle()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/makale-ekle');
            return;
        }

        $data = [
            'city_id' => $_POST['city_id'],
            'district_id' => $_POST['district_id'] ?? null,
            'title' => $_POST['title'],
            'slug' => $this->generateSlug($_POST['title']),
            'content' => $_POST['content'],
            'excerpt' => $_POST['excerpt'] ?? null,
            'meta_title' => $_POST['meta_title'] ?? null,
            'meta_description' => $_POST['meta_description'] ?? null,
            'author_id' => $_SESSION['user_id'],
            'is_published' => 1,
            'published_at' => date('Y-m-d H:i:s'),
        ];

        $this->articleModel->create($data);

        $_SESSION['success'] = 'Makale başarıyla eklendi.';
        $this->redirect('/admin/makaleler');
    }

    public function editArticleForm($id)
    {
        $article = $this->articleModel->find($id);

        if (!$article) {
            $_SESSION['error'] = 'Makale bulunamadı.';
            $this->redirect('/admin/makaleler');
            return;
        }

        $cities = $this->cityModel->all();
        $districts = [];

        if ($article['city_id']) {
            $districts = $this->districtModel->getByCity($article['city_id']);
        }

        $this->view('admin.article-edit', [
            'pageTitle' => 'Makale Düzenle',
            'article' => $article,
            'cities' => $cities,
            'districts' => $districts,
        ]);
    }

    public function updateArticle($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/makaleler');
            return;
        }

        $article = $this->articleModel->find($id);

        if (!$article) {
            $_SESSION['error'] = 'Makale bulunamadı.';
            $this->redirect('/admin/makaleler');
            return;
        }

        $data = [
            'city_id' => $_POST['city_id'],
            'district_id' => $_POST['district_id'] ?? null,
            'title' => $_POST['title'],
            'slug' => $this->generateSlug($_POST['title']),
            'content' => $_POST['content'],
            'excerpt' => $_POST['excerpt'] ?? null,
            'meta_title' => $_POST['meta_title'] ?? null,
            'meta_description' => $_POST['meta_description'] ?? null,
            'is_published' => isset($_POST['is_published']) ? 1 : 0,
        ];

        $this->articleModel->update($id, $data);

        $_SESSION['success'] = 'Makale başarıyla güncellendi.';
        $this->redirect('/admin/makaleler');
    }

    /**
     * Silme Talepleri Listesi
     */
    public function deletionRequests()
    {
        $requests = $this->deletionRequestModel->getAll();
        $pendingRequests = $this->deletionRequestModel->getAllPending();

        $this->view('admin.deletion-requests', [
            'pageTitle' => 'Veri Silme Talepleri',
            'requests' => $requests,
            'pendingCount' => count($pendingRequests),
            'totalCount' => count($requests),
        ]);
    }

    /**
     * Silme Talebi Detayı
     */
    public function deletionRequestDetail($id)
    {
        $request = $this->deletionRequestModel->find($id);

        if (!$request) {
            $_SESSION['error'] = 'Talep bulunamadı.';
            $this->redirect('/admin/silme-talepleri');
            return;
        }

        $this->view('admin.deletion-request-detail', [
            'pageTitle' => 'Silme Talebi Detayı #' . $id,
            'request' => $request,
        ]);
    }

    /**
     * Silme Talebini Onayla
     */
    public function approveDeletionRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/silme-talepleri');
            return;
        }

        $requestId = $_POST['request_id'] ?? null;
        $adminNotes = $_POST['admin_notes'] ?? null;

        if (!$requestId) {
            $_SESSION['error'] = 'Geçersiz talep.';
            $this->redirect('/admin/silme-talepleri');
            return;
        }

        $this->deletionRequestModel->process($requestId, 'approved', $_SESSION['user_id'], $adminNotes);

        $_SESSION['success'] = 'Silme talebi onaylandı. Şimdi "Tamamla" butonuna basarak kullanıcı verilerini silebilirsiniz.';
        $this->redirect('/admin/silme-talepleri');
    }

    /**
     * Silme Talebini Reddet
     */
    public function rejectDeletionRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/silme-talepleri');
            return;
        }

        $requestId = $_POST['request_id'] ?? null;
        $adminNotes = $_POST['admin_notes'] ?? null;

        if (!$requestId || !$adminNotes) {
            $_SESSION['error'] = 'Red nedeni belirtmeniz gerekmektedir.';
            $this->redirect('/admin/silme-talepleri');
            return;
        }

        $this->deletionRequestModel->process($requestId, 'rejected', $_SESSION['user_id'], $adminNotes);

        $_SESSION['success'] = 'Silme talebi reddedildi.';
        $this->redirect('/admin/silme-talepleri');
    }

    /**
     * Silme Talebini Tamamla (Kullanıcıyı Sil)
     */
    public function completeDeletionRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/silme-talepleri');
            return;
        }

        $requestId = $_POST['request_id'] ?? null;
        $confirmDeletion = isset($_POST['confirm_deletion']);

        if (!$requestId || !$confirmDeletion) {
            $_SESSION['error'] = 'Silme işlemini onaylamanız gerekmektedir.';
            $this->redirect('/admin/silme-talepleri');
            return;
        }

        $request = $this->deletionRequestModel->find($requestId);

        if (!$request || $request['status'] !== 'approved') {
            $_SESSION['error'] = 'Bu talep henüz onaylanmamış veya bulunamadı.';
            $this->redirect('/admin/silme-talepleri');
            return;
        }

        // Kullanıcıyı kalıcı olarak sil
        $success = $this->deletionRequestModel->permanentlyDeleteUser($request['user_id']);

        if ($success) {
            // Talebi tamamlandı olarak işaretle
            $this->deletionRequestModel->process($requestId, 'completed', $_SESSION['user_id'], 'Kullanıcı hesabı ve tüm verileri başarıyla silindi.');

            $_SESSION['success'] = 'Kullanıcı hesabı ve tüm verileri başarıyla silindi.';
        } else {
            $_SESSION['error'] = 'Kullanıcı silinirken bir hata oluştu.';
        }

        $this->redirect('/admin/silme-talepleri');
    }

    public function companyImportForm()
    {
        $this->view('admin.company-import', [
            'pageTitle' => 'Excel ile Firma Ekle',
        ]);
    }

    public function processCompanyImport()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/firma-import');
            return;
        }

        // Composer autoload
        require_once __DIR__ . '/../vendor/autoload.php';

        // Excel dosyasını kontrol et
        if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Lütfen geçerli bir Excel dosyası yükleyin.';
            $this->redirect('/admin/firma-import');
            return;
        }

        $file = $_FILES['excel_file'];

        // Dosya boyutu kontrolü (5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            $_SESSION['error'] = 'Dosya boyutu çok büyük. Maksimum 5MB olmalıdır.';
            $this->redirect('/admin/firma-import');
            return;
        }

        // Dosya uzantısı kontrolü
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExtension, ['xlsx', 'xls'])) {
            $_SESSION['error'] = 'Sadece .xlsx veya .xls formatında dosyalar kabul edilir.';
            $this->redirect('/admin/firma-import');
            return;
        }

        try {
            // Excel dosyasını oku
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader(ucfirst($fileExtension));
            $spreadsheet = $reader->load($file['tmp_name']);
            $worksheet = $spreadsheet->getActiveSheet();

            // Başlık satırını al (1. satır)
            $headerRow = $worksheet->rangeToArray('A1:' . $worksheet->getHighestColumn() . '1')[0];

            // Boş değerleri temizle
            $headerRow = array_map('trim', $headerRow);
            $headerRow = array_filter($headerRow);

            if (empty($headerRow)) {
                $_SESSION['error'] = 'Excel dosyasında başlık satırı bulunamadı.';
                $this->redirect('/admin/firma-import');
                return;
            }

            // Zorunlu sütunları kontrol et
            $requiredColumns = ['name', 'city_id', 'district_id'];
            $missingColumns = array_diff($requiredColumns, $headerRow);

            if (!empty($missingColumns)) {
                $_SESSION['error'] = 'Eksik zorunlu sütunlar: ' . implode(', ', $missingColumns);
                $this->redirect('/admin/firma-import');
                return;
            }

            // Otomatik onay ayarı
            $autoApprove = isset($_POST['auto_approve']) ? 1 : 0;

            // Verileri işle
            $highestRow = $worksheet->getHighestRow();
            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            for ($row = 2; $row <= $highestRow; $row++) {
                $rowData = $worksheet->rangeToArray('A' . $row . ':' . $worksheet->getHighestColumn() . $row)[0];

                // Boş satırları atla
                if (empty(array_filter($rowData))) {
                    continue;
                }

                // Başlık satırı ile veriyi eşleştir
                $data = array_combine($headerRow, array_slice($rowData, 0, count($headerRow)));

                // Satırı işle
                $result = $this->processCompanyRow($data, $autoApprove, $row);

                if ($result['success']) {
                    $successCount++;
                } else {
                    $errorCount++;
                    $errors[] = "Satır {$row}: " . $result['error'];
                }
            }

            // Sonuç mesajını hazırla
            $message = "{$successCount} firma başarıyla eklendi.";

            if ($errorCount > 0) {
                $message .= " {$errorCount} satırda hata oluştu.";
                $_SESSION['import_errors'] = $errors;
            }

            if ($successCount > 0) {
                $_SESSION['success'] = $message;
            } else {
                $_SESSION['error'] = "Hiçbir firma eklenemedi. Lütfen Excel dosyanızı kontrol edin.";
            }

            $this->redirect('/admin/firma-import-sonuc');
        } catch (Exception $e) {
            $_SESSION['error'] = 'Excel dosyası işlenirken bir hata oluştu: ' . $e->getMessage();
            $this->redirect('/admin/firma-import');
        }
    }

    private function processCompanyRow($data, $autoApprove, $rowNumber)
    {
        try {
            // Zorunlu alanları kontrol et
            if (empty($data['name'])) {
                return ['success' => false, 'error' => 'Firma adı boş olamaz'];
            }

            if (empty($data['city_id'])) {
                return ['success' => false, 'error' => 'Şehir adı boş olamaz'];
            }

            if (empty($data['district_id'])) {
                return ['success' => false, 'error' => 'İlçe adı boş olamaz'];
            }

            // Şehir adından ID bul
            $cityName = trim($data['city_id']);
            $city = $this->cityModel->findBy('name', $cityName);

            if (!$city) {
                return ['success' => false, 'error' => "Şehir bulunamadı: {$cityName}"];
            }

            // İlçe adından ID bul
            $districtName = trim($data['district_id']);
            $district = $this->districtModel->findByNameAndCity($districtName, $city['id']);

            if (!$district) {
                return ['success' => false, 'error' => "İlçe bulunamadı: {$districtName} ({$cityName})"];
            }

            // İl-ilçe doğrulaması
            if ($district['city_id'] != $city['id']) {
                return ['success' => false, 'error' => "{$districtName} ilçesi {$cityName} iline ait değil"];
            }

            // Firma verisini hazırla
            $companyData = [
                'name' => trim($data['name']),
                'slug' => $this->generateSlug($data['name']),
                'city_id' => $city['id'],
                'district_id' => $district['id'],
                'phone' => isset($data['phone']) ? trim($data['phone']) : null,
                'address' => isset($data['address']) ? trim($data['address']) : null,
                'email' => isset($data['email']) ? trim($data['email']) : null,
                'website' => isset($data['website']) ? trim($data['website']) : null,
                'description' => isset($data['description']) ? trim($data['description']) : null,
                'is_approved' => $autoApprove,
                'user_id' => null,
            ];

            // Firmayı veritabanına ekle
            $companyId = $this->companyModel->create($companyData);

            if (!$companyId) {
                return ['success' => false, 'error' => 'Firma veritabanına eklenirken hata oluştu'];
            }

            return ['success' => true, 'company_id' => $companyId];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function companyImportResult()
    {
        $errors = $_SESSION['import_errors'] ?? [];
        unset($_SESSION['import_errors']);

        $this->view('admin.company-import-result', [
            'pageTitle' => 'İçe Aktarma Sonucu',
            'errors' => $errors,
        ]);
    }

    /**
     * Kullanıcı Oluşturma Formu
     */
    public function createUserForm()
    {
        // Tüm firmaları getir (kullanıcı atamak için)
        $companies = $this->companyModel->getAll();

        $this->view('admin.user-create', [
            'pageTitle' => 'Kullanıcı Ekle',
            'companies' => $companies,
        ]);
    }

    /**
     * Kullanıcı Oluşturma İşlemi
     */
    public function createUser()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/kullanici-ekle');
            return;
        }

        $userEmail = $_POST['user_email'] ?? '';
        $fullName = $_POST['full_name'] ?? '';
        $password = $_POST['password'] ?? '';
        $companyId = $_POST['company_id'] ?? null;

        // Zorunlu alanları kontrol et
        if (empty($userEmail) || empty($fullName) || empty($password)) {
            $_SESSION['error'] = 'E-posta, Tam Ad ve Şifre alanları zorunludur.';
            $this->redirect('/admin/kullanici-ekle');
            return;
        }

        // Şifre uzunluğu kontrolü
        if (strlen($password) < 6) {
            $_SESSION['error'] = 'Şifre en az 6 karakter olmalıdır.';
            $this->redirect('/admin/kullanici-ekle');
            return;
        }

        // E-posta kontrolü
        require_once __DIR__ . '/../models/User.php';
        $userModel = new User();

        if ($userModel->findByEmail($userEmail)) {
            $_SESSION['error'] = 'Bu e-posta adresi zaten kullanılıyor.';
            $this->redirect('/admin/kullanici-ekle');
            return;
        }

        // Kullanıcı oluştur
        $userData = [
            'email' => $userEmail,
            'password' => $password,
            'full_name' => $fullName,
            'role' => 'company', // Otomatik olarak company rolü
            'is_active' => isset($_POST['user_is_active']) ? 1 : 0,
        ];

        $userId = $userModel->register($userData);

        if (!$userId) {
            $_SESSION['error'] = 'Kullanıcı oluşturulurken bir hata oluştu.';
            $this->redirect('/admin/kullanici-ekle');
            return;
        }

        // Firma seçilmişse firmaya kullanıcı ata
        if (!empty($companyId)) {
            $company = $this->companyModel->find($companyId);

            if ($company) {
                // Firmaya kullanıcı ID'sini ata
                $this->companyModel->update($companyId, [
                    'user_id' => $userId,
                ]);

                $_SESSION['success'] = 'Kullanıcı başarıyla oluşturuldu ve firmaya atandı.';
            } else {
                $_SESSION['success'] = 'Kullanıcı başarıyla oluşturuldu ancak firma bulunamadı.';
            }
        } else {
            $_SESSION['success'] = 'Kullanıcı başarıyla oluşturuldu.';
        }

        $this->redirect('/admin/kullanici-ekle');
    }

    /**
     * Firma Silme İşlemi
     */
    public function deleteCompany($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/firmalar');
            return;
        }

        $company = $this->companyModel->find($id);

        if (!$company) {
            $_SESSION['error'] = 'Firma bulunamadı.';
            $this->redirect('/admin/firmalar');
            return;
        }

        // Logo dosyasını sil
        if ($company['logo']) {
            $logoPath = __DIR__ . '/../public/uploads/logos/' . $company['logo'];
            if (file_exists($logoPath)) {
                unlink($logoPath);
            }
        }

        // Firmayı sil
        if ($this->companyModel->delete($id)) {
            $_SESSION['success'] = 'Firma başarıyla silindi.';
        } else {
            $_SESSION['error'] = 'Firma silinirken bir hata oluştu.';
        }

        $this->redirect('/admin/firmalar');
    }

    /**
     * Makale Silme İşlemi
     */
    public function deleteArticle($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/makaleler');
            return;
        }

        $article = $this->articleModel->find($id);

        if (!$article) {
            $_SESSION['error'] = 'Makale bulunamadı.';
            $this->redirect('/admin/makaleler');
            return;
        }

        // Makaleyi sil
        if ($this->articleModel->delete($id)) {
            $_SESSION['success'] = 'Makale başarıyla silindi.';
        } else {
            $_SESSION['error'] = 'Makale silinirken bir hata oluştu.';
        }

        $this->redirect('/admin/makaleler');
    }

    /**
     * Kullanıcı Listesi
     */
    public function users()
    {
        $users = $this->userModel->getAllWithCompany();

        $this->view('admin.users', [
            'pageTitle' => 'Kullanıcı Yönetimi',
            'users' => $users,
        ]);
    }

    /**
     * Kullanıcı Silme İşlemi
     */
    public function deleteUser($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/kullanicilar');
            return;
        }

        $user = $this->userModel->find($id);

        if (!$user) {
            $_SESSION['error'] = 'Kullanıcı bulunamadı.';
            $this->redirect('/admin/kullanicilar');
            return;
        }

        // Admin kullanıcılarını silmeyi engelle
        if ($user['role'] === 'admin') {
            $_SESSION['error'] = 'Admin kullanıcıları silinemez.';
            $this->redirect('/admin/kullanicilar');
            return;
        }

        // Kullanıcıya bağlı firmaları kontrol et
        if ($user['id']) {
            $companies = $this->companyModel->getByUserId($user['id']);
            if (!empty($companies)) {
                $_SESSION['error'] = 'Bu kullanıcıya bağlı firmalar var. Önce firmaları silmelisiniz.';
                $this->redirect('/admin/kullanicilar');
                return;
            }
        }

        // Kullanıcıyı sil
        if ($this->userModel->delete($id)) {
            $_SESSION['success'] = 'Kullanıcı başarıyla silindi.';
        } else {
            $_SESSION['error'] = 'Kullanıcı silinirken bir hata oluştu.';
        }

        $this->redirect('/admin/kullanicilar');
    }

    private function generateSlug($text)
    {
        $turkish = ['Ç', 'Ş', 'Ğ', 'Ü', 'İ', 'Ö', 'ç', 'ş', 'ğ', 'ü', 'ı', 'ö'];
        $english = ['C', 'S', 'G', 'U', 'I', 'O', 'c', 's', 'g', 'u', 'i', 'o'];
        $text = str_replace($turkish, $english, $text);
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9-]/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }
}
