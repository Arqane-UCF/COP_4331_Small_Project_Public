<?php
// api/contacts/toggle-favorite.php
require_once __DIR__ . '/../../DBManager.php';
session_start();
header('Content-Type: application/json; charset=utf-8');

$userId = $_SESSION['user_id'] ?? $_SESSION['uid'] ?? null;
$id = (int)($_POST['id'] ?? 0);
$fav = (int)(($_POST['favorited'] ?? '0') === '1');
if (!$userId || !$id) { http_response_code(400); echo json_encode(['success'=>false,'error'=>'Bad request']); exit; }

$db = DBGlobal::getRawDB();
$stmt = $db->prepare("UPDATE contacts SET favorite=? WHERE id=? AND ownerid=?");
$stmt->bind_param("iii", $fav, $id, $userId);
$stmt->execute();

echo json_encode(['success'=>true,'favorited'=>$fav]);
