<?php

require_once __DIR__ . '/../core/Model.php';

class Article extends Model
{
    protected $table = 'articles';

    public function findBySlug($slug, $cityId, $districtId = null)
    {
        if ($districtId) {
            $sql = "SELECT a.*, c.name as city_name, c.slug as city_slug,
                           d.name as district_name, d.slug as district_slug,
                           u.full_name as author_name
                    FROM articles a
                    LEFT JOIN cities c ON a.city_id = c.id
                    LEFT JOIN districts d ON a.district_id = d.id
                    LEFT JOIN users u ON a.author_id = u.id
                    WHERE a.slug = ? AND a.city_id = ? AND a.district_id = ? AND a.is_published = 1";
            return $this->db->fetch($sql, [$slug, $cityId, $districtId]);
        } else {
            $sql = "SELECT a.*, c.name as city_name, c.slug as city_slug,
                           u.full_name as author_name
                    FROM articles a
                    LEFT JOIN cities c ON a.city_id = c.id
                    LEFT JOIN users u ON a.author_id = u.id
                    WHERE a.slug = ? AND a.city_id = ? AND a.district_id IS NULL AND a.is_published = 1";
            return $this->db->fetch($sql, [$slug, $cityId]);
        }
    }

    public function getByCity($cityId, $limit = null)
    {
        $sql = "SELECT a.*, c.name as city_name, c.slug as city_slug
                FROM articles a
                LEFT JOIN cities c ON a.city_id = c.id
                WHERE a.city_id = ? AND a.district_id IS NULL AND a.is_published = 1
                ORDER BY a.created_at DESC";

        if ($limit) {
            $sql .= " LIMIT $limit";
        }

        return $this->db->fetchAll($sql, [$cityId]);
    }

    public function getByDistrict($districtId, $limit = null)
    {
        $sql = "SELECT a.*, c.name as city_name, c.slug as city_slug,
                       d.name as district_name, d.slug as district_slug
                FROM articles a
                LEFT JOIN cities c ON a.city_id = c.id
                LEFT JOIN districts d ON a.district_id = d.id
                WHERE a.district_id = ? AND a.is_published = 1
                ORDER BY a.created_at DESC";

        if ($limit) {
            $sql .= " LIMIT $limit";
        }

        return $this->db->fetchAll($sql, [$districtId]);
    }

    public function getLatest($limit = 10)
    {
        $sql = "SELECT a.*, c.name as city_name, c.slug as city_slug,
                       d.name as district_name, d.slug as district_slug
                FROM articles a
                LEFT JOIN cities c ON a.city_id = c.id
                LEFT JOIN districts d ON a.district_id = d.id
                WHERE a.is_published = 1
                ORDER BY a.created_at DESC
                LIMIT $limit";

        return $this->db->fetchAll($sql);
    }

    public function incrementViewCount($id)
    {
        $sql = "UPDATE {$this->table} SET view_count = view_count + 1 WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function getAll()
    {
        $sql = "SELECT a.*, c.name as city_name, c.slug as city_slug,
                       d.name as district_name, d.slug as district_slug,
                       u.full_name as author_name
                FROM articles a
                LEFT JOIN cities c ON a.city_id = c.id
                LEFT JOIN districts d ON a.district_id = d.id
                LEFT JOIN users u ON a.author_id = u.id
                ORDER BY a.created_at DESC";

        return $this->db->fetchAll($sql);
    }

    public function find($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }

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

    public function getTotalCount()
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $result = $this->db->fetch($sql);
        return $result['total'] ?? 0;
    }

    public function getRecentArticles($limit = 5)
    {
        $sql = "SELECT a.*, c.name as city_name, c.slug as city_slug,
                       d.name as district_name, d.slug as district_slug,
                       u.full_name as author_name
                FROM articles a
                LEFT JOIN cities c ON a.city_id = c.id
                LEFT JOIN districts d ON a.district_id = d.id
                LEFT JOIN users u ON a.author_id = u.id
                ORDER BY a.created_at DESC
                LIMIT ?";

        return $this->db->fetchAll($sql, [$limit]);
    }

    /**
     * HUB Architecture: Get single HUB article for district
     *
     * @param int $districtId
     * @return array|null Single article for this district
     */
    public function getDistrictHubArticle($districtId)
    {
        $sql = "SELECT a.*, c.name as city_name, c.slug as city_slug,
                       d.name as district_name, d.slug as district_slug,
                       u.full_name as author_name
                FROM articles a
                LEFT JOIN cities c ON a.city_id = c.id
                LEFT JOIN districts d ON a.district_id = d.id
                LEFT JOIN users u ON a.author_id = u.id
                WHERE a.district_id = ? AND a.is_published = 1
                LIMIT 1";

        return $this->db->fetch($sql, [$districtId]);
    }

    /**
     * HUB Architecture: Get single HUB article for city
     *
     * @param int $cityId
     * @return array|null Single article for this city (district_id IS NULL)
     */
    public function getCityHubArticle($cityId)
    {
        $sql = "SELECT a.*, c.name as city_name, c.slug as city_slug,
                       u.full_name as author_name
                FROM articles a
                LEFT JOIN cities c ON a.city_id = c.id
                LEFT JOIN users u ON a.author_id = u.id
                WHERE a.city_id = ? AND a.district_id IS NULL AND a.is_published = 1
                LIMIT 1";

        return $this->db->fetch($sql, [$cityId]);
    }

    /**
     * HUB Architecture: Create or update HUB article
     * Ensures only one article per location
     *
     * @param array $data Article data
     * @return int Article ID
     */
    public function createOrUpdateHubArticle($data)
    {
        $cityId = $data['city_id'];
        $districtId = $data['district_id'] ?? null;

        // Check if article already exists
        if ($districtId) {
            $existing = $this->getDistrictHubArticle($districtId);
        } else {
            $existing = $this->getCityHubArticle($cityId);
        }

        if ($existing) {
            // Update existing article
            $this->update($existing['id'], $data);
            return $existing['id'];
        } else {
            // Create new article
            return $this->create($data);
        }
    }

    /**
     * Find duplicate articles (for migration)
     *
     * @return array List of locations with multiple articles
     */
    public function findDuplicates()
    {
        $sql = "SELECT city_id, district_id, COUNT(*) as count
                FROM articles
                GROUP BY city_id, district_id
                HAVING count > 1";

        return $this->db->fetchAll($sql);
    }

    /**
     * Check if article is a HUB article (slug=NULL)
     *
     * @param int $articleId Article ID
     * @return bool True if HUB article, false otherwise
     */
    public function isHubArticle($articleId)
    {
        $article = $this->find($articleId);
        return $article && is_null($article['slug']);
    }

    /**
     * Get canonical URL for article
     *
     * @param array $article Article data with city_slug, district_slug, slug
     * @return string Canonical URL path
     */
    public function getCanonicalUrl($article)
    {
        $url = '/' . $article['city_slug'];

        if (!empty($article['district_slug'])) {
            $url .= '/' . $article['district_slug'];
        }

        // HUB article ise slug ekleme (slug=NULL)
        if (!is_null($article['slug']) && $article['slug'] !== '') {
            $url .= '/' . $article['slug'];
        }

        return $url;
    }
}
