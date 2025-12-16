<?php

return [
    // SMTP Ayarları
    'smtp' => [
        'host' => envRequired('MAIL_HOST'),
        'port' => (int) env('MAIL_PORT', 587),
        'username' => envRequired('MAIL_USERNAME'),
        'password' => envRequired('MAIL_PASSWORD'),
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
    ],

    // Gönderen Bilgileri
    'from' => [
        'address' => envRequired('MAIL_FROM_ADDRESS'),
        'name' => env('MAIL_FROM_NAME', 'Lastikciariyorum.com'),
    ],

    // Alıcı Bilgileri (İletişim formundan gelen mesajlar için)
    'contact' => [
        'to' => envRequired('MAIL_CONTACT_TO'),
        'subject' => 'Yeni İletişim Formu Mesajı',
    ],

    // Genel Ayarlar
    'charset' => 'UTF-8',
    'debug' => env('APP_DEBUG', false),
];
