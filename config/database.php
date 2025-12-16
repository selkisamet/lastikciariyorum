<?php

return [
    'host' => envRequired('DB_HOST'),
    'dbname' => envRequired('DB_NAME'),
    'username' => envRequired('DB_USER'),
    'password' => env('DB_PASS', ''), // Şifre boş olabilir (localhost için)
    'charset' => env('DB_CHARSET', 'utf8mb4'),
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
