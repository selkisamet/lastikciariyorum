<?php

return [
    'site_name' => envRequired('APP_NAME'),
    'site_url' => envRequired('APP_URL'),
    'base_path' => env('BASE_PATH', ''),
    'upload_dir' => __DIR__ . '/../public/uploads/',
    'upload_url' => '/uploads/',
    'items_per_page' => (int) env('ITEMS_PER_PAGE', 12),
    'session_lifetime' => (int) env('SESSION_LIFETIME', 7200),
    'debug' => env('APP_DEBUG', false),
    'env' => envRequired('APP_ENV'),
];
