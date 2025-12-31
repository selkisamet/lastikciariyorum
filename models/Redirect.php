<?php

require_once __DIR__ . '/../core/Model.php';

/**
 * Redirect Model
 *
 * Eski makale URL'lerini yeni HUB sayfalarına 301 redirect etmek için kullanılır.
 * SEO yapısal değişiklik sonrası duplicate içeriklerin birleştirilmesi için.
 */
class Redirect extends Model
{
    protected $table = 'url_redirects';

    /**
     * Redirect bul
     *
     * @param string $oldUrl Eski URL (örn: /istanbul/sultanbeyli/makale-slug)
     * @return array|null Redirect bilgisi veya null
     */
    public function findRedirect($oldUrl)
    {
        // Normalize URL (başta ve sonda / temizle)
        $oldUrl = '/' . trim($oldUrl, '/');

        $sql = "SELECT * FROM {$this->table} WHERE old_url = ? LIMIT 1";
        $redirect = $this->db->fetch($sql, [$oldUrl]);

        if ($redirect) {
            // Hit count güncelle
            $this->incrementHitCount($redirect['id']);
        }

        return $redirect;
    }

    /**
     * Redirect oluştur
     *
     * @param string $oldUrl Eski URL
     * @param string $newUrl Yeni URL
     * @param int $redirectType 301 (default) veya 302
     * @return int Insert ID
     */
    public function createRedirect($oldUrl, $newUrl, $redirectType = 301)
    {
        // Normalize URLs
        $oldUrl = '/' . trim($oldUrl, '/');
        $newUrl = '/' . trim($newUrl, '/');

        // Aynı URL'e redirect varsa güncelle
        $existing = $this->findRedirect($oldUrl);
        if ($existing) {
            return $this->update($existing['id'], [
                'new_url' => $newUrl,
                'redirect_type' => $redirectType
            ]);
        }

        $sql = "INSERT INTO {$this->table} (old_url, new_url, redirect_type) VALUES (?, ?, ?)";
        return $this->db->query($sql, [$oldUrl, $newUrl, $redirectType]);
    }

    /**
     * Hit count artır
     *
     * @param int $id Redirect ID
     * @return bool
     */
    private function incrementHitCount($id)
    {
        $sql = "UPDATE {$this->table}
                SET hit_count = hit_count + 1,
                    last_hit_at = NOW()
                WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    /**
     * Toplu redirect import (Excel/CSV'den)
     *
     * @param array $redirects [['old_url' => '', 'new_url' => ''], ...]
     * @return array ['success' => int, 'failed' => int, 'errors' => []]
     */
    public function bulkImport($redirects)
    {
        $success = 0;
        $failed = 0;
        $errors = [];

        foreach ($redirects as $index => $redirect) {
            try {
                if (empty($redirect['old_url']) || empty($redirect['new_url'])) {
                    throw new Exception('old_url ve new_url zorunludur');
                }

                $this->createRedirect(
                    $redirect['old_url'],
                    $redirect['new_url'],
                    $redirect['redirect_type'] ?? 301
                );

                $success++;
            } catch (Exception $e) {
                $failed++;
                $errors[] = "Satır {$index}: " . $e->getMessage();
            }
        }

        return [
            'success' => $success,
            'failed' => $failed,
            'errors' => $errors
        ];
    }

    /**
     * Tüm redirectleri listele
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAll($limit = 100, $offset = 0)
    {
        $sql = "SELECT * FROM {$this->table}
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?";
        return $this->db->fetchAll($sql, [$limit, $offset]);
    }

    /**
     * Redirect sil
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    /**
     * Redirect güncelle
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data)
    {
        $fields = [];
        $values = [];

        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }

        $values[] = $id;
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";

        return $this->db->query($sql, $values);
    }

    /**
     * İstatistikler
     *
     * @return array
     */
    public function getStats()
    {
        $sql = "SELECT
                    COUNT(*) as total_redirects,
                    SUM(hit_count) as total_hits,
                    MAX(last_hit_at) as last_usage
                FROM {$this->table}";

        return $this->db->fetch($sql);
    }
}
