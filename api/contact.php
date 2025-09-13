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
        // PULL CONTACT DATA HERE
        http_send_status(501);
        ?>
        Request Not Implemented!
        <?php
        return;
    }
    case "POST": {
        // ADD CONTACT DATA HERE
        http_send_status(501);
        ?>
        Request Not Implemented!
        <?php
        return;
    }
    case "PATCH": {
        http_send_status(501);
        ?>
        Request Not Implemented!
        <?php
        return;
    }
    case "DELETE": {
        $contact = $user->getContactByID($contactID);
        if(!$contact) {
            http_send_status(404);
            ?>
            {"success": false, "error": "Contact not found"}
            <?php
            return;
        }

        $contact->destroy();
        http_send_status(200);
        ?>
        {"success": true, "message": "Contact Deleted!"}
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