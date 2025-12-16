<?php

require_once __DIR__ . '/../core/Model.php';

class CookieConsent extends Model
{
    protected $table = 'cookie_consents';

    /**
     * Benzersiz çerez onay ID'si oluşturur
     *
     * @return string 64 karakterlik benzersiz ID
     */
    public static function generateConsentId()
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Kullanıcının çerez onayını kaydeder
     *
     * @param array $preferences Çerez tercihleri (necessary, analytics, marketing, preferences)
     * @return string|false Consent ID veya false
     */
    public function saveConsent($preferences)
    {
        $consentId = self::generateConsentId();
        $ipAddress = ViewLog::getClientIp();
        $userAgent = ViewLog::getUserAgent();

        // Onay geçerlilik süresi (12 ay sonra)
        $expiresAt = date('Y-m-d H:i:s', strtotime('+12 months'));

        $data = [
            'consent_id' => $consentId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'necessary' => true, // Her zaman true
            'analytics' => !empty($preferences['analytics']),
            'marketing' => !empty($preferences['marketing']),
            'preferences' => !empty($preferences['preferences']),
            'expires_at' => $expiresAt
        ];

        $sql = "INSERT INTO {$this->table}
                (consent_id, ip_address, user_agent, necessary, analytics, marketing, preferences, expires_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $result = $this->db->query($sql, array_values($data));

        return $result ? $consentId : false;
    }

    /**
     * Çerez onayını günceller
     *
     * @param string $consentId Consent ID
     * @param array $preferences Yeni çerez tercihleri
     * @return bool İşlem başarılı ise true
     */
    public function updateConsent($consentId, $preferences)
    {
        // Onay geçerlilik süresini yenile
        $expiresAt = date('Y-m-d H:i:s', strtotime('+12 months'));

        $sql = "UPDATE {$this->table}
                SET analytics = ?,
                    marketing = ?,
                    preferences = ?,
                    expires_at = ?,
                    last_updated = CURRENT_TIMESTAMP
                WHERE consent_id = ?";

        return $this->db->query($sql, [
            !empty($preferences['analytics']),
            !empty($preferences['marketing']),
            !empty($preferences['preferences']),
            $expiresAt,
            $consentId
        ]);
    }

    /**
     * Consent ID'ye göre çerez onayını getirir
     *
     * @param string $consentId Consent ID
     * @return array|false Onay bilgileri veya false
     */
    public function getConsent($consentId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE consent_id = ? LIMIT 1";
        return $this->db->fetch($sql, [$consentId]);
    }

    /**
     * Çerez onayının süresi dolmuş mu kontrol eder
     *
     * @param string $consentId Consent ID
     * @return bool Süresi dolmuşsa true
     */
    public function isExpired($consentId)
    {
        $sql = "SELECT expires_at FROM {$this->table} WHERE consent_id = ? LIMIT 1";
        $result = $this->db->fetch($sql, [$consentId]);

        if (!$result) {
            return true;
        }

        return strtotime($result['expires_at']) < time();
    }

    /**
     * Belirli bir çerez türü için onay var mı kontrol eder
     *
     * @param string $consentId Consent ID
     * @param string $type Çerez türü (necessary, analytics, marketing, preferences)
     * @return bool Onay varsa true
     */
    public function hasConsent($consentId, $type)
    {
        if (!$consentId) {
            return false;
        }

        // Süresi dolmuşsa onay geçersiz
        if ($this->isExpired($consentId)) {
            return false;
        }

        $consent = $this->getConsent($consentId);

        if (!$consent) {
            return false;
        }

        // Necessary çerezler her zaman true
        if ($type === 'necessary') {
            return true;
        }

        return !empty($consent[$type]);
    }

    /**
     * Süresi dolmuş onayları temizler
     *
     * @param int $daysAfterExpiry Süresi dolduktan kaç gün sonra silinecek (varsayılan: 30)
     * @return bool İşlem başarılı ise true
     */
    public function cleanExpiredConsents($daysAfterExpiry = 30)
    {
        $sql = "DELETE FROM {$this->table}
                WHERE expires_at < DATE_SUB(NOW(), INTERVAL ? DAY)";

        return $this->db->query($sql, [$daysAfterExpiry]);
    }

    /**
     * IP bazlı istatistikler (KVKK raporlama için)
     *
     * @param int $days Son kaç günün istatistiği (varsayılan: 30)
     * @return array İstatistik bilgileri
     */
    public function getConsentStats($days = 30)
    {
        $sql = "SELECT
                    COUNT(*) as total_consents,
                    SUM(analytics) as analytics_accepted,
                    SUM(marketing) as marketing_accepted,
                    SUM(preferences) as preferences_accepted,
                    COUNT(DISTINCT ip_address) as unique_users
                FROM {$this->table}
                WHERE consent_date >= DATE_SUB(NOW(), INTERVAL ? DAY)";

        return $this->db->fetch($sql, [$days]);
    }
}
