<?php
require_once "../DBManager.php";
session_start();
header('Content-Type: application/json; charset=utf-8');

if (isset($_SESSION["user_id"])) {
    echo json_encode(["success" => true, "redirect" => "/views/dashboard.php"]);
    exit;
}

if (
    !isset($_POST["username"]) ||
    !isset($_POST["password"]) ||
    !isset($_POST["confirm"])
) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Missing fields"]);
    exit;
}

$username = trim((string)$_POST["username"]);
$password = (string)$_POST["password"];
$confirm  = (string)$_POST["confirm"];

if ($username === "" || $password === "") {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Username and password are required"]);
    exit;
}

if ($password !== $confirm) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Passwords do not match"]);
    exit;
}

$registration = User::register($username, $password);

if (!$registration) {
    http_response_code(409);
    echo json_encode(["success" => false, "error" => "Username already exists or registration failed"]);
    exit;
}

$_SESSION["user_id"]  = $registration->id;
$_SESSION["username"] = $registration->username; 

echo json_encode([
    "success"  => true,
    "message"  => "Registration successful",
    "redirect" => "/views/dashboard.php"
]);
exit;
