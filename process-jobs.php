<?php
/**
 * Background Job Processor - CLI Script
 *
 * This script processes pending background jobs.
 * It should be run via CLI (command line) either manually or via cron.
 *
 * Usage:
 *   php process-jobs.php [--limit=N]
 *
 * Options:
 *   --limit=N    Process maximum N jobs (default: 10)
 */

// Prevent direct browser access
if (php_sapi_name() !== 'cli') {
    // Allow web access but don't show output until done
    set_time_limit(0);
    ignore_user_abort(true);
}

require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/helpers.php';
require_once __DIR__ . '/models/BackgroundJob.php';
require_once __DIR__ . '/models/Article.php';
require_once __DIR__ . '/services/AIService.php';
require_once __DIR__ . '/services/BackgroundJobProcessor.php';

// Parse command line arguments
$limit = 10; // Default limit

if (php_sapi_name() === 'cli') {
    // CLI mode - parse --limit argument
    foreach ($argv as $arg) {
        if (strpos($arg, '--limit=') === 0) {
            $limit = (int)substr($arg, 8);
        }
    }
} else {
    // Web mode - check GET/POST parameters
    if (isset($_GET['limit'])) {
        $limit = (int)$_GET['limit'];
    }
}

// Ensure limit is reasonable
if ($limit < 1) $limit = 1;
if ($limit > 100) $limit = 100;

try {
    error_log("Starting job processor (limit: $limit)");

    $processor = new BackgroundJobProcessor();
    $processedCount = $processor->processPendingJobs($limit);

    error_log("Job processor completed. Processed $processedCount job(s)");

    if (php_sapi_name() === 'cli') {
        echo "Processed $processedCount job(s)\n";
    }

    exit(0);

} catch (Exception $e) {
    error_log("Job processor error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    if (php_sapi_name() === 'cli') {
        echo "Error: " . $e->getMessage() . "\n";
    }

    exit(1);
}
