<?php

header('Content-Type: application/json');

// CORS ayarları (gerekirse)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// OPTIONS request için
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Sadece POST isteğine izin ver
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit();
}

// JSON verilerini al
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit();
}

// Bootstrap application
session_start();

// Config yükle
require_once __DIR__ . '/../../config/database.php';

// Model ve Database sınıflarını yükle
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/Model.php';
require_once __DIR__ . '/../../models/ViewLog.php';
require_once __DIR__ . '/../../models/CookieConsent.php';

try {
    $cookieConsentModel = new CookieConsent();

    // Çerez tercihlerini kaydet
    $preferences = [
        'analytics' => isset($data['analytics']) && $data['analytics'] === true,
        'marketing' => isset($data['marketing']) && $data['marketing'] === true,
        'preferences' => isset($data['preferences']) && $data['preferences'] === true
    ];

    $consentId = $cookieConsentModel->saveConsent($preferences);

    if ($consentId) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'consent_id' => $consentId,
            'message' => 'Çerez tercihleri kaydedildi'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Çerez tercihleri kaydedilemedi'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Bir hata oluştu: ' . $e->getMessage()
    ]);
}
