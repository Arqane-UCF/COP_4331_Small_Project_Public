<?php
// Add require_once "/var/www/html/loader.php";
// Main header File for all PHP endpoint

require_once "vendor/autoload.php";

// Load .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Configure Sentry
$S_DSN = getenv('SENTRY_DSN');
if($S_DSN !== false)
    \Sentry\init([
        'dsn' => $S_DSN,
        'traces_sample_rate' => 1.0,
        'profiles_sample_rate' => 1.0,
        'enable_logs' => true,
    ]);

// Database conn
$mysql = mysqli_connect("localhost", getenv("DB_USERNAME"), getenv("DB_PASSWORD"), getenv("DB_USERNAME"));
if(!$mysql)
    exit("Database cannot be reached");