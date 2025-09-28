<?php
require_once "../DBManager.php";
session_start();
header('Content-Type: application/json; charset=utf-8');

if (isset($_SESSION["user_id"])) {
    echo json_encode(["success" => true, "redirect" => "/views/dashboard.php"]);
    exit;
}

if (!isset($_POST["username"]) || !isset($_POST["password"])) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Missing fields"]);
    exit;
}

$username = trim((string)$_POST["username"]);
$password = (string)$_POST["password"];

$login = User::login($username, $password);

if (!$login) {
    http_response_code(401);
    echo json_encode(["success" => false, "error" => "Invalid username or password"]);
    exit;
}

$_SESSION["user_id"]  = $login->id;
$_SESSION["username"] = $login->username; 

echo json_encode([
    "success"  => true,
    "message"  => "Login successful",
    "redirect" => "/views/dashboard.php"
]);
exit;
