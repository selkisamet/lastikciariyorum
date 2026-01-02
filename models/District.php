<?php

require_once __DIR__ . '/../core/Model.php';

class District extends Model
{
    protected $table = 'districts';

    public function findBySlug($cityId, $slug)
    {
        $sql = "SELECT * FROM {$this->table} WHERE city_id = ? AND slug = ?";
        return $this->db->fetch($sql, [$cityId, $slug]);
    }

    public function getByCity($cityId)
    {
        $sql = "SELECT d.*,
                       (SELECT COUNT(*) FROM companies WHERE district_id = d.id AND is_approved = 1) as company_count,
                       (SELECT COUNT(*) FROM articles WHERE district_id = d.id AND is_published = 1) as article_count
                FROM districts d
                WHERE d.city_id = ?
                ORDER BY d.name ASC";

        return $this->db->fetchAll($sql, [$cityId]);
    }

    public function getWithCounts($districtId)
    {
        $sql = "SELECT d.*,
                       (SELECT COUNT(*) FROM companies WHERE district_id = d.id AND is_approved = 1) as company_count,
                       (SELECT COUNT(*) FROM articles WHERE district_id = d.id AND is_published = 1) as article_count
                FROM districts d
                WHERE d.id = ?";

        return $this->db->fetch($sql, [$districtId]);
    }

    public function getAllWithCounts()
    {
        $sql = "SELECT d.*,
                       c.name as city_name,
                       c.slug as city_slug,
                       (SELECT COUNT(*) FROM companies WHERE district_id = d.id AND is_approved = 1) as company_count,
                       (SELECT COUNT(*) FROM articles WHERE district_id = d.id AND is_published = 1) as article_count
                FROM districts d
                JOIN cities c ON d.city_id = c.id
                WHERE (SELECT COUNT(*) FROM companies WHERE district_id = d.id AND is_approved = 1) > 0
                   OR (SELECT COUNT(*) FROM articles WHERE district_id = d.id AND is_published = 1) > 0
                ORDER BY d.name ASC";

        return $this->db->fetchAll($sql);
    }

    public function search($term)
    {
        $sql = "SELECT d.*,
                       c.name as city_name,
                       c.slug as city_slug,
                       (SELECT COUNT(*) FROM companies WHERE district_id = d.id AND is_approved = 1) as company_count
                FROM districts d
                JOIN cities c ON d.city_id = c.id
                WHERE d.name COLLATE utf8mb4_turkish_ci LIKE ? OR c.name COLLATE utf8mb4_turkish_ci LIKE ?
                ORDER BY d.name ASC
                LIMIT 20";

        $searchTerm = '%' . $term . '%';
        return $this->db->fetchAll($sql, [$searchTerm, $searchTerm]);
    }

    public function findByNameAndCity($districtName, $cityId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE name = ? AND city_id = ?";
        return $this->db->fetch($sql, [$districtName, $cityId]);
    }

    /**
     * Update district record
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
}
