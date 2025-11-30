<?php
// db.php
// Loads DB credentials from .env when vlucas/phpdotenv is installed.
// Falls back to the original hardcoded values if .env or phpdotenv is not available.

$default_host = 'localhost';
$default_user = 'root';
$default_pass = '';
$default_db   = 'foothaven';

$host = $default_host;
$user = $default_user;
$pass = $default_pass;
$db   = $default_db;

// If Composer autoload exists and phpdotenv is installed, load .env values safely
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    if (class_exists('Dotenv\Dotenv')) {
        // Create and load the .env file (safeLoad won't throw if file missing)
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->safeLoad();

        $host = getenv('DB_HOST') !== false ? getenv('DB_HOST') : $host;
        $user = getenv('DB_USER') !== false ? getenv('DB_USER') : $user;
        $pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : $pass;
        $db   = getenv('DB_NAME') !== false ? getenv('DB_NAME') : $db;
    }
}

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Note: To enable .env loading, run in your project root:
// composer require vlucas/phpdotenv
?>