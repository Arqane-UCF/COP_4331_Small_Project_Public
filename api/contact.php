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

$contactID = $_GET["id"];
if(!$contactID) {
    ?>
    {"success": false, "error": "Contact ID not provided"}
    <?php
    return;
}
$contactID = intval($contactID);

switch($_SERVER["REQUEST_METHOD"]) {
    case "GET": {
        // @todo: PULL CONTACT DATA HERE
        http_send_status(501);
        ?>
        Request Not Implemented!
        <?php
        return;
    }
    case "POST": {
        // @todo: ADD CONTACT DATA HERE
        http_send_status(501);
        ?>
        Request Not Implemented!
        <?php
        return;
    }
    case "PATCH": {
        if($_SERVER['CONTENT_TYPE'] !== "application/x-www-form-urlencoded") {
            http_send_status(400);
            ?>
                {"success": false, "error": "Content Type must be 'application/x-www-form-urlencoded'"}
            <?php
            return;
        }

        parse_str(file_get_contents('php://input'), $_PATCH);
        if(empty($_PATCH)) {
            http_send_status(400);
            ?>
            {"success": false, "error": "Empty Body"}
            <?php
            return;
        }

        $contact = $user->getContactByID($contactID);
        if($_PATCH["firstName"])
            $contact->setName($_PATCH["firstName"]);
        if($_PATCH["lastName"])
            $contact->setName(lastName: $_PATCH["lastName"]);
        if($_PATCH["email"])
            $contact->setEmail($_PATCH["email"]);
        if($_PATCH["phone"])
            $contact->setPhoneNum($_PATCH["phone"]);
        if($_PATCH["favorite"])
            $contact->setFavorite(boolval($_PATCH["favorite"]));

        if(!$contact->save()) {
            http_send_status(500);
            ?>
                {"success": false, "error": "Contact not saved. Server Issues?"}
            <?php
        }
        ?>
            {"success": true, "message": "Contact successfully Updated"}
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