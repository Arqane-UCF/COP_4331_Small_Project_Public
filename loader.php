<?php
// Add 《require_once "/var/www/html/loader.php";》 to the top of the php code
// Or you can only just add 《require_once "/var/www/html/DBManager.php";》 if database interaction is needed
// Add 《\Sentry\logger()->flush();》 to the bottom of the php code (except for library/component php codes)


// Main header File for all PHP endpoint
require_once "vendor/autoload.php";

// Load .env
$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();

// Configure Sentry
$S_DSN = getenv('SENTRY_DSN');
if($S_DSN !== false) {
    \Sentry\init([
            'dsn' => $S_DSN,
            'send_default_pii' => true,
            'traces_sample_rate' => 1.0,
            'profiles_sample_rate' => 1.0,
            'enable_logs' => true,
    ]);
}

// Exit Handler
register_shutdown_function(function() {
    \Sentry\logger()->flush();
});