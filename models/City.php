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
                WHERE c.name LIKE ?
                ORDER BY c.name ASC
                LIMIT 20";

        return $this->db->fetchAll($sql, ['%' . $term . '%']);
    }

    /**
     * HUB SEO: Get city by slug with decoded H2 sections
     */
    public function getBySlugWithH2($slug)
    {
        $city = $this->findBySlug($slug);
        if ($city && isset($city['h2_sections'])) {
            $city['h2_sections'] = json_decode($city['h2_sections'], true) ?? [];
        }
        return $city;
    }

    /**
     * HUB SEO: Get city by ID with decoded H2 sections
     */
    public function getWithH2Sections($cityId)
    {
        $city = $this->findBy('id', $cityId);
        if ($city && isset($city['h2_sections'])) {
            $city['h2_sections'] = json_decode($city['h2_sections'], true) ?? [];
        }
        return $city;
    }

    /**
     * HUB SEO: Save city with encoded H2 sections
     */
    public function saveWithH2Sections($id, $data)
    {
        if (isset($data['h2_sections'])) {
            // Validate and encode JSON structure
            if (is_array($data['h2_sections'])) {
                $data['h2_sections'] = json_encode($data['h2_sections'], JSON_UNESCAPED_UNICODE);
            }
        }

        return $this->update($id, $data);
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
