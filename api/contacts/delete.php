<?php
declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../DBManager.php';
session_start();

$userId = $_SESSION['user_id'] ?? $_SESSION['uid'] ?? null;
if (!$userId) {
  http_response_code(401);
  echo json_encode(['success' => false, 'error' => 'Not authenticated']); exit;
}

$id = $_POST['id'] ?? $_GET['id'] ?? null;
$id = is_numeric($id) ? (int)$id : 0;
if ($id <= 0) {
  http_response_code(400);
  echo json_encode(['success' => false, 'error' => 'Missing or invalid id']); exit;
}

$db = DBGlobal::getRawDB();

// Verify the contact belongs to this user
$chk = $db->prepare("SELECT id FROM contacts WHERE id = ? AND ownerid = ?");
$chk->bind_param("ii", $id, $userId);
$chk->execute();
$own = $chk->get_result()->fetch_row();
$chk->close();

if (!$own) {
  http_response_code(404);
  echo json_encode(['success' => false, 'error' => 'Contact not found']); exit;
}

// Delete associated tags first (if no FK cascade)
$delTags = $db->prepare("DELETE FROM tags WHERE contactid = ?");
$delTags->bind_param("i", $id);
$delTags->execute();
$delTags->close();

// Delete the contact
$del = $db->prepare("DELETE FROM contacts WHERE id = ?");
$del->bind_param("i", $id);
$ok = $del->execute();
$aff = $del->affected_rows;
$del->close();

if (!$ok || $aff === 0) {
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => 'Delete failed']); exit;
}

echo json_encode(['success' => true, 'id' => $id]); exit;
