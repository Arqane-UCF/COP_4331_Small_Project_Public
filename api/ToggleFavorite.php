<?php
require_once "../DBManager.php";
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
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

$contactId = intval($_POST["contact_id"]);

// Create contact object and toggle favorite
$contact = new Contact($contactId, "", "", "", "", [], false);
$result = $contact->setFavorite();

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
