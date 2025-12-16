<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/City.php';
require_once __DIR__ . '/../models/District.php';
require_once __DIR__ . '/../models/Company.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/ViewLog.php';
require_once __DIR__ . '/../models/UserConsent.php';
require_once __DIR__ . '/../models/DeletionRequest.php';

class CompanyController extends Controller
{
    private $cityModel;
    private $districtModel;
    private $companyModel;
    private $userModel;
    private $viewLogModel;
    private $userConsentModel;
    private $deletionRequestModel;

    public function __construct()
    {
        $this->cityModel = new City();
        $this->districtModel = new District();
        $this->companyModel = new Company();
        $this->userModel = new User();
        $this->viewLogModel = new ViewLog();
        $this->userConsentModel = new UserConsent();
        $this->deletionRequestModel = new DeletionRequest();
    }

    public function show($citySlug, $districtSlug, $companySlug)
    {
        $city = $this->cityModel->findBySlug($citySlug);

        if (!$city) {
            http_response_code(404);
            echo "İl bulunamadı";
            return;
        }

        $district = null;
        $districtId = null;

        if ($districtSlug) {
            $district = $this->districtModel->findBySlug($city['id'], $districtSlug);

            if (!$district) {
                http_response_code(404);
                echo "İlçe bulunamadı";
                return;
            }

            $districtId = $district['id'];
        }

        $company = $this->companyModel->findBySlug($companySlug, $city['id'], $districtId);

        if (!$company) {
            http_response_code(404);
            echo "Firma bulunamadı";
            return;
        }

        // IP bazlı güvenli görüntülenme sayacı
        $ipAddress = ViewLog::getClientIp();
        $userAgent = ViewLog::getUserAgent();

        // Son 24 saat içinde aynı IP'den görüntülenme yoksa say
        if (!$this->viewLogModel->hasRecentView('company', $company['id'], $ipAddress, 24)) {
            // Görüntülenme kaydını oluştur
            $this->viewLogModel->logView('company', $company['id'], $ipAddress, $userAgent);
            // Sayacı artır
            $this->companyModel->incrementViewCount($company['id']);
        }

        // Schema.org yapılandırılmış veri
        $organizationSchema = [
            'name' => $company['name'],
            'phone' => $company['phone'],
            'address' => $company['address'],
            'district' => $district['name'] ?? '',
            'city' => $city['name'],
            'url' => 'https://lastikciariyorum.com/' . $citySlug . '/' . $districtSlug . '/firma/' . $companySlug,
            'weekday_open' => $company['weekday_open'] ?? '08:00',
            'weekday_close' => $company['weekday_close'] ?? '19:00',
            'saturday_open' => $company['saturday_open'] ?? '09:00',
            'saturday_close' => $company['saturday_close'] ?? '18:00',
        ];

        // Breadcrumb Schema
        $breadcrumbSchema = [
            ['name' => 'Ana Sayfa', 'url' => 'https://lastikciariyorum.com/'],
            ['name' => $city['name'], 'url' => 'https://lastikciariyorum.com/' . $citySlug],
            ['name' => $district['name'], 'url' => 'https://lastikciariyorum.com/' . $citySlug . '/' . $districtSlug],
            ['name' => $company['name'], 'url' => 'https://lastikciariyorum.com/' . $citySlug . '/' . $districtSlug . '/firma/' . $companySlug],
        ];

        $this->view('company.show', [
            'city' => $city,
            'district' => $district,
            'company' => $company,
            'pageTitle' => $company['name'] . ' - ' . $city['name'] . ' Lastik Tamircisi',
            'metaDescription' => $company['description'] ?? $company['name'] . ' lastik tamir servisi hakkında bilgiler.',
            'organizationSchema' => $organizationSchema,
            'breadcrumbSchema' => $breadcrumbSchema,
        ]);
    }

    /**
     * Firma Ekle Formu (Kayıt + Firma Bilgileri)
     * Kullanıcı girişi YAPMAMIŞ olmalı
     */
    public function createForm()
    {
        // Eğer kullanıcı giriş yapmışsa firma ekleyemez, panele yönlendir
        if (isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Zaten giriş yapmışsınız. Firma eklemek için firma panelinizi kullanın.';
            $this->redirect('/firma-paneli');
            return;
        }

        $cities = $this->cityModel->all();

        $this->view('company.create', [
            'cities' => $cities,
            'pageTitle' => 'Firma Ekle - Kayıt Ol',
        ]);
    }

