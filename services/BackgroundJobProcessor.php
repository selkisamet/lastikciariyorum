<?php

require_once __DIR__ . '/../models/BackgroundJob.php';
require_once __DIR__ . '/../models/Article.php';
require_once __DIR__ . '/../models/City.php';
require_once __DIR__ . '/../models/District.php';
require_once __DIR__ . '/AIService.php';

class BackgroundJobProcessor
{
    private $jobModel;
    private $articleModel;
    private $cityModel;
    private $districtModel;
    private $aiService;

    public function __construct()
    {
        $this->jobModel = new BackgroundJob();
        $this->articleModel = new Article();
        $this->cityModel = new City();
        $this->districtModel = new District();
        $this->aiService = new AIService();
    }

    /**
     * Process a single job
     */
    public function processJob($jobId)
    {
        try {
            $job = $this->jobModel->find($jobId);

            if (!$job) {
                error_log("Job not found: $jobId");
                return false;
            }

            // Allow processing if pending OR if processing but stalled (for recovery)
            if ($job['status'] !== 'pending' && $job['status'] !== 'processing') {
                error_log("Job $jobId cannot be processed (status: {$job['status']})");
                return false;
            }

            // Update status to processing (or keep as processing if recovering)
            if ($job['status'] === 'pending') {
                $this->jobModel->updateStatus($jobId, 'processing');
            }
        } catch (Exception $e) {
            error_log("Error checking/updating job status: " . $e->getMessage());
            throw $e;
        }

        try {
            switch ($job['job_type']) {
                case 'bulk_article_generation':
                    $this->processBulkArticleGeneration($jobId, $job['payload']);
                    break;

                default:
                    throw new Exception("Unknown job type: {$job['job_type']}");
            }

            // Check if job was cancelled during processing (use fresh DB query)
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT status FROM background_jobs WHERE id = ?");
            $stmt->execute([$jobId]);
            $currentStatus = $stmt->fetchColumn();

            if ($currentStatus === 'failed') {
                // Job was cancelled, don't mark as completed
                return false;
            }

            // Mark as completed
            $this->jobModel->updateStatus($jobId, 'completed');
            return true;

        } catch (Exception $e) {
            error_log("Job $jobId failed: " . $e->getMessage());
            $this->jobModel->updateStatus($jobId, 'failed', $e->getMessage());
            return false;
        }
    }

