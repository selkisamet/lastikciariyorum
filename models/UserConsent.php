<?php

require_once __DIR__ . '/../core/Database.php';

class UserConsent
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Kullanıcı için onay kaydı oluştur
     */
    public function create($data)
    {
        $sql = "INSERT INTO user_consents (
                    user_id,
                    kvkk_consent,
                    kvkk_consent_date,
                    kvkk_consent_ip,
                    marketing_consent,
                    marketing_consent_date,
                    marketing_consent_ip
                ) VALUES (
                    :user_id,
                    :kvkk_consent,
                    :kvkk_consent_date,
                    :kvkk_consent_ip,
                    :marketing_consent,
                    :marketing_consent_date,
                    :marketing_consent_ip
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $data['user_id'],
            'kvkk_consent' => $data['kvkk_consent'] ? 1 : 0,
            'kvkk_consent_date' => $data['kvkk_consent'] ? date('Y-m-d H:i:s') : null,
            'kvkk_consent_ip' => $data['kvkk_consent'] ? ($data['ip_address'] ?? null) : null,
            'marketing_consent' => $data['marketing_consent'] ? 1 : 0,
            'marketing_consent_date' => $data['marketing_consent'] ? date('Y-m-d H:i:s') : null,
            'marketing_consent_ip' => $data['marketing_consent'] ? ($data['ip_address'] ?? null) : null,
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Kullanıcının onay kaydını getir
     */
    public function getByUserId($userId)
    {
        $sql = "SELECT * FROM user_consents WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Pazarlama onayını güncelle
     */
    public function updateMarketingConsent($userId, $consent, $ipAddress = null)
    {
        $sql = "UPDATE user_consents SET
                    marketing_consent = :consent,
                    marketing_consent_date = :consent_date,
                    marketing_consent_ip = :ip_address,
                    marketing_revoked_date = :revoked_date
                WHERE user_id = :user_id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'consent' => $consent ? 1 : 0,
            'consent_date' => $consent ? date('Y-m-d H:i:s') : null,
            'ip_address' => $consent ? $ipAddress : null,
            'revoked_date' => !$consent ? date('Y-m-d H:i:s') : null,
            'user_id' => $userId,
        ]);
    }

    /**
     * KVKK onayını kontrol et
     */
    public function hasKvkkConsent($userId)
    {
        $consent = $this->getByUserId($userId);
        return $consent && $consent['kvkk_consent'] == 1;
    }

    /**
     * Pazarlama onayını kontrol et
     */
    public function hasMarketingConsent($userId)
    {
        $consent = $this->getByUserId($userId);
        return $consent && $consent['marketing_consent'] == 1;
    }

    /**
     * IP adresini al (IPv4 ve IPv6 desteği)
     */
    public static function getClientIp()
    {
        $ipAddress = '';

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
        }

        return $ipAddress;
    }
}
