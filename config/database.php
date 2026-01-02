<?php

return [
    'host' => envRequired('DB_HOST'),
    'dbname' => envRequired('DB_NAME'),
    'username' => envRequired('DB_USER'),
    'password' => env('DB_PASS', ''),
    'charset' => env('DB_CHARSET', 'utf8mb4'),
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        // MySQL bağlantı zaman aşımını artır (AI istekleri uzun sürebilir)
        PDO::ATTR_TIMEOUT => 120, // 2 dakika
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_turkish_ci, SESSION wait_timeout=120, interactive_timeout=120",
    ]
];
