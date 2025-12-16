<?php
// Set 410 Gone status code
http_response_code(410);

// Load helper functions
require_once __DIR__ . '/../core/helpers.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load the 410 error view
require_once __DIR__ . '/../views/errors/410.php';
