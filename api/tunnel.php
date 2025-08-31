<?php
// Sentry Proxy Service for front-end

// Read raw envelope payload from request body
$envelope = file_get_contents('php://input');

// Parse the first line (envelope header) to get the DSN and project ID
$lines = preg_split("/\r\n|\n|\r/", $envelope, 2);
$header = json_decode($lines[0] ?? "{}", true);
if (!isset($header['dsn'])) {
    http_response_code(400);
    exit('Missing DSN in envelope header');
}

$dsn = parse_url(getenv('SENTRY_DSN'));
$projectId = intval(trim($dsn['path'] ?? '', '/'));

// Optional: restrict to known project IDs for safety
$knownProjectIds = [$projectId]; // replace with your project ID(s)
if (!in_array($projectId, $knownProjectIds, true)) {
    http_response_code(403);
    exit('Unknown project');
}

// IMPORTANT: Use your org’s ingest host (from your DSN), not just sentry.io
// Example DSN host: o324674.ingest.sentry.io
$host = $dsn['host'] ?? '';
if ($host === '') {
    http_response_code(400);
    exit('Invalid DSN host');
}

// Forward the envelope to Sentry
$ctx = stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/x-sentry-envelope\r\n",
        'content' => $envelope,
        'timeout' => 5,
    ],
]);

$result = @file_get_contents("https://{$host}/api/{$projectId}/envelope/", false, $ctx);
$ok = $result !== false;

// Respond with Sentry’s response or a generic OK
http_response_code($ok ? 200 : 502);
echo $ok ? $result : 'Upstream error';