    /**
     * Firma Ekleme (Kullanıcı Kaydı + Firma Oluşturma)
     */
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/firma-ekle');
            return;
        }

        // Validation
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $fullName = $_POST['full_name'] ?? '';
        $companyName = $_POST['company_name'] ?? '';
        $cityId = $_POST['city_id'] ?? '';
        $districtId = $_POST['district_id'] ?? '';
        $kvkkConsent = isset($_POST['kvkk_consent']) ? 1 : 0;
        $marketingConsent = isset($_POST['marketing_consent']) ? 1 : 0;

        if (empty($email) || empty($password) || empty($fullName) || empty($companyName) || empty($cityId) || empty($districtId)) {
            $_SESSION['error'] = 'Lütfen tüm zorunlu alanları doldurun.';
            $this->redirect('/firma-ekle');
            return;
        }

        // KVKK onayı zorunludur
        if (!$kvkkConsent) {
            $_SESSION['error'] = 'KVKK Aydınlatma Metni onayı zorunludur.';
            $this->redirect('/firma-ekle');
            return;
        }

        if (strlen($password) < 6) {
            $_SESSION['error'] = 'Şifre en az 6 karakter olmalıdır.';
            $this->redirect('/firma-ekle');
            return;
        }

        // E-posta kontrolü
        if ($this->userModel->findByEmail($email)) {
            $_SESSION['error'] = 'Bu e-posta adresi zaten kullanılıyor.';
            $this->redirect('/firma-ekle');
            return;
        }

        // Kullanıcı oluştur
        $userData = [
            'email' => $email,
            'password' => $password,
            'full_name' => $fullName,
            'role' => 'company',
            'is_active' => 0,  // Admin onayından sonra aktif olacak
        ];

        $userId = $this->userModel->register($userData);

        // Firma oluştur
        $companyData = [
            'user_id' => $userId,
            'city_id' => $cityId,
            'district_id' => $_POST['district_id'] ?? null,
            'name' => $companyName,
            'slug' => $this->generateSlug($companyName),
            'phone' => $_POST['phone'] ?? null,
            'address' => $_POST['address'] ?? null,
            'description' => $_POST['description'] ?? null,
            'website' => $_POST['website'] ?? null,
            'email' => $email,
            'is_approved' => 0,
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
                    $this->redirect('/firma-ekle');
                    return;
                }
            } else {
                $_SESSION['error'] = 'Geçersiz dosya formatı. Sadece JPG, PNG veya GIF kabul edilir.';
                $this->redirect('/firma-ekle');
                return;
            }
        }

        $this->companyModel->create($companyData);

        // KVKK ve pazarlama onaylarını kaydet
        $ipAddress = UserConsent::getClientIp();
        $this->userConsentModel->create([
            'user_id' => $userId,
            'kvkk_consent' => $kvkkConsent,
            'marketing_consent' => $marketingConsent,
            'ip_address' => $ipAddress,
        ]);

        $_SESSION['success'] = 'Firma kaydınız alındı! Admin onayından sonra giriş yapabileceksiniz.';
        $this->redirect('/login');
    }

    /**
     * Firma Paneli (Sadece company rolü erişebilir)
     */
    public function dashboard()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }

        // Sadece company rolü erişebilir
        if ($_SESSION['user_role'] !== 'company') {
            $_SESSION['error'] = 'Bu sayfaya erişim yetkiniz yok.';
            $this->redirect('/');
            return;
        }

        $companies = $this->companyModel->getByUserId($_SESSION['user_id']);

        $this->view('company.dashboard', [
            'companies' => $companies,
            'pageTitle' => 'Firma Paneli',
        ]);
    }

    /**
     * Firma Güncelleme Formu
     */
    public function editForm($companyId)
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'company') {
            $this->redirect('/login');
            return;
        }

        $company = $this->companyModel->find($companyId);

        if (!$company || $company['user_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = 'Firma bulunamadı veya size ait değil.';
            $this->redirect('/firma-paneli');
            return;
        }

        $cities = $this->cityModel->all();
        $districts = [];

        if ($company['city_id']) {
            $districts = $this->districtModel->getByCity($company['city_id']);
        }

        // Kullanıcı bilgilerini getir
        require_once __DIR__ . '/../models/User.php';
        $userModel = new User();
        $user = $userModel->find($_SESSION['user_id']);

        $this->view('company.edit', [
            'company' => $company,
            'user' => $user,
            'cities' => $cities,
            'districts' => $districts,
            'pageTitle' => 'Firma Düzenle',
        ]);
    }

    /**
     * Firma Güncelleme
     */
    public function update($companyId)
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'company') {
            $this->redirect('/login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/firma-paneli');
            return;
        }

        $company = $this->companyModel->find($companyId);

        if (!$company || $company['user_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = 'Firma bulunamadı veya size ait değil.';
            $this->redirect('/firma-paneli');
            return;
        }

        // Kullanıcı bilgilerini güncelle
        require_once __DIR__ . '/../models/User.php';
        $userModel = new User();

        $userData = [
            'email' => $_POST['user_email'],
            'full_name' => $_POST['full_name'],
        ];

        // E-posta değiştirilmişse, başka kullanıcı tarafından kullanılmadığını kontrol et
        $existingUser = $userModel->findByEmail($_POST['user_email']);
        if ($existingUser && $existingUser['id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = 'Bu e-posta adresi zaten kullanılıyor.';
            $this->redirect('/firma-duzenle/' . $companyId);
            return;
        }

        // Yeni şifre girildiyse güncelle
        if (!empty($_POST['new_password'])) {
            if (strlen($_POST['new_password']) < 6) {
                $_SESSION['error'] = 'Şifre en az 6 karakter olmalıdır.';
                $this->redirect('/firma-duzenle/' . $companyId);
                return;
            }
            $userData['password'] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        }

        $userModel->update($_SESSION['user_id'], $userData);

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
                        // Eski logoyu sil (varsa)
                        if ($company['logo'] && file_exists($uploadDir . $company['logo'])) {
                            unlink($uploadDir . $company['logo']);
                        }

                        $data['logo'] = $fileName;
                    }
                } else {
                    $_SESSION['error'] = 'Logo dosyası çok büyük. Maksimum 2MB olmalıdır.';
                    $this->redirect('/firma-duzenle/' . $companyId);
                    return;
                }
            } else {
                $_SESSION['error'] = 'Geçersiz dosya formatı. Sadece JPG, PNG veya GIF kabul edilir.';
                $this->redirect('/firma-duzenle/' . $companyId);
                return;
            }
        }

        $this->companyModel->update($companyId, $data);

        $_SESSION['success'] = 'Kullanıcı ve firma bilgileriniz başarıyla güncellendi.';
        $this->redirect('/firma-paneli');
    }

    /**
     * Firma Kaldırma Talebi
     */
    public function requestRemoval($companyId)
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'company') {
            $this->redirect('/login');
            return;
        }

        $company = $this->companyModel->find($companyId);

        if (!$company || $company['user_id'] != $_SESSION['user_id']) {
            $_SESSION['error'] = 'Firma bulunamadı veya size ait değil.';
            $this->redirect('/firma-paneli');
            return;
        }

        // Firma onayını kaldır
        $this->companyModel->update($companyId, ['is_approved' => 0]);

        $_SESSION['success'] = 'Firmanızın kaldırılma talebi alındı. Admin inceleyecektir.';
        $this->redirect('/firma-paneli');
    }

    /**
     * Hesabı Kalıcı Olarak Sil
     */
    public function deleteAccount()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'company') {
            $this->redirect('/login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/firma-paneli');
            return;
        }

        $confirmDelete = isset($_POST['confirm_delete']);
        $reason = $_POST['reason'] ?? null;

        if (!$confirmDelete) {
            $_SESSION['error'] = 'Hesap silme işlemini onaylamanız gerekmektedir.';
            $this->redirect('/firma-paneli');
            return;
        }

        // Bekleyen silme talebi var mı kontrol et
        $existingRequest = $this->deletionRequestModel->getPendingByUserId($_SESSION['user_id']);
        if ($existingRequest) {
            $_SESSION['error'] = 'Zaten beklemede olan bir silme talebiniz bulunmaktadır.';
            $this->redirect('/firma-paneli');
            return;
        }

        // Silme talebini oluştur
        $ipAddress = DeletionRequest::getClientIp();
        $this->deletionRequestModel->create([
            'user_id' => $_SESSION['user_id'],
            'request_type' => 'account_deletion',
            'reason' => $reason,
            'ip_address' => $ipAddress,
        ]);

        // Kullanıcıyı çıkış yap
        $userId = $_SESSION['user_id'];
        session_destroy();
        session_start();

        $_SESSION['success'] = 'Hesap silme talebiniz alındı. Talebiniz incelenecek ve 1-2 iş günü içinde hesabınız silinecektir.';
        $this->redirect('/');
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