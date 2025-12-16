<?php

require_once __DIR__ . '/../core/Controller.php';

class PageController extends Controller
{
    /**
     * Çerez Politikası sayfası
     */
    public function cookiePolicy()
    {
        $this->view('pages.cookie-policy', [
            'pageTitle' => 'Çerez Politikası - Lastikciariyorum.com',
            'metaDescription' => 'Lastikciariyorum.com çerez politikası ve kullanım detayları'
        ]);
    }

    /**
     * Gizlilik Politikası sayfası
     */
    public function privacyPolicy()
    {
        $this->view('pages.privacy-policy', [
            'pageTitle' => 'Gizlilik Politikası - Lastikciariyorum.com',
            'metaDescription' => 'Lastikciariyorum.com gizlilik politikası ve kişisel veri koruma'
        ]);
    }

    /**
     * Hakkımızda sayfası
     */
    public function about()
    {
        $this->view('pages.about', [
            'pageTitle' => 'Hakkımızda - Lastikciariyorum.com',
            'metaDescription' => 'Lastikciariyorum.com hakkında bilgi'
        ]);
    }

    /**
     * İletişim sayfası
     */
    public function contact()
    {
        $this->view('pages.contact', [
            'pageTitle' => 'İletişim - Lastikciariyorum.com',
            'metaDescription' => 'Lastikciariyorum.com ile iletişime geçin'
        ]);
    }

    /**
     * İletişim formu gönderimi
     */
    public function submitContact()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/iletisim');
            return;
        }

        // Form verilerini al
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $consent = isset($_POST['contact_consent']);

        // Validasyon
        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            $_SESSION['error'] = 'Lütfen tüm zorunlu alanları doldurun.';
            $this->redirect('/iletisim');
            return;
        }

        // E-posta formatı kontrolü
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Geçerli bir e-posta adresi girin.';
            $this->redirect('/iletisim');
            return;
        }

        // KVKK onayı kontrolü
        if (!$consent) {
            $_SESSION['error'] = 'İletişim bilgilerinizin işlenmesi için onay vermeniz gerekmektedir.';
            $this->redirect('/iletisim');
            return;
        }

        // E-posta gönderimi
        try {
            $mail = new Mail();
            $sent = $mail->sendContactForm($name, $email, $subject, $message);

            if ($sent) {
                $_SESSION['success'] = 'Mesajınız başarıyla gönderildi. En kısa sürede size dönüş yapacağız.';
            } else {
                $_SESSION['error'] = 'E-posta gönderilirken bir hata oluştu. Lütfen daha sonra tekrar deneyin.';
            }
        } catch (Exception $e) {
            error_log("Contact form error: " . $e->getMessage());
            $_SESSION['error'] = 'Bir hata oluştu. Lütfen daha sonra tekrar deneyin.';
        }

        $this->redirect('/iletisim');
    }

    /**
     * KVKK Aydınlatma Metni sayfası
     */
    public function kvkkAydinlatma()
    {
        $this->view('pages.kvkk-aydinlatma', [
            'pageTitle' => 'KVKK Aydınlatma Metni - Lastikciariyorum.com',
            'metaDescription' => 'Kişisel Verilerin Korunması Kanunu kapsamında aydınlatma metni'
        ]);
    }
}
