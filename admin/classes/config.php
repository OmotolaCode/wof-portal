<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'wof_training_portal');

// Application configuration
define('APP_NAME', 'Whoba Ogo Foundation');
define('APP_URL', 'http://localhost/wof-portal');
define('UPLOAD_PATH', 'uploads/');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
ini_set('session.use_strict_mode', 1);
session_start();

// Timezone
date_default_timezone_set('UTC');
?>