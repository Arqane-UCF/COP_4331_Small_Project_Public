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
    return;
}

$username = $_POST["username"];
$password = $_POST["password"];
$Login = User::login($username,$password);

if (!isset($Login)) {
    ?>
    {"success": false, "error": "Invalid Username/Password"} 
    <?php
    return;
}

$_SESSION["user_id"] = $Login->id;
?>
{"success": true, "message": "Login successful"}

