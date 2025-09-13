<?php
require_once "../DBManager.php";
session_start();

// Check if user is logged in
$userID = $_SESSION["user_id"];
if (!isset($userID)) {
    ?>
    {"success": false, "error": "User not logged in"}
    <?php
    \Sentry\logger()->flush();
    return;
}

// Check if contact_id is provided
if (!isset($_POST["contact_id"])) {
    ?>
    {"success": false, "error": "Missing contact_id"}
    <?php
    \Sentry\logger()->flush();
    return;
}

// Create contact object and toggle favorite
$user = User::getByID($userID);
if(!$user) {
    ?>
        {"success": false, "error": "User not found"}
    <?php
    return;
}

$result = $user
        ->getContactByID(intval($_POST["contact_id"]))
        ->setFavorite();

if ($result) {
    ?>
    {"success": true, "message": "Favorite status updated"}
    <?php
} else {
    ?>
    {"success": false, "error": "Failed to update favorite status"}
    <?php
}

\Sentry\logger()->flush();
?>
