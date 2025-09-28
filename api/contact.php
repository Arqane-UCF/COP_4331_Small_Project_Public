<?php
require_once "../DBManager.php";
session_start();


header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0'); // avoid HTML in API responses

function read_input_array(): array {
    $raw = file_get_contents('php://input') ?: '';
    $ct  = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($ct, 'application/json') !== false) {
        $d = json_decode($raw, true);
        return is_array($d) ? $d : [];
    }
    parse_str($raw, $d);
    return is_array($d) ? $d : [];
}


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
    {"success": false, "error": "User not found", "DEBUGID": "<?php echo $userid; ?>"}
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
        if(empty($_POST["firstName"]) || empty($_POST["email"]) || empty($_POST["phone"])) {
            http_response_code(400);
            echo json_encode(["success" => false, "error" => "All fields (firstName, email, phone) are required"]);
            return;
        }

        $firstName = trim($_POST["firstName"]);
        $lastName = $_POST["lastName"] ? trim($_POST["lastName"]) : null;
        $email = trim($_POST["email"]);
        $phone = trim($_POST["phone"]);

        $newContact = Contact::create($user->id, $firstName, $email, $phone, $lastName, false);
        
        if(!$newContact) {
            http_response_code(500);
            echo json_encode(["success" => false, "error" => "Failed to create contact"]);
            return;
        }

        echo json_encode([
            "success" => true,
            "message" => "Contact created successfully",
            "id" => $newContact->id,
        ]);
        return;
    }
    case 'PATCH': {
        // Parse PATCH body (x-www-form-urlencoded or JSON)
        $data = read_input_array();
    
        // id & auth
        $contactID = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$contactID) { http_response_code(400); echo json_encode(["success"=>false,"error"=>"Missing id"]); return; }
    
        // Build column map from incoming fields
        $updates = [];
    
        if (array_key_exists('firstName', $data)) $updates['firstName'] = trim((string)$data['firstName']);
        if (array_key_exists('lastName',  $data)) $updates['lastName']  = trim((string)$data['lastName']);
        if (array_key_exists('email',     $data)) $updates['email']     = trim((string)$data['email']);
    
        // map front-end "phone" to DB column "phoneNum"
        if (array_key_exists('phone',     $data)) $updates['phoneNum']  = trim((string)$data['phone']);
    
        // accept either "favorite" or "favorited"
        if (array_key_exists('favorite',  $data) || array_key_exists('favorited', $data)) {
            $favRaw = $data['favorite'] ?? $data['favorited'];
            $updates['favorite'] = ((string)$favRaw === '1' || $favRaw === 1 || $favRaw === true || $favRaw === 'true') ? 1 : 0;
        }
    
        if (!$updates) { http_response_code(400); echo json_encode(["success"=>false,"error"=>"No fields to update"]); return; }
    
        // Build dynamic UPDATE
        $sets  = [];
        $types = '';
        $vals  = [];
        foreach ($updates as $col => $val) {
            $sets[]  = "$col = ?";
            $types  .= is_int($val) ? 'i' : 's';
            $vals[]  = $val;
        }
        $types .= 'ii';
        $vals[] = $contactID;
        $vals[] = $userid; // must already be defined from session earlier in file
    
        $sql = "UPDATE contacts SET ".implode(', ', $sets)." WHERE id = ? AND ownerid = ?";
        $db  = DBGlobal::getRawDB();
        $stmt = $db->prepare($sql);
        if (!$stmt) { http_response_code(500); echo json_encode(["success"=>false,"error"=>"Prepare failed"]); return; }
    
        // bind_param with dynamic args requires references
        $bind = [];
        $bind[] = &$types;
        foreach ($vals as $k => $v) { $bind[] = &$vals[$k]; }
        call_user_func_array([$stmt, 'bind_param'], $bind);
    
        if (!$stmt->execute()) { http_response_code(500); echo json_encode(["success"=>false,"error"=>"Update failed"]); return; }
    
        echo json_encode(["success"=>true, "id"=>$contactID] + $updates);
        return;
    }
    
    case "DELETE": {
        if(!$contactID) {
            http_response_code(400);
            echo json_encode(["success" => false, "error" => "Contact ID not provided"]);
            return;
        }
    
        $db  = DBGlobal::getRawDB();
        $uid = (int)$user->id; // via __get on User
    
        // Delete only if this contact belongs to the logged-in user
        $stmt = $db->prepare("DELETE FROM contacts WHERE id = ? AND ownerid = ?");
        if (!$stmt) {
            http_response_code(500);
            echo json_encode(["success" => false, "error" => "Prepare failed"]);
            return;
        }
    
        $stmt->bind_param("ii", $contactID, $uid);
        if (!$stmt->execute()) {
            http_response_code(500);
            echo json_encode(["success" => false, "error" => "Delete failed"]);
            return;
        }
    
        if ($stmt->affected_rows === 0) {
            http_response_code(404);
            echo json_encode(["success" => false, "error" => "Contact not found"]);
            return;
        }
    
        http_response_code(200);
        echo json_encode(["success" => true, "message" => "Contact Deleted!"]);
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