    /**
     * Process bulk article generation job
     */
    private function processBulkArticleGeneration($jobId, $payload)
    {
        $locations = $payload['locations'] ?? [];
        $primaryKeyword = $payload['primary_keyword'] ?? '';
        $keywords = $payload['keywords'] ?? [];
        $wordCount = $payload['word_count'] ?? 800;
        $autoPublish = $payload['auto_publish'] ?? false;

        if (empty($locations)) {
            throw new Exception('No locations provided');
        }

        $totalItems = count($locations);

        // Get current progress for recovery (if job was interrupted)
        $currentJob = $this->jobModel->find($jobId);
        $processedCount = $currentJob['processed_items'] ?? 0;

        // Initialize progress if starting fresh
        if ($processedCount === 0) {
            $this->jobModel->updateProgress($jobId, 0, $totalItems);
        }

        // Get existing results count for recovery
        $existingResults = $this->jobModel->getResults($jobId);
        $successCount = 0;
        $errorCount = 0;

        foreach ($existingResults as $result) {
            if ($result['status'] === 'success') {
                $successCount++;
            } else {
                $errorCount++;
            }
        }

        foreach ($locations as $index => $location) {
            // Check if job was cancelled (get fresh data from DB)
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT status FROM background_jobs WHERE id = ?");
            $stmt->execute([$jobId]);
            $currentJobStatus = $stmt->fetchColumn();

            if ($currentJobStatus === 'failed') {
                // Job was cancelled by user
                break; // Exit the loop
            }

            // RECOVERY: Skip already processed items
            if ($index < $processedCount) {
                continue;
            }
            try {
                // Check if HUB article already exists
                $existingArticle = null;
                if ($location['district_id']) {
                    $existingArticle = $this->articleModel->getDistrictHubArticle(
                        $location['city_id'],
                        $location['district_id']
                    );
                } else {
                    $existingArticle = $this->articleModel->getCityHubArticle($location['city_id']);
                }

                if ($existingArticle) {
                    $errorMsg = "HUB article already exists for this location";

                    error_log("DEBUG: About to call addResultDetail for job $jobId");

                    $addResult = $this->jobModel->addResultDetail(
                        $jobId,
                        $location['city_id'],
                        $location['district_id'],
                        $location['city_name'],
                        $location['district_name'],
                        'failed',
                        null,
                        $errorMsg
                    );

                    error_log("DEBUG: addResultDetail returned: " . ($addResult ? 'true' : 'false'));

                    $errorCount++;
                    $processedCount++;

                    error_log("DEBUG: About to call updateProgress($jobId, $processedCount, $totalItems)");

                    $this->jobModel->updateProgress($jobId, $processedCount, $totalItems);

                    error_log("DEBUG: updateProgress completed, continuing to next item");

                    continue;
                }

                // Check if job was cancelled BEFORE generating article
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT status FROM background_jobs WHERE id = ?");
                $stmt->execute([$jobId]);
                $currentJobStatus = $stmt->fetchColumn();

                if ($currentJobStatus === 'failed') {
                    error_log("Job $jobId was cancelled by user before generating article, stopping");
                    break;
                }

                // Generate article content
                // Yeni AIService formatı: doğrudan params array alıyor
                $params = [
                    'city' => $location['city_name'],
                    'district' => $location['district_name'] ?? null,
                    'primary_keyword' => $primaryKeyword,
                    'keywords' => $keywords,
                    'word_count' => $wordCount,
                ];

                // generateArticle() artık doğrudan makale verisini döndürüyor (Exception fırlatıyor hata durumunda)
                $article = $this->aiService->generateArticle($params);

                // Check if job was cancelled AFTER generating article (before saving)
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT status FROM background_jobs WHERE id = ?");
                $stmt->execute([$jobId]);
                $currentJobStatus = $stmt->fetchColumn();

                if ($currentJobStatus === 'failed') {
                    error_log("Job $jobId was cancelled by user after generating article, stopping (article not saved)");
                    break;
                }

                // Save article to database
                $data = [
                    'city_id' => $location['city_id'],
                    'district_id' => $location['district_id'],
                    'slug' => null, // HUB article (no slug)
                    'title' => $article['title'],
                    'content' => $article['content'],
                    'excerpt' => $article['excerpt'],
                    'meta_title' => $article['meta_title'],
                    'meta_description' => $article['meta_description'],
                    'author_id' => $payload['user_id'] ?? 1,
                    'is_published' => $autoPublish ? 1 : 0,
                    'published_at' => $autoPublish ? date('Y-m-d H:i:s') : null,
                ];

                $articleId = $this->articleModel->create($data);

                if (!$articleId) {
                    throw new Exception('Failed to save article to database');
                }

                // Record success
                $this->jobModel->addResultDetail(
                    $jobId,
                    $location['city_id'],
                    $location['district_id'],
                    $location['city_name'],
                    $location['district_name'],
                    'success',
                    $articleId,
                    null
                );

                $successCount++;

            } catch (Exception $e) {
                error_log("Failed to generate article for {$location['city_name']}/{$location['district_name']}: " . $e->getMessage());

                $this->jobModel->addResultDetail(
                    $jobId,
                    $location['city_id'],
                    $location['district_id'],
                    $location['city_name'],
                    $location['district_name'],
                    'failed',
                    null,
                    $e->getMessage()
                );

                $errorCount++;
            }

            $processedCount++;
            $this->jobModel->updateProgress($jobId, $processedCount, $totalItems);

            // Small delay to prevent API rate limiting
            usleep(500000); // 0.5 seconds
        }

        // Save final result summary
        $resultSummary = [
            'total' => $totalItems,
            'success' => $successCount,
            'failed' => $errorCount,
        ];

        $this->jobModel->saveResult($jobId, $resultSummary);
    }

    /**
     * Process all pending jobs
     */
    public function processPendingJobs($limit = 10)
    {
        $jobs = $this->jobModel->getPending($limit);

        foreach ($jobs as $job) {
            $this->processJob($job['id']);
        }

        return count($jobs);
    }

    /**
     * Get job status with details
     */
    public function getJobStatus($jobId)
    {
        $job = $this->jobModel->find($jobId);

        if (!$job) {
            return null;
        }

        $results = $this->jobModel->getResults($jobId);

        return [
            'job' => $job,
            'results' => $results,
        ];
    }
}
