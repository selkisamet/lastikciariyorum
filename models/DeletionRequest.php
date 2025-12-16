<?php

require_once __DIR__ . '/../core/Database.php';

class DeletionRequest
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Yeni silme talebi oluştur
     */
    public function create($data)
    {
        $sql = "INSERT INTO deletion_requests (
                    user_id,
                    request_type,
                    reason,
                    requested_at,
                    requested_ip
                ) VALUES (
                    :user_id,
                    :request_type,
                    :reason,
                    :requested_at,
                    :requested_ip
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $data['user_id'],
            'request_type' => $data['request_type'],
            'reason' => $data['reason'] ?? null,
            'requested_at' => date('Y-m-d H:i:s'),
            'requested_ip' => $data['ip_address'] ?? null,
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Kullanıcının bekleyen talebini getir
     */
    public function getPendingByUserId($userId)
    {
        $sql = "SELECT * FROM deletion_requests
                WHERE user_id = :user_id
                AND status = 'pending'
                ORDER BY requested_at DESC
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Tüm bekleyen talepleri getir (admin için)
     */
    public function getAllPending()
    {
        $sql = "SELECT dr.*,
                       u.email as user_email,
                       u.full_name as user_name,
                       c.name as company_name
                FROM deletion_requests dr
                JOIN users u ON dr.user_id = u.id
                LEFT JOIN companies c ON u.id = c.user_id
                WHERE dr.status = 'pending'
                ORDER BY dr.requested_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tüm talepleri getir (admin için)
     */
    public function getAll($limit = 50, $offset = 0)
    {
        $sql = "SELECT dr.*,
                       u.email as user_email,
                       u.full_name as user_name,
                       c.name as company_name,
                       admin.full_name as processed_by_name
                FROM deletion_requests dr
                JOIN users u ON dr.user_id = u.id
                LEFT JOIN companies c ON u.id = c.user_id
                LEFT JOIN users admin ON dr.processed_by = admin.id
                ORDER BY dr.requested_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Talep detayını getir
     */
    public function find($id)
    {
        $sql = "SELECT dr.*,
                       u.email as user_email,
                       u.full_name as user_name,
                       c.name as company_name,
                       admin.full_name as processed_by_name
                FROM deletion_requests dr
                JOIN users u ON dr.user_id = u.id
                LEFT JOIN companies c ON u.id = c.user_id
                LEFT JOIN users admin ON dr.processed_by = admin.id
                WHERE dr.id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Talebi işle (onayla/reddet)
     */
    public function process($id, $status, $adminId, $adminNotes = null)
    {
        $sql = "UPDATE deletion_requests SET
                    status = :status,
                    processed_by = :admin_id,
                    processed_at = :processed_at,
                    admin_notes = :admin_notes
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'status' => $status,
            'admin_id' => $adminId,
            'processed_at' => date('Y-m-d H:i:s'),
            'admin_notes' => $adminNotes,
            'id' => $id,
        ]);
    }

    /**
     * Kullanıcıyı kalıcı olarak sil
     */
    public function permanentlyDeleteUser($userId)
    {
        try {
            $this->db->beginTransaction();

            // Önce kullanıcıya ait tüm firma logolarını sil
            $sql = "SELECT logo FROM companies WHERE user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $userId]);
            $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($companies as $company) {
                if ($company['logo']) {
                    $logoPath = __DIR__ . '/../public/uploads/logos/' . $company['logo'];
                    if (file_exists($logoPath)) {
                        unlink($logoPath);
                    }
                }
            }

            // Kullanıcıya ait firmaların görüntülenme kayıtlarını sil
            $sql = "DELETE vl FROM view_logs vl
                    INNER JOIN companies c ON vl.viewable_id = c.id
                    WHERE c.user_id = :user_id AND vl.viewable_type = 'company'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $userId]);

            // Kullanıcıya ait firmaları sil
            $sql = "DELETE FROM companies WHERE user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $userId]);

            // Kullanıcıya ait user_consents kayıtlarını sil
            $sql = "DELETE FROM user_consents WHERE user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $userId]);

            // Kullanıcıya ait deletion_requests kayıtlarını sil
            $sql = "DELETE FROM deletion_requests WHERE user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $userId]);

            // Son olarak kullanıcıyı sil
            $sql = "DELETE FROM users WHERE id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $userId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * IP adresini al
     */
    public static function getClientIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? '';
        }
    }

    /**
     * Bekleyen talep sayısını getir
     */
    public function getPendingCount()
    {
        $sql = "SELECT COUNT(*) as total FROM deletion_requests WHERE status = 'pending'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
}
