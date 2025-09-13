<?php
require_once "../DBManager.php";
session_start();

if (isset($_SESSION["user_id"])) {
    ?>
    {"success": false, "error": "User already logged in"}
    <?php
    return;
}

if (!isset($_POST["username"]) || !isset($_POST["password"])) {
    http_response_code(400);
    ?>
    { "success": false, "error": "Missing Fields" }
    <?php
    return;
}

$username = $_POST["username"];
$password = $_POST["password"];
$registration = User::register($username,$password);

if (!isset($registration)) {
    http_response_code(403);
    ?>
    {"success": false, "error": "Registration failed, perhaps duplicate username?"}
    <?php
    return;
}

$_SESSION["user_id"] = $registration->id;
?>
{ "success": true, "message": "Registration successful" }
