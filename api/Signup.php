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
    ?>
    { "success": false, "error": "Missing Fields" }
    <?php
    \Sentry\logger()->flush();
    return;
}

$username = $_POST["username"];
$password = $_POST["password"];
$registration = User::register($username,$password);

if (!isset($registration)) {
    ?>
    {"success": false, "error": "Registration failed, perhaps duplicate username?"}
    <?php
    \Sentry\logger()->flush();
    return;
}

$_SESSION["user_id"] = $registration->id;
\Sentry\logger()->flush();
?>
{ "success": true, "message": "Registration successful" }
