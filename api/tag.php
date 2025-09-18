<?php
require_once "../DBManager.php";
session_start();

$userid = $_SESSION["user_id"] ?? null;
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

$contactID = $_GET["contact_id"] ?? null;


switch($_SERVER["REQUEST_METHOD"]) {
    case "GET": {
        // If contact_id is not provided, return all unique tag names for the user
        if(!$contactID) {
            
            $statement = DBGlobal::getRawDB()->prepare("
                SELECT DISTINCT t.value 
                FROM tags t 
                INNER JOIN contacts c ON t.contactid = c.id 
                WHERE c.ownerid = ?
            ");
            $statement->bind_param("i", $userid);

            if(!$statement->execute()) {
                http_response_code(500);
                ?>
                {"success": false, "error": "Database error"}
                <?php
                return;
            }

            $result = $statement->get_result();
            $tags = [];
            while($tag = $result->fetch_assoc()) {
                $tags[] = $tag["value"];
            }

            http_response_code(200);
            ?>
            {"success": true, "tags": <?php echo json_encode($tags); ?>}
            <?php
            return;
        }

        if($contactID) {
            $contactID = intval($contactID);
        }
        // Verify contact exists and belongs to user
        $contact = $user->getContactByID($contactID);
        if(!$contact) {
            http_response_code(404);
            ?>
            {"success": false, "error": "Contact not found"}
            <?php
            return;
        }

        // Get tags for the contact
        $statement = DBGlobal::getRawDB()->prepare("SELECT id, value FROM tags WHERE contactid = ?");
        $statement->bind_param("i", $contactID);

        if(!$statement->execute()) {
            http_response_code(500);
            ?>
            {"success": false, "error": "Database error"}
            <?php
            return;
        }

        $result = $statement->get_result();
        $tags = [];
        while($tag = $result->fetch_assoc()) {
            $tags[] = array(
                "id" => intval($tag["id"]),
                "value" => $tag["value"]
            );
        }

        http_response_code(200);
        ?>
        {"success": true, "tags": <?php echo json_encode($tags); ?>}
        <?php
        return;
    }

    case "POST": {
        $tagValue = $_POST["value"] ?? null;
        if(!$tagValue) {
            http_response_code(400);
            ?>
            {"success": false, "error": "Contact ID not provided"}
            <?php
            return;
        }

        // Verify contact exists and belongs to user
        $contact = $user->getContactByID($contactID);
        if(!$contact) {
            http_response_code(404);
            ?>
            {"success": false, "error": "Contact not found"}
            <?php
            return;
        }

        // Add tag to contact
        $statement = DBGlobal::getRawDB()->prepare("INSERT INTO tags (contactid, value) VALUES (?, ?)");
        $statement->bind_param("is", $contactID, $tagValue);

        if(!$statement->execute()) {
            http_response_code(500);
            ?>
            {"success": false, "error": "Database error"}
            <?php
            return;
        }

        http_response_code(200);
        ?>
        {"success": true, "message": "Tag added successfully", "tag_id": <?php echo $statement->insert_id; ?>}
        <?php
        return;
    }

    case "DELETE": {
        $tagID = $_GET["id"] ?? null;
        if(!$tagID) {
            http_response_code(400);
            ?>
            {"success": false, "error": "Tag ID not provided"}
            <?php
            return;
        }
        $tagID = intval($tagID);

        // Verify tag exists and belongs to a contact owned by the user
        $statement = DBGlobal::getRawDB()->prepare("
            SELECT t.id FROM tags t 
            INNER JOIN contacts c ON t.contactid = c.id 
            WHERE t.id = ? AND c.ownerid = ?
        ");
        $statement->bind_param("ii", $tagID, $userid);

        if(!$statement->execute()) {
            http_response_code(500);
            ?>
            {"success": false, "error": "Database error"}
            <?php
            return;
        }

        if($statement->get_result()->num_rows === 0) {
            http_response_code(404);
            ?>
            {"success": false, "error": "Tag not found"}
            <?php
            return;
        }

        // Delete the tag
        $deleteStatement = DBGlobal::getRawDB()->prepare("DELETE FROM tags WHERE id = ?");
        $deleteStatement->bind_param("i", $tagID);

        if(!$deleteStatement->execute()) {
            http_response_code(500);
            ?>
            {"success": false, "error": "Database error"}
            <?php
            return;
        }

        if($deleteStatement->affected_rows === 0) {
            http_response_code(404);
            ?>
            {"success": false, "error": "Tag not found"}
            <?php
            return;
        }

        http_response_code(200);
        ?>
        {"success": true, "message": "Tag removed successfully"}
        <?php
        return;
    }

    case "PUT":
    case "PATCH": {
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
