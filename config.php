<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'ampibet_db');

// Application Settings
define('COIN_RATE', 6.5); // Rate: Rp100.000 = 650.000 coins (multiplier 6.5)

// Allowed IPs for API Access (Maximum 2 IPs)
$allowed_ips = [
    '127.0.0.1',    // Localhost
    '::1'           // IPv6 localhost
    // Add or modify IPs as needed, but keep maximum 2
];

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();