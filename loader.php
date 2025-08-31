<?php
// Add require_once "/var/www/html/loader.php"; to the top of the php code
// Add \Sentry\logger()->flush(); to the bottom of the php code (except for library/component php codes)


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
?>

<!-- Frontend Sentry loader stuff -->
<script src="https://browser.sentry-cdn.com/10.8.0/bundle.tracing.replay.min.js" crossorigin="anonymous" on-lazy="no"></script>
<script src="/scripts/loader.js"></script>
