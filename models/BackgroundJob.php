<?php

class BackgroundJob
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create a new background job
     */
    public function create($jobType, $payload, $userId, $totalItems = 0)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO background_jobs (job_type, payload, user_id, total_items, status)
                VALUES (?, ?, ?, ?, 'pending')
            ");

            $payloadJson = json_encode($payload);
            $stmt->execute([$jobType, $payloadJson, $userId, $totalItems]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("BackgroundJob create error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Find a job by ID
     */
    public function find($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM background_jobs WHERE id = ?");
            $stmt->execute([$id]);
            $job = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($job && $job['payload']) {
                $job['payload'] = json_decode($job['payload'], true);
            }
            if ($job && $job['result']) {
                $job['result'] = json_decode($job['result'], true);
            }

            return $job;
        } catch (PDOException $e) {
            error_log("BackgroundJob find error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all jobs for a user
     */
    public function getByUser($userId, $limit = 50)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM background_jobs
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$userId, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("BackgroundJob getByUser error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get pending jobs
     */
    public function getPending($limit = 10)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM background_jobs
                WHERE status = 'pending'
                ORDER BY created_at ASC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($jobs as &$job) {
                if ($job['payload']) {
                    $job['payload'] = json_decode($job['payload'], true);
                }
            }

            return $jobs;
        } catch (PDOException $e) {
            error_log("BackgroundJob getPending error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update job status
     */
    public function updateStatus($id, $status, $errorMessage = null)
    {
        try {
            // Get fresh connection (with reconnect)
            $db = Database::getInstance()->getConnection();

            $updates = ['status' => $status];

            if ($status === 'processing') {
                $updates['started_at'] = date('Y-m-d H:i:s');
            } elseif ($status === 'completed' || $status === 'failed') {
                $updates['completed_at'] = date('Y-m-d H:i:s');
            }

            if ($errorMessage) {
                $updates['error_message'] = $errorMessage;
            }

            $fields = [];
            $values = [];
            foreach ($updates as $field => $value) {
                $fields[] = "$field = ?";
                $values[] = $value;
            }
            $values[] = $id;

            $stmt = $db->prepare("
                UPDATE background_jobs
                SET " . implode(', ', $fields) . "
                WHERE id = ?
            ");

            return $stmt->execute($values);
        } catch (PDOException $e) {
            error_log("BackgroundJob updateStatus error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update job progress
     */
    public function updateProgress($id, $processedItems, $totalItems = null)
    {
        try {
            // Get fresh connection (with reconnect)
            $db = Database::getInstance()->getConnection();

            $progress = $totalItems > 0 ? round(($processedItems / $totalItems) * 100) : 0;

            $sql = "UPDATE background_jobs SET processed_items = ?, progress = ?";
            $params = [$processedItems, $progress];

            if ($totalItems !== null) {
                $sql .= ", total_items = ?";
                $params[] = $totalItems;
            }

            $sql .= " WHERE id = ?";
            $params[] = $id;

            $stmt = $db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("BackgroundJob updateProgress error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Save job result
     */
    public function saveResult($id, $result)
    {
        try {
            // Get fresh connection (with reconnect)
            $db = Database::getInstance()->getConnection();

            $stmt = $db->prepare("
                UPDATE background_jobs
                SET result = ?
                WHERE id = ?
            ");

            $resultJson = json_encode($result);
            return $stmt->execute([$resultJson, $id]);
        } catch (PDOException $e) {
            error_log("BackgroundJob saveResult error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add job result detail (for individual article generations)
     */
    public function addResultDetail($jobId, $cityId, $districtId, $cityName, $districtName, $status, $articleId = null, $errorMessage = null)
    {
        try {
            // Get fresh connection (with reconnect)
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                INSERT INTO background_job_results
                (job_id, city_id, district_id, city_name, district_name, status, article_id, error_message)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            return $stmt->execute([
                $jobId,
                $cityId,
                $districtId,
                $cityName,
                $districtName,
                $status,
                $articleId,
                $errorMessage
            ]);
        } catch (PDOException $e) {
            error_log("BackgroundJob addResultDetail error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get job results
     */
    public function getResults($jobId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM background_job_results
                WHERE job_id = ?
                ORDER BY created_at ASC
            ");
            $stmt->execute([$jobId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("BackgroundJob getResults error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Delete old completed jobs (cleanup)
     */
    public function deleteOldJobs($daysOld = 30)
    {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM background_jobs
                WHERE status IN ('completed', 'failed')
                AND completed_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            ");
            return $stmt->execute([$daysOld]);
        } catch (PDOException $e) {
            error_log("BackgroundJob deleteOldJobs error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get job statistics
     */
    public function getStats($userId = null)
    {
        try {
            $sql = "
                SELECT
                    status,
                    COUNT(*) as count,
                    AVG(TIMESTAMPDIFF(SECOND, started_at, completed_at)) as avg_duration
                FROM background_jobs
            ";

            $params = [];
            if ($userId !== null) {
                $sql .= " WHERE user_id = ?";
                $params[] = $userId;
            }

            $sql .= " GROUP BY status";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("BackgroundJob getStats error: " . $e->getMessage());
            return [];
        }
    }
}
