<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/User.php';

class AuthController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function loginForm()
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/');
            return;
        }

        $this->view('auth.login', [
            'pageTitle' => 'Giriş Yap',
        ]);
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/login');
            return;
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $user = $this->userModel->verify($email, $password);

        if (!$user) {
            $_SESSION['error'] = 'E-posta veya şifre hatalı.';
            $this->redirect('/login');
            return;
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role'];

        if ($user['role'] === 'admin') {
            $this->redirect('/admin');
        } else {
            $this->redirect('/');
        }
    }

    public function registerForm()
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/');
            return;
        }

        $this->view('auth.register', [
            'pageTitle' => 'Kayıt Ol',
        ]);
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/register');
            return;
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $fullName = $_POST['full_name'] ?? '';

        // Validation
        if (empty($email) || empty($password) || empty($fullName)) {
            $_SESSION['error'] = 'Lütfen tüm alanları doldurun.';
            $this->redirect('/register');
            return;
        }

        if ($password !== $confirmPassword) {
            $_SESSION['error'] = 'Şifreler eşleşmiyor.';
            $this->redirect('/register');
            return;
        }

        if (strlen($password) < 6) {
            $_SESSION['error'] = 'Şifre en az 6 karakter olmalıdır.';
            $this->redirect('/register');
            return;
        }

        // Check if email exists
        if ($this->userModel->findByEmail($email)) {
            $_SESSION['error'] = 'Bu e-posta adresi zaten kullanılıyor.';
            $this->redirect('/register');
            return;
        }

        $data = [
            'email' => $email,
            'password' => $password,
            'full_name' => $fullName,
            'role' => 'user',
        ];

        $userId = $this->userModel->register($data);

        $_SESSION['success'] = 'Kayıt başarılı. Giriş yapabilirsiniz.';
        $this->redirect('/login');
    }

    public function logout()
    {
        session_destroy();
        $this->redirect('/');
    }

    public function forgotPasswordForm()
    {
        $this->view('auth.forgot-password', [
            'pageTitle' => 'Şifremi Unuttum',
        ]);
    }

    public function forgotPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/forgot-password');
            return;
        }

        $email = $_POST['email'] ?? '';

        $token = $this->userModel->createResetToken($email);

        if ($token) {
            // TODO: Send email with reset link
            $_SESSION['success'] = 'Şifre sıfırlama bağlantısı e-posta adresinize gönderildi.';
        } else {
            $_SESSION['error'] = 'E-posta adresi bulunamadı.';
        }

        $this->redirect('/forgot-password');
    }

    public function resetPasswordForm($token)
    {
        $user = $this->userModel->verifyResetToken($token);

        if (!$user) {
            $_SESSION['error'] = 'Geçersiz veya süresi dolmuş token.';
            $this->redirect('/login');
            return;
        }

        $this->view('auth.reset-password', [
            'pageTitle' => 'Şifre Sıfırla',
            'token' => $token,
        ]);
    }

    public function resetPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/login');
            return;
        }

        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($password !== $confirmPassword) {
            $_SESSION['error'] = 'Şifreler eşleşmiyor.';
            $this->redirect('/reset-password/' . $token);
            return;
        }

        if (strlen($password) < 6) {
            $_SESSION['error'] = 'Şifre en az 6 karakter olmalıdır.';
            $this->redirect('/reset-password/' . $token);
            return;
        }

        $result = $this->userModel->resetPassword($token, $password);

        if ($result) {
            $_SESSION['success'] = 'Şifreniz başarıyla sıfırlandı. Giriş yapabilirsiniz.';
            $this->redirect('/login');
        } else {
            $_SESSION['error'] = 'Geçersiz veya süresi dolmuş token.';
            $this->redirect('/login');
        }
    }
}
