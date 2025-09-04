<?php
require_once "../DBManager.php";
session_start();

if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
}

if (!isset($_POST["username"]) || !isset($_POST["password"])) {
    ?>
    {
        "error": "Registration failed"
    }
    <?php
    return;
}

$username = $_POST["username"];
$password = $_POST["password"];

$registration = User::register(username,password);

if (!isset($registration)) {
    ?>
    {
        "error": "Registration failed"
    } 
    <?php
    return;
}

$_SESSION["user_id"] = $registration->id;






?>

{
    "success": "Registration successful"
}
