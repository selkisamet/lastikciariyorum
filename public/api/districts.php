<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Get city ID from path or query
$cityId = null;

// Try to get from query string first
if (isset($_GET['city_id'])) {
    $cityId = $_GET['city_id'];
}

// If not in query, try to get from path
if (!$cityId && isset($_SERVER['REQUEST_URI'])) {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (preg_match('#/api/districts/(\d+)#', $path, $matches)) {
        $cityId = $matches[1];
    }
}

if (!$cityId) {
    echo json_encode([]);
    exit;
}

// Connect to database
try {
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../core/Database.php';

    $db = Database::getInstance();

    $districts = $db->fetchAll(
        "SELECT id, name FROM districts WHERE city_id = ? ORDER BY name ASC",
        [$cityId]
    );

    echo json_encode($districts);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
