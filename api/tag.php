<?php
require_once "../DBManager.php";
session_start();

$userid = $_SESSION["user_id"];
if (!isset($userid)) {
    ?>
    {"success": false, "error": "User not logged in"}
    <?php
    return;
}

$user = User::getByID($userid);
if(!$user) {
    ?>
    {"success": false, "error": "User not found"}
    <?php
    return;
}

$contactID = $_GET["contact_id"];
if(!$contactID) {
    ?>
    {"success": false, "error": "Contact ID not provided"}
    <?php
    return;
}
$contactID = intval($contactID);

switch($_SERVER["REQUEST_METHOD"]) {
    case "GET": {
        // @todo: PULL TAGS from specific contact
        http_send_status(501);
        ?>
        Request Not Implemented!
        <?php
        return;
    }
    case "POST": {
        // @todo: ADD TAG to CONTACT HERE
        http_send_status(501);
        ?>
        Request Not Implemented!
        <?php
        return;
    }
    case "DELETE": {
        // @todo: Deleting specific tag from contact
        http_send_status(501);
        ?>
        Request Not Implemented!
        <?php
        return;
    }
    case "PATCH": {
        // Don't worry about implementing this
        http_send_status(405);
        ?>
        Not Supported
        <?php
        return;
    }

    case "PUT": {
        // Don't worry about implementing this
        http_send_status(405);
        ?>
        Not Supported
        <?php
        return;
    }
    default:
        return; // prob OPTIONS/HEAD Method
}