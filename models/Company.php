<?php

require_once __DIR__ . '/../core/Model.php';

class Company extends Model
{
    protected $table = 'companies';

    public function findBySlug($slug, $cityId, $districtId = null)
    {
        if ($districtId) {
            $sql = "SELECT co.*, c.name as city_name, c.slug as city_slug,
                           d.name as district_name, d.slug as district_slug
                    FROM companies co
                    LEFT JOIN cities c ON co.city_id = c.id
                    LEFT JOIN districts d ON co.district_id = d.id
                    WHERE co.slug = ? AND co.city_id = ? AND co.district_id = ? AND co.is_approved = 1";
            return $this->db->fetch($sql, [$slug, $cityId, $districtId]);
        } else {
            $sql = "SELECT co.*, c.name as city_name, c.slug as city_slug
                    FROM companies co
                    LEFT JOIN cities c ON co.city_id = c.id
                    WHERE co.slug = ? AND co.city_id = ? AND co.is_approved = 1";
            return $this->db->fetch($sql, [$slug, $cityId]);
        }
    }

    public function getByCity($cityId, $limit = null)
    {
        $sql = "SELECT co.*, c.name as city_name, c.slug as city_slug,
                       d.name as district_name, d.slug as district_slug
                FROM companies co
                LEFT JOIN cities c ON co.city_id = c.id
                LEFT JOIN districts d ON co.district_id = d.id
                WHERE co.city_id = ? AND co.is_approved = 1
                ORDER BY co.created_at DESC";

        if ($limit) {
            $sql .= " LIMIT $limit";
        }

        return $this->db->fetchAll($sql, [$cityId]);
    }

    public function getByDistrict($districtId, $limit = null)
    {
        $sql = "SELECT co.*, c.name as city_name, c.slug as city_slug,
                       d.name as district_name, d.slug as district_slug
                FROM companies co
                LEFT JOIN cities c ON co.city_id = c.id
                LEFT JOIN districts d ON co.district_id = d.id
                WHERE co.district_id = ? AND co.is_approved = 1
                ORDER BY co.created_at DESC";

        if ($limit) {
            $sql .= " LIMIT $limit";
        }

        return $this->db->fetchAll($sql, [$districtId]);
    }

    public function getByUserId($userId)
    {
        $sql = "SELECT co.*, c.name as city_name, c.slug as city_slug,
                       d.name as district_name, d.slug as district_slug
                FROM companies co
                LEFT JOIN cities c ON co.city_id = c.id
                LEFT JOIN districts d ON co.district_id = d.id
                WHERE co.user_id = ?
                ORDER BY co.created_at DESC";

        return $this->db->fetchAll($sql, [$userId]);
    }

    public function getAll()
    {
        $sql = "SELECT co.*, c.name as city_name, c.slug as city_slug,
                       d.name as district_name, d.slug as district_slug
                FROM companies co
                LEFT JOIN cities c ON co.city_id = c.id
                LEFT JOIN districts d ON co.district_id = d.id
                ORDER BY co.is_approved ASC, co.created_at DESC";

        return $this->db->fetchAll($sql);
    }

    public function getPending($limit = null)
    {
        $sql = "SELECT co.*, c.name as city_name, c.slug as city_slug,
                       d.name as district_name, d.slug as district_slug
                FROM companies co
                LEFT JOIN cities c ON co.city_id = c.id
                LEFT JOIN districts d ON co.district_id = d.id
                WHERE co.is_approved = 0
                ORDER BY co.created_at DESC";

        if ($limit) {
            $sql .= " LIMIT $limit";
        }

        return $this->db->fetchAll($sql);
    }

    public function incrementViewCount($id)
    {
        $sql = "UPDATE {$this->table} SET view_count = view_count + 1 WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function approve($id)
    {
        return $this->update($id, ['is_approved' => 1]);
    }

    public function reject($id)
    {
        return $this->update($id, ['is_approved' => 0]);
    }

    public function getTotalCount()
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $result = $this->db->fetch($sql);
        return $result['total'] ?? 0;
    }

    public function getApprovedCount()
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE is_approved = 1";
        $result = $this->db->fetch($sql);
        return $result['total'] ?? 0;
    }

    public function getPendingCount()
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE is_approved = 0";
        $result = $this->db->fetch($sql);
        return $result['total'] ?? 0;
    }

    public function getRecent($limit = 5)
    {
        $sql = "SELECT co.*, c.name as city_name, c.slug as city_slug,
                       d.name as district_name, d.slug as district_slug
                FROM companies co
                LEFT JOIN cities c ON co.city_id = c.id
                LEFT JOIN districts d ON co.district_id = d.id
                ORDER BY co.created_at DESC
                LIMIT ?";

        return $this->db->fetchAll($sql, [$limit]);
    }

    public function getLast7DaysStats()
    {
        $sql = "SELECT DATE(created_at) as date, COUNT(*) as count
                FROM {$this->table}
                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date ASC";

        return $this->db->fetchAll($sql);
    }
}
