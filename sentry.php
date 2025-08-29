<?php
// Add require_once "/var/www/html/sentry.php";
// Error Logger toolkit

require_once "vendor/autoload.php";

\Sentry\init([
  'dsn' => 'https://b04e1712f35bd482f7705e163822dec5@o125145.ingest.us.sentry.io/4509924680859648',
  'traces_sample_rate' => 1.0,
  'profiles_sample_rate' => 1.0,
  'enable_logs' => true,
]);
?>
<script>
        console.log("~PHP Sentry Enabled~");
</script>