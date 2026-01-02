<?php

require_once __DIR__ . '/../core/Model.php';

class City extends Model
{
    protected $table = 'cities';

    public function findBySlug($slug)
    {
        return $this->findBy('slug', $slug);
    }

    public function getWithDistricts($cityId)
    {
        $sql = "SELECT c.*,
                       (SELECT COUNT(*) FROM districts WHERE city_id = c.id) as district_count,
                       (SELECT COUNT(*) FROM companies WHERE city_id = c.id AND is_approved = 1) as company_count
                FROM cities c
                WHERE c.id = ?";

        return $this->db->fetch($sql, [$cityId]);
    }

    public function getAllWithCounts()
    {
        $sql = "SELECT c.*,
                       (SELECT COUNT(*) FROM companies WHERE city_id = c.id AND is_approved = 1) as company_count,
                       (SELECT COUNT(*) FROM articles WHERE city_id = c.id AND is_published = 1) as article_count
                FROM cities c
                ORDER BY c.name ASC";

        return $this->db->fetchAll($sql);
    }

    public function search($term)
    {
        $sql = "SELECT c.*,
                       (SELECT COUNT(*) FROM companies WHERE city_id = c.id AND is_approved = 1) as company_count
                FROM cities c
                WHERE c.name COLLATE utf8mb4_turkish_ci LIKE ?
                ORDER BY c.name ASC
                LIMIT 20";

        return $this->db->fetchAll($sql, ['%' . $term . '%']);
    }

    /**
     * Update city record
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
