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
        if(empty($_POST["firstName"]) || empty($_POST["lastName"]) || empty($_POST["email"]) || empty($_POST["phone"])) {
            http_response_code(400);
            echo json_encode(["success" => false, "error" => "All fields (firstName, lastName, email, phone) are required"]);
            return;
        }

        $firstName = trim($_POST["firstName"]);
        $lastName = trim($_POST["lastName"]);
        $email = trim($_POST["email"]);
        $phone = trim($_POST["phone"]);

        $newContact = Contact::create($user->id, $firstName, $lastName, $email, $phone, false);
        
        if(!$newContact) {
            http_response_code(500);
            echo json_encode(["success" => false, "error" => "Failed to create contact"]);
            return;
        }

        echo json_encode([
            "success" => true,
            "message" => "Contact created successfully",
            "contact" => [
                "id" => $newContact->id,
                "firstName" => $newContact->firstName,
                "lastName" => $newContact->lastName,
                "email" => $newContact->email,
                "phone" => $newContact->phoneNum,
            ]
        ]);
        return;
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
            http_response_code(400);
            ?>
                {"success": false, "error": "Content Type must be 'application/x-www-form-urlencoded'"}
            <?php
            return;
        }

        parse_str(file_get_contents('php://input'), $_PATCH);
        if(empty($_PATCH)) {
            http_response_code(400);
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
            http_response_code(500);
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
            http_response_code(404);
            ?>
            {"success": false, "error": "Contact not found"}
            <?php
            return;
        }

        $contact->destroy();
        http_response_code(200);
        ?>
        {"success": true, "message": "Contact Deleted!"}
        <?php
        return;
    }
    case "PUT": {
        // Don't worry about implementing this
        http_response_code(405);
        ?>
        Not Supported
        <?php
        return;
    }
    default:
        return; // prob OPTIONS/HEAD Method
}