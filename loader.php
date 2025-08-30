<?php
// Add require_once "/var/www/html/loader.php";
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
?>
    <!-- Frontend Sentry loader stuff -->
    <script src="/scripts/sentry.js" data-lazy="no"></script>
    <script>
        if(window.Sentry) {
            Sentry.init({
                dsn: "<?php echo $S_DSN; ?>",
                integrations: [
                    Sentry.browserTracingIntegration(),
                    Sentry.replayIntegration()
                ],
                tracesSampleRate: 1.0, // My account has the budget LOL
                replaysSessionSampleRate: 0.0, // Useless
                replaysOnErrorSampleRate: 1.0, // Might have enough budget
                sendDefaultPii: true,
                enableLogs: true,
                tunnel: "/tunnel.php"
                ignoreErrors: [
                    "jQuery",
                    "Failed to fetch",
                    "EADDRINUSE"
                ]
            });
            console.log("Sentry Loaded Done ^w^");
        }

    </script>
<?php
}
?>