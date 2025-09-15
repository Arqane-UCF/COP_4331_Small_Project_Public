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

// Get search parameters (optional)
$firstName = $_GET["firstName"] ?? null;
$lastName = $_GET["lastName"] ?? null;
$contactID = $_GET["id"] ?? null;

// If contactID is provided, convert to int
if($contactID) {
    $contactID = intval($contactID);
}

switch($_SERVER["REQUEST_METHOD"]) {
    case "GET": {
        // If contactID is provided, get specific contact
        if($contactID) {
            $contact = $user->getContactByID($contactID);
            if(!$contact) {
                http_response_code(404);
                echo json_encode(["success" => false, "error" => "Contact not found"]);
                return;
            }
            
            // Combine first and last name into a single field
            $fullName = trim($contact->firstName . " " . $contact->lastName);
            
            http_response_code(200);
            echo json_encode([
                "success" => true,
                "contacts" => [
                    [
                        "id" => $contact->id,
                        "name" => $fullName,
                        "email" => $contact->email,
                        "phone" => $contact->phoneNum,
                        "isFavorite" => $contact->isFavorite
                    ]
                ]
            ]);
            return;
        }
        
        // Otherwise, search contacts by name (or get all if no search parameters)
        $contacts = $user->searchContactByName($firstName, $lastName);
        
        if($contacts === null) {
            http_response_code(500);
            echo json_encode(["success" => false, "error" => "Database error occurred"]);
            return;
        }
        
        // Format contacts for response
        $contactList = [];
        foreach($contacts as $contact) {
            $fullName = trim($contact->firstName . " " . $contact->lastName);
            $contactList[] = [
                "id" => $contact->id,
                "name" => $fullName,
                "email" => $contact->email,
                "phone" => $contact->phoneNum,
                "isFavorite" => $contact->isFavorite
            ];
        }
        
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "contacts" => $contactList
        ]);
        return;
    }
    
    
    case "POST": {
        
    }
    case "PATCH": {
        if(!$contactID) {
            http_response_code(400);
            ?>
            {"success": false, "error": "Contact ID not provided"}
            <?php
            return;
        }

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
        if(!$contactID) {
            http_response_code(400);
            ?>
            {"success": false, "error": "Contact ID not provided"}
            <?php
            return;
        }

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