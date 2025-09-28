<?php
declare(strict_types=1);

// Capture ANY accidental output so we can still return JSON
ob_start();

// If a fatal error happens, convert it into JSON
register_shutdown_function(function () {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        while (ob_get_level() > 0) ob_end_clean();
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'Fatal: '.$e['message']]);
    }
});

ini_set('display_errors', '1');
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../DBManager.php';
session_start();

$userId = $_SESSION['user_id'] ?? $_SESSION['uid'] ?? null;
if (!$userId) {
    http_response_code(401);
    // discard any noise printed earlier
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

// Inputs
$name  = trim((string)($_POST['name']  ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$phone = trim((string)($_POST['phone'] ?? ''));
$tags  = $_POST['tags'] ?? [];
if (is_string($tags)) $tags = array_filter(array_map('trim', explode(',', $tags)));
if (!is_array($tags)) $tags = [];

if ($name === '') {
    http_response_code(400);
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Name is required']);
    exit;
}

// Split name
$parts = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY);
$first = $parts[0] ?? '';
$last  = implode(' ', array_slice($parts, 1));

// Insert contact
$db = DBGlobal::getRawDB();
$insC = $db->prepare("INSERT INTO contacts (ownerid, firstName, lastName, email, phoneNum, favorite) VALUES (?, ?, ?, ?, ?, 0)");
$insC->bind_param("issss", $userId, $first, $last, $email, $phone);
if (!$insC->execute()) {
    http_response_code(500);
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Failed to create contact']);
    exit;
}
$contactId = $insC->insert_id;
$insC->close();

// Insert tags (your current schema: tags(contactid,value))
$tags = array_values(array_unique(array_filter(array_map('trim', $tags))));
if (!empty($tags)) {
    $insT = $db->prepare("INSERT INTO tags (contactid, value) VALUES (?, ?)");
    foreach ($tags as $t) {
        if ($t === '') continue;
        $t = mb_substr($t, 0, 64);
        $insT->bind_param("is", $contactId, $t);
        $insT->execute();
    }
    $insT->close();
}

// Render the card to a *separate* buffer
$cardHtml = (function() use ($contactId, $first, $last, $email, $phone, $tags) {
    ob_start();
    $c = [
        'id'         => $contactId,
        'name'       => trim("$first $last"),
        'email'      => $email,
        'phone'      => $phone,
        'favorited'  => 0,
        'created_at' => date('Y-m-d H:i:s'),
        'tags'       => $tags
    ];
    require __DIR__ . '/../../assets/components/contactCard.php';
    return ob_get_clean();
})();

// Discard ANY stray output before we send JSON
ob_clean();

echo json_encode([
    'success'   => true,
    'id'        => $contactId,
    'card_html' => $cardHtml
]);
exit;
