<?php
/**
 * DIAGNOSTIC TOOL - Find why job processing fails
 */

set_time_limit(60);
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/helpers.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>System Diagnostic</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 30px; border-bottom: 2px solid #ddd; padding-bottom: 5px; }
        .check { margin: 15px 0; padding: 15px; border-radius: 5px; }
        .check.success { background: #d4edda; border-left: 5px solid #28a745; }
        .check.error { background: #f8d7da; border-left: 5px solid #dc3545; }
        .check.warning { background: #fff3cd; border-left: 5px solid #ffc107; }
        .check.info { background: #d1ecf1; border-left: 5px solid #17a2b8; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .label { font-weight: bold; display: inline-block; min-width: 200px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
<h1>🔍 System Diagnostic</h1>

<?php

try {
    $db = Database::getInstance()->getConnection();

    // 1. Database Connection
    echo "<h2>1. Database Connection</h2>";
    echo "<div class='check success'>✅ Database connected successfully</div>";

    // 2. AI Provider Settings
    echo "<h2>2. AI Provider Settings</h2>";
    require_once __DIR__ . '/models/AIProviderSetting.php';
    $settingsModel = new AIProviderSetting();
    $providers = $settingsModel->getAllWithStats();

    if (empty($providers)) {
        echo "<div class='check error'>❌ No AI providers configured!</div>";
    } else {
        echo "<table>";
        echo "<tr><th>Provider</th><th>Status</th><th>API Key</th><th>Priority</th><th>Success Rate</th><th>Requests</th><th>Last Error</th></tr>";

        $hasActiveProvider = false;
        foreach ($providers as $p) {
            $status = $p['is_active'] ? '✅ Active' : '❌ Inactive';
            $apiKey = !empty($p['api_key']) ? '✅ Configured' : '❌ Missing';
            $successRate = $p['success_rate'] . '%';
            $totalRequests = $p['total_requests'];
            $lastError = $p['last_error'] ?? '-';

            if ($p['is_active'] && !empty($p['api_key'])) {
                $hasActiveProvider = true;
            }

            $rowClass = ($p['is_active'] && !empty($p['api_key'])) ? '' : 'style="background: #f8d7da;"';

            echo "<tr $rowClass>";
            echo "<td>{$p['display_name']}</td>";
            echo "<td>$status</td>";
            echo "<td>$apiKey</td>";
            echo "<td>{$p['priority']}</td>";
            echo "<td>$successRate</td>";
            echo "<td>$totalRequests</td>";
            echo "<td>" . substr($lastError, 0, 50) . (strlen($lastError) > 50 ? '...' : '') . "</td>";
            echo "</tr>";
        }
        echo "</table>";

        if (!$hasActiveProvider) {
            echo "<div class='check error'>❌ No active provider with API key configured!</div>";
        } else {
            echo "<div class='check success'>✅ At least one provider is ready</div>";
        }
    }

    // 3. Background Jobs
    echo "<h2>3. Background Jobs Status</h2>";
    $stmt = $db->query("SELECT status, COUNT(*) as count FROM background_jobs GROUP BY status");
    $jobStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($jobStats)) {
        echo "<div class='check info'>ℹ️ No jobs found</div>";
    } else {
        echo "<table>";
        echo "<tr><th>Status</th><th>Count</th></tr>";
        foreach ($jobStats as $stat) {
            echo "<tr><td>{$stat['status']}</td><td>{$stat['count']}</td></tr>";
        }
        echo "</table>";
    }

    // Recent jobs
    $stmt = $db->query("SELECT * FROM background_jobs ORDER BY id DESC LIMIT 5");
    $recentJobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>Recent Jobs:</h3>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Type</th><th>Status</th><th>Progress</th><th>Items</th><th>Created</th><th>Error</th></tr>";
    foreach ($recentJobs as $job) {
        $error = $job['error_message'] ? substr($job['error_message'], 0, 50) : '-';
        echo "<tr>";
        echo "<td>#{$job['id']}</td>";
        echo "<td>{$job['job_type']}</td>";
        echo "<td>{$job['status']}</td>";
        echo "<td>{$job['progress']}%</td>";
        echo "<td>{$job['processed_items']}/{$job['total_items']}</td>";
        echo "<td>{$job['created_at']}</td>";
        echo "<td>$error</td>";
        echo "</tr>";
    }
    echo "</table>";

    // 4. Test Single Article Generation
    echo "<h2>4. Test Article Generation</h2>";

    $activeProviders = $settingsModel->getActiveProviders();
    if (empty($activeProviders)) {
        echo "<div class='check error'>❌ Cannot test - no active providers</div>";
    } else {
        echo "<div class='check info'>ℹ️ Testing with provider: {$activeProviders[0]['display_name']}</div>";

        // Load AI service
        require_once __DIR__ . '/services/AIService.php';
        $aiService = new AIService();

        try {
            echo "<p>Generating test article... (this may take 10-20 seconds)</p>";
            ob_flush();
            flush();

            $startTime = microtime(true);
            $testArticle = $aiService->generateArticle([
                'city' => 'İstanbul',
                'district' => 'Kadıköy',
                'primary_keyword' => 'lastik',
                'keywords' => ['lastik servisi', 'oto lastik'],
                'word_count' => 400
            ]);
            $duration = round(microtime(true) - $startTime, 2);

            echo "<div class='check success'>";
            echo "✅ Article generated successfully in {$duration}s<br><br>";
            echo "<strong>Title:</strong> {$testArticle['title']}<br>";
            echo "<strong>Meta Title:</strong> {$testArticle['meta_title']}<br>";
            echo "<strong>Content Length:</strong> " . strlen($testArticle['content']) . " chars<br>";
            echo "<strong>Excerpt:</strong> " . substr($testArticle['excerpt'], 0, 100) . "...<br>";
            echo "</div>";

        } catch (Exception $e) {
            echo "<div class='check error'>";
            echo "❌ Test failed: " . $e->getMessage() . "<br><br>";
            echo "<strong>This is the problem!</strong> Fix this before running bulk jobs.<br>";
            echo "</div>";
        }
    }

    // 5. Server Environment
    echo "<h2>5. Server Environment</h2>";
    echo "<div class='check info'>";
    echo "<span class='label'>PHP Version:</span> " . PHP_VERSION . "<br>";
    echo "<span class='label'>Max Execution Time:</span> " . ini_get('max_execution_time') . "s<br>";
    echo "<span class='label'>Memory Limit:</span> " . ini_get('memory_limit') . "<br>";
    echo "<span class='label'>Upload Max:</span> " . ini_get('upload_max_filesize') . "<br>";
    echo "<span class='label'>Post Max:</span> " . ini_get('post_max_size') . "<br>";
    echo "</div>";

    echo "<h2>✅ Diagnostic Complete</h2>";
    echo "<div class='check success'>";
    echo "Check results above. If article generation test passed, bulk processing should work.<br>";
    echo "If test failed, check API keys in <a href='/admin/ai-saglaiyici-ayarlari'>AI Provider Settings</a>.";
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='check error'>";
    echo "❌ Fatal Error: " . $e->getMessage() . "<br><br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}

?>

<p style="text-align: center; margin-top: 40px;">
    <a href="/diagnose.php" style="display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;">🔄 Run Again</a>
    <a href="/admin/ai-saglaiyici-ayarlari" style="display: inline-block; padding: 12px 24px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin-left: 10px;">⚙️ AI Settings</a>
    <a href="/admin/ai-makale-olustur" style="display: inline-block; padding: 12px 24px; background: #17a2b8; color: white; text-decoration: none; border-radius: 5px; margin-left: 10px;">📝 Create Job</a>
</p>

</div>
</body>
</html>
