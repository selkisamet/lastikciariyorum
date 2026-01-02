<?php

require_once __DIR__ . '/../core/Model.php';

/**
 * AI Provider Setting Model
 *
 * Çoklu AI sağlayıcı yapılandırmasını ve istatistiklerini yönetir.
 */
class AIProviderSetting extends Model
{
    protected $table = 'ai_provider_settings';

    /**
     * Aktif sağlayıcıları öncelik sırasına göre getir
     *
     * @return array Aktif sağlayıcılar (öncelik yüksekten düşüğe)
     */
    public function getActiveProviders()
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE is_active = 1
                ORDER BY priority DESC, id ASC";

        return $this->db->fetchAll($sql);
    }

    /**
     * Varsayılan sağlayıcıyı getir
     *
     * @return array|null Varsayılan sağlayıcı veya null
     */
    public function getDefaultProvider()
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE is_default = 1 AND is_active = 1
                LIMIT 1";

        return $this->db->fetch($sql);
    }

    /**
     * Sağlayıcıyı isme göre getir
     *
     * @param string $name Provider name (anthropic, openai, vb.)
     * @return array|null Sağlayıcı bilgisi veya null
     */
    public function getByName($name)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE provider_name = ?
                LIMIT 1";

        return $this->db->fetch($sql, [$name]);
    }

    /**
     * Sağlayıcı istatistiklerini güncelle
     *
     * @param int $id Provider ID
     * @param bool $success Başarılı mı?
     * @param string|null $errorMessage Hata mesajı (başarısızsa)
     * @param int|null $responseTimeMs Yanıt süresi (ms)
     * @return bool
     */
    public function updateStats($id, $success, $errorMessage = null, $responseTimeMs = null)
    {
        if ($success) {
            // Başarılı istek
            $sql = "UPDATE {$this->table}
                    SET success_count = success_count + 1,
                        last_used_at = NOW(),
                        last_error = NULL,
                        updated_at = NOW()
                    WHERE id = ?";

            return $this->db->query($sql, [$id]);
        } else {
            // Başarısız istek
            $sql = "UPDATE {$this->table}
                    SET error_count = error_count + 1,
                        last_error = ?,
                        updated_at = NOW()
                    WHERE id = ?";

            return $this->db->query($sql, [$errorMessage, $id]);
        }
    }

    /**
     * Varsayılan sağlayıcıyı ayarla
     *
     * @param int $id Provider ID
     * @return bool
     */
    public function setDefault($id)
    {
        // Önce tüm varsayılanları kaldır
        $this->db->query("UPDATE {$this->table} SET is_default = 0");

        // Yeni varsayılanı ayarla
        $sql = "UPDATE {$this->table}
                SET is_default = 1,
                    updated_at = NOW()
                WHERE id = ?";

        return $this->db->query($sql, [$id]);
    }

    /**
     * Sağlayıcıyı aktif/pasif yap
     *
     * @param int $id Provider ID
     * @param bool $isActive Aktif mi?
     * @return bool
     */
    public function toggleActive($id, $isActive)
    {
        $sql = "UPDATE {$this->table}
                SET is_active = ?,
                    updated_at = NOW()
                WHERE id = ?";

        return $this->db->query($sql, [$isActive ? 1 : 0, $id]);
    }

    /**
     * Tüm sağlayıcıları istatistiklerle birlikte getir
     *
     * @return array Tüm sağlayıcılar
     */
    public function getAllWithStats()
    {
        $sql = "SELECT *,
                       (success_count + error_count) as total_requests,
                       CASE
                           WHEN (success_count + error_count) > 0
                           THEN ROUND((success_count * 100.0) / (success_count + error_count), 2)
                           ELSE 0
                       END as success_rate
                FROM {$this->table}
                ORDER BY priority DESC, display_name ASC";

        return $this->db->fetchAll($sql);
    }

    /**
     * Provider config JSON'ını decode et
     *
     * @param array $provider Provider row
     * @return array Decoded config
     */
    public function getConfig($provider)
    {
        if (empty($provider['provider_config'])) {
            return [];
        }

        $config = json_decode($provider['provider_config'], true);
        return $config ?? [];
    }

    /**
     * Provider config'i güncelle
     *
     * @param int $id Provider ID
     * @param array $config Config array
     * @return bool
     */
    public function updateConfig($id, $config)
    {
        $sql = "UPDATE {$this->table}
                SET provider_config = ?,
                    updated_at = NOW()
                WHERE id = ?";

        return $this->db->query($sql, [json_encode($config), $id]);
    }

    /**
     * Sağlayıcının çalışıp çalışmadığını kontrol et
     *
     * @param int $id Provider ID
     * @return bool API key yapılandırılmış ve aktif mi?
     */
    public function isConfigured($id)
    {
        $provider = $this->find($id);

        if (!$provider) {
            return false;
        }

        return !empty($provider['api_key']) && $provider['is_active'] == 1;
    }

    /**
     * Son hataları getir (debugging için)
     *
     * @param int $limit Kaç tane
     * @return array Hatalı sağlayıcılar
     */
    public function getRecentErrors($limit = 5)
    {
        $sql = "SELECT provider_name, display_name, last_error, error_count, updated_at
                FROM {$this->table}
                WHERE last_error IS NOT NULL
                  AND last_error != ''
                ORDER BY updated_at DESC
                LIMIT ?";

        return $this->db->fetchAll($sql, [$limit]);
    }

    /**
     * En çok kullanılan sağlayıcıları getir
     *
     * @param int $limit Kaç tane
     * @return array En çok kullanılan sağlayıcılar
     */
    public function getMostUsed($limit = 5)
    {
        $sql = "SELECT provider_name, display_name, success_count, last_used_at
                FROM {$this->table}
                WHERE success_count > 0
                ORDER BY success_count DESC, last_used_at DESC
                LIMIT ?";

        return $this->db->fetchAll($sql, [$limit]);
    }
}
