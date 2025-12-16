<?php

require_once __DIR__ . '/../core/Model.php';

class ViewLog extends Model
{
    protected $table = 'view_logs';

    /**
     * Belirli bir süre içinde aynı IP'den görüntülenme olup olmadığını kontrol eder
     *
     * @param string $type 'article' veya 'company'
     * @param int $id Görüntülenen öğenin ID'si
     * @param string $ipAddress IP adresi
     * @param int $hours Kaç saat içinde kontrol edilecek (varsayılan: 24)
     * @return bool Daha önce görüntülenmişse true
     */
    public function hasRecentView($type, $id, $ipAddress, $hours = 24)
    {
        $sql = "SELECT COUNT(*) as count
                FROM {$this->table}
                WHERE viewable_type = ?
                AND viewable_id = ?
                AND ip_address = ?
                AND created_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)";

        $result = $this->db->fetch($sql, [$type, $id, $ipAddress, $hours]);

        return $result && $result['count'] > 0;
    }

    /**
     * Yeni bir görüntülenme kaydı oluşturur
     *
     * @param string $type 'article' veya 'company'
     * @param int $id Görüntülenen öğenin ID'si
     * @param string $ipAddress IP adresi
     * @param string|null $userAgent Kullanıcı tarayıcı bilgisi
     * @return bool İşlem başarılı ise true
     */
    public function logView($type, $id, $ipAddress, $userAgent = null)
    {
        $sql = "INSERT INTO {$this->table} (viewable_type, viewable_id, ip_address, user_agent)
                VALUES (?, ?, ?, ?)";

        return $this->db->query($sql, [$type, $id, $ipAddress, $userAgent]);
    }

    /**
     * Belirli bir öğe için benzersiz görüntülenme sayısını döndürür
     *
     * @param string $type 'article' veya 'company'
     * @param int $id Görüntülenen öğenin ID'si
     * @return int Benzersiz IP sayısı
     */
    public function getUniqueViewCount($type, $id)
    {
        $sql = "SELECT COUNT(DISTINCT ip_address) as count
                FROM {$this->table}
                WHERE viewable_type = ?
                AND viewable_id = ?";

        $result = $this->db->fetch($sql, [$type, $id]);

        return $result ? (int)$result['count'] : 0;
    }

    /**
     * Eski kayıtları temizler (örn. 90 günden eski)
     *
     * @param int $days Kaç günden eski kayıtlar silinecek
     * @return bool İşlem başarılı ise true
     */
    public function cleanOldLogs($days = 90)
    {
        $sql = "DELETE FROM {$this->table}
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";

        return $this->db->query($sql, [$days]);
    }

    /**
     * Kullanıcının IP adresini güvenli bir şekilde alır
     *
     * @return string IP adresi
     */
    public static function getClientIp()
    {
        // Proxy arkasındaysa gerçek IP'yi al
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Virgülle ayrılmış IP listesi olabilir, ilkini al
            $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ipList[0]);
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }

        // IPv6'yı normalize et
        if ($ip === '::1') {
            $ip = '127.0.0.1';
        }

        // IP validasyonu
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $ip = '0.0.0.0';
        }

        return $ip;
    }

    /**
     * Kullanıcı tarayıcı bilgisini alır
     *
     * @return string|null User agent
     */
    public static function getUserAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? null;
    }
}
