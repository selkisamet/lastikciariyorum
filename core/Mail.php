<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../phpmailer/src/Exception.php';
require_once __DIR__ . '/../phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/src/SMTP.php';

class Mail
{
    private $mailer;
    private $config;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../config/mail.php';
        $this->mailer = new PHPMailer(true);
        $this->setupSMTP();
    }

    private function setupSMTP()
    {
        try {
            // SMTP ayarları
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['smtp']['host'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->config['smtp']['username'];
            $this->mailer->Password = $this->config['smtp']['password'];
            $this->mailer->SMTPSecure = $this->config['smtp']['encryption'];
            $this->mailer->Port = $this->config['smtp']['port'];

            // Karakter seti
            $this->mailer->CharSet = $this->config['charset'];

            // Debug modu
            if ($this->config['debug']) {
                $this->mailer->SMTPDebug = 2;
            }

            // Türkçe dil desteği
            $this->mailer->setLanguage('tr', __DIR__ . '/../phpmailer/language/');

            // Gönderen bilgileri
            $this->mailer->setFrom(
                $this->config['from']['address'],
                $this->config['from']['name']
            );

        } catch (Exception $e) {
            error_log("Mail setup error: " . $e->getMessage());
            throw new Exception("E-posta ayarları yapılandırılamadı.");
        }
    }

    /**
     * Basit e-posta gönderme
     */
    public function send($to, $subject, $body, $isHTML = true)
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->isHTML($isHTML);
            $this->mailer->Body = $body;

            if ($isHTML) {
                $this->mailer->AltBody = strip_tags($body);
            }

            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Mail send error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * İletişim formu e-postası
     */
    public function sendContactForm($name, $email, $subject, $message)
    {
        $emailBody = $this->getContactFormTemplate($name, $email, $subject, $message);

        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            // Yönetici e-postasına gönder
            $this->mailer->addAddress($this->config['contact']['to']);

            // Gönderen kişiye yanıt verebilmek için Reply-To ekle
            $this->mailer->addReplyTo($email, $name);

            $this->mailer->Subject = $this->config['contact']['subject'] . ' - ' . $subject;
            $this->mailer->isHTML(true);
            $this->mailer->Body = $emailBody;
            $this->mailer->AltBody = strip_tags($emailBody);

            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Contact form mail error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Firma onay bildirimi
     */
    public function sendCompanyApprovalNotification($companyEmail, $companyName)
    {
        $emailBody = $this->getCompanyApprovalTemplate($companyName);

        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            $this->mailer->addAddress($companyEmail);
            $this->mailer->Subject = 'Firmanız Onaylandı - Lastikciariyorum.com';
            $this->mailer->isHTML(true);
            $this->mailer->Body = $emailBody;
            $this->mailer->AltBody = strip_tags($emailBody);

            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Company approval mail error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Şifre sıfırlama e-postası
     */
    public function sendPasswordReset($email, $resetToken)
    {
        $resetLink = "https://lastikciariyorum.com/sifre-sifirla?token=" . $resetToken;
        $emailBody = $this->getPasswordResetTemplate($resetLink);

        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            $this->mailer->addAddress($email);
            $this->mailer->Subject = 'Şifre Sıfırlama Talebi - Lastikciariyorum.com';
            $this->mailer->isHTML(true);
            $this->mailer->Body = $emailBody;
            $this->mailer->AltBody = strip_tags($emailBody);

            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Password reset mail error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * İletişim formu e-posta şablonu
     */
    private function getContactFormTemplate($name, $email, $subject, $message)
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2563eb; color: white; padding: 20px; text-align: center; }
                .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
                .info-row { margin: 15px 0; padding: 10px; background: white; border-left: 4px solid #2563eb; }
                .label { font-weight: bold; color: #1e40af; }
                .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>🔧 Yeni İletişim Formu Mesajı</h2>
                </div>
                <div class='content'>
                    <div class='info-row'>
                        <span class='label'>İsim:</span><br>
                        {$name}
                    </div>
                    <div class='info-row'>
                        <span class='label'>E-posta:</span><br>
                        <a href='mailto:{$email}'>{$email}</a>
                    </div>
                    <div class='info-row'>
                        <span class='label'>Konu:</span><br>
                        {$subject}
                    </div>
                    <div class='info-row'>
                        <span class='label'>Mesaj:</span><br>
                        " . nl2br(htmlspecialchars($message)) . "
                    </div>
                    <div class='info-row'>
                        <span class='label'>Gönderim Tarihi:</span><br>
                        " . date('d.m.Y H:i') . "
                    </div>
                </div>
                <div class='footer'>
                    <p>Bu e-posta Lastikciariyorum.com iletişim formu üzerinden gönderilmiştir.</p>
                    <p>Yanıtlamak için direkt olarak gönderenin e-posta adresini kullanabilirsiniz.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Firma onay e-posta şablonu
     */
    private function getCompanyApprovalTemplate($companyName)
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #10b981; color: white; padding: 20px; text-align: center; }
                .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
                .success-icon { font-size: 48px; text-align: center; margin: 20px 0; }
                .button { display: inline-block; background: #2563eb; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>✅ Firmanız Onaylandı!</h2>
                </div>
                <div class='content'>
                    <div class='success-icon'>🎉</div>
                    <h3 style='text-align: center; color: #10b981;'>Tebrikler {$companyName}!</h3>
                    <p>Firmanız Lastikciariyorum.com'da yayına alınmıştır.</p>
                    <p>Artık müşteriler sizi kolayca bulabilecek ve iletişime geçebilecek.</p>
                    <p style='text-align: center;'>
                        <a href='https://lastikciariyorum.com/firma-paneli' class='button'>Firma Panelinize Gidin</a>
                    </p>
                    <p><strong>Firma panelinizden yapabilecekleriniz:</strong></p>
                    <ul>
                        <li>Firma bilgilerinizi güncelleyebilirsiniz</li>
                        <li>İletişim bilgilerinizi değiştirebilirsiniz</li>
                        <li>Çalışma saatlerinizi düzenleyebilirsiniz</li>
                    </ul>
                </div>
                <div class='footer'>
                    <p>© 2024 Lastikciariyorum.com - Türkiye'nin Lastik Tamircisi Rehberi</p>
                    <p>Bu bir otomatik e-postadır, lütfen yanıtlamayın.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Şifre sıfırlama e-posta şablonu
     */
    private function getPasswordResetTemplate($resetLink)
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #ef4444; color: white; padding: 20px; text-align: center; }
                .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
                .button { display: inline-block; background: #2563eb; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; color: #6b7280; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>🔒 Şifre Sıfırlama Talebi</h2>
                </div>
                <div class='content'>
                    <p>Merhaba,</p>
                    <p>Lastikciariyorum.com hesabınız için şifre sıfırlama talebinde bulundunuz.</p>
                    <p style='text-align: center;'>
                        <a href='{$resetLink}' class='button'>Şifremi Sıfırla</a>
                    </p>
                    <p>Veya aşağıdaki linki tarayıcınıza kopyalayın:</p>
                    <p style='background: #f3f4f6; padding: 10px; word-break: break-all; font-size: 12px;'>{$resetLink}</p>
                    <div class='warning'>
                        <strong>⚠️ Güvenlik Uyarısı:</strong><br>
                        Bu link 24 saat geçerlidir. Eğer bu talebi siz yapmadıysanız, bu e-postayı görmezden gelebilirsiniz.
                    </div>
                </div>
                <div class='footer'>
                    <p>© 2024 Lastikciariyorum.com</p>
                    <p>Bu bir otomatik e-postadır, lütfen yanıtlamayın.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
