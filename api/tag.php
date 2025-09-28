<?php
declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../DBManager.php';
session_start();

$userid = $_SESSION['user_id'] ?? null;
if (!$userid) {
  http_response_code(401);
  echo json_encode(['success' => false, 'error' => 'User not logged in']);
  exit;
}

$db = DBGlobal::getRawDB();
$method = $_SERVER['REQUEST_METHOD'];
$contactID = isset($_GET['contact_id']) ? (int)$_GET['contact_id'] : null;

try {
  switch ($method) {

    case 'GET': {
      if (!$contactID) {
        $limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 10;
        $sql = "
          SELECT DISTINCT t.value
          FROM tags t
          INNER JOIN contacts c ON t.contactid = c.id
          WHERE c.ownerid = ?
          ORDER BY LOWER(t.value) ASC
          LIMIT ?
        ";
        $st = $db->prepare($sql);
        $st->bind_param("ii", $userid, $limit);
        if (!$st->execute()) { throw new RuntimeException('Database error'); }
        $res = $st->get_result();
        $tags = [];
        while ($row = $res->fetch_assoc()) { $tags[] = (string)$row['value']; }
        echo json_encode(['success' => true, 'tags' => $tags]);
        exit;
      }

      $st = $db->prepare("
        SELECT t.id, t.value
        FROM tags t
        INNER JOIN contacts c ON c.id = t.contactid
        WHERE t.contactid = ? AND c.ownerid = ?
        ORDER BY LOWER(t.value) ASC
      ");
      $st->bind_param("ii", $contactID, $userid);
      if (!$st->execute()) { throw new RuntimeException('Database error'); }
      $res = $st->get_result();
      $tags = [];
      while ($row = $res->fetch_assoc()) {
        $tags[] = ['id' => (int)$row['id'], 'value' => (string)$row['value']];
      }
      echo json_encode(['success' => true, 'tags' => $tags]);
      exit;
    }

    case 'POST': {
      $contactID = isset($_POST['contact_id']) ? (int)$_POST['contact_id'] : 0;
      $tagValue  = isset($_POST['value']) ? trim((string)$_POST['value']) : '';

      if ($contactID <= 0 || $tagValue === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing fields']);
        exit;
      }

      $chk = $db->prepare("SELECT id FROM contacts WHERE id = ? AND ownerid = ?");
      $chk->bind_param("ii", $contactID, $userid);
      $chk->execute();
      if (!$chk->get_result()->fetch_row()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Contact not found']);
        exit;
      }

      $tagValue = mb_substr($tagValue, 0, 64);
      $ins = $db->prepare("INSERT INTO tags (contactid, value) VALUES (?, ?)");
      $ins->bind_param("is", $contactID, $tagValue);
      if (!$ins->execute()) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error']);
        exit;
      }

      echo json_encode(['success' => true, 'tag_id' => $ins->insert_id, 'value' => $tagValue]);
      exit;
    }

    case 'DELETE': {
      parse_str($_SERVER['QUERY_STRING'] ?? '', $q);
      $tagID = isset($q['id']) ? (int)$q['id'] : 0;
      if ($tagID <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Tag ID not provided']);
        exit;
      }

      $chk = $db->prepare("
        SELECT t.id
        FROM tags t
        INNER JOIN contacts c ON c.id = t.contactid
        WHERE t.id = ? AND c.ownerid = ?
      ");
      $chk->bind_param("ii", $tagID, $userid);
      $chk->execute();
      if (!$chk->get_result()->fetch_row()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Tag not found']);
        exit;
      }

      $del = $db->prepare("DELETE FROM tags WHERE id = ?");
      $del->bind_param("i", $tagID);
      if (!$del->execute() || $del->affected_rows === 0) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error']);
        exit;
      }

      echo json_encode(['success' => true]);
      exit;
    }

    default:
      http_response_code(405);
      echo json_encode(['success' => false, 'error' => 'Method not allowed']);
      exit;
  }

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
  exit;
}
