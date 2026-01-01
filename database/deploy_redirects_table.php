<?php
/**
 * Deploy URL Redirects Table
 *
 * This script creates the url_redirects table needed for the HUB SEO architecture.
 * Run this on production to fix the "Table 'url_redirects' doesn't exist" error.
 *
 * Usage:
 *   php database/deploy_redirects_table.php
 *
 * Or upload to production and visit:
 *   https://lastikciariyorum.com/database/deploy_redirects_table.php
 */

require_once __DIR__ . '/../config/database.php';

// Security: Only allow in development or with a secret key
$isDevEnvironment = ($_SERVER['SERVER_NAME'] ?? '') === 'localhost'
                 || ($_SERVER['SERVER_ADDR'] ?? '') === '127.0.0.1';

$hasSecretKey = isset($_GET['key']) && $_GET['key'] === 'deploy2024redirects';

if (!$isDevEnvironment && !$hasSecretKey) {
    http_response_code(403);
    die("Access denied. Use: ?key=deploy2024redirects");
}

echo "<pre>";
echo "=== URL Redirects Table Deployment ===\n\n";

try {
    // Create PDO connection
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    echo "✓ Database connected: " . DB_NAME . "\n\n";

    // Check if table already exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'url_redirects'");
    $exists = $stmt->fetch();

    if ($exists) {
        echo "⚠ Table 'url_redirects' already exists!\n";
        echo "  Nothing to do.\n\n";

        // Show table info
        $count = $pdo->query("SELECT COUNT(*) FROM url_redirects")->fetchColumn();
        echo "  Current redirects count: {$count}\n";
    } else {
        echo "Creating table 'url_redirects'...\n";

        // Read SQL file
        $sqlFile = __DIR__ . '/create_redirects_table.sql';
        if (!file_exists($sqlFile)) {
            throw new Exception("SQL file not found: {$sqlFile}");
        }

        $sql = file_get_contents($sqlFile);
        $pdo->exec($sql);

        echo "✓ Table 'url_redirects' created successfully!\n\n";

        // Verify table structure
        $columns = $pdo->query("DESCRIBE url_redirects")->fetchAll();
        echo "Table structure:\n";
        foreach ($columns as $col) {
            echo "  - {$col['Field']} ({$col['Type']})\n";
        }
    }

    echo "\n=== Deployment Complete ===\n";
    echo "The site should now work without the PDOException error.\n";

} catch (PDOException $e) {
    echo "✗ Database Error: " . $e->getMessage() . "\n";
    echo "  Code: " . $e->getCode() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "</pre>";
