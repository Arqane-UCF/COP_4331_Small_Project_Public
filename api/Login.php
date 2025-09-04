<?php
require_once "../DBManager.php";
session_start();

if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
}

if (!isset($_POST["username"]) || !isset($_POST["password"])) {
    ?>
    {
        "error": "Login failed"
    }
    <?php
    return;
}

$username = $_POST["username"];
$password = $_POST["password"];

$Login = User::login(username,password);

if (!isset($Login)) {
    ?>
    {
        "error": "Login failed"
    } 
    <?php
    return;
}

$_SESSION["user_id"] = $Login->id;






?>

{
    "success": "Login successful"
}
