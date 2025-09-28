<?php
require_once "loader.php";
use function Sentry\logger;

mysqli_report(MYSQLI_REPORT_OFF);
class DBGlobal {
    private static $mysql;
    public static function getRawDB() {
        if(!DBGlobal::$mysql)
            return DBGlobal::$mysql = mysqli_connect("localhost", getenv("DB_USERNAME"), getenv("DB_PASSWORD"), getenv("DB_USERNAME"));
        return DBGlobal::$mysql;
    }
}

class User {
    private int $id;
    private string $username;

    private function __construct($id, $username) {
        $this->id = $id;
        $this->username = $username;
    }
    /** !!!ONLY USE THIS TO PULL USER DATA FROM SESSION!!! */
    public static function getByID(int $id): ?User
    {
        logger()->debug(sprintf("User.getByID: Query for userid %d", $id));
        $statement = DBGlobal::getRawDB()->prepare("SELECT * FROM users WHERE id = ?");
        $statement->bind_param("i", $id);

        if(!$statement->execute()) {
            logger()->error(sprintf("User.getByID: Query for userid %d failed with status: %d", $id, $statement->errno));
            return null;
        }

        $result = $statement->get_result()->fetch_assoc();
        if(!$result) {
            logger()->warn(sprintf("User.getByID: Userid %d not found", $id));
            return null;
        }
        logger()->debug(sprintf("User.getByID: Found Userid %d", $id));
        return new User($result["id"], $result["username"]);
    }
    public static function login(string $username, string $password): ?User
    {
        logger()->debug(sprintf("User.login: Query for user %s", $username));
        $statement = DBGlobal::getRawDB()->prepare("SELECT * FROM users WHERE username = ?");
        $statement->bind_param("s", $username);

        if(!$statement->execute()) {
            logger()->error(sprintf("User.login: Query for user %s failed with status: %d", $username, $statement->errno));
            return null;
        }

        $result = $statement->get_result()->fetch_assoc();
        if(!$result) {
            logger()->info(sprintf("User.login: User %s not found", $username));
            return null;
        }

        if(!password_verify($password, $result["password"])) {
            logger()->info(sprintf("User.login: User %s input incorrect password", $username));
            return null;
        }

        logger()->info(sprintf("User.login: User %s successfully authenticated", $username));
        return new User($result["id"], $result["username"]);
    }

    public static function register(string $username, string $password): ?User
    {
        logger()->debug(sprintf("User.register: Query for user %s", $username));
        $hashpwd = password_hash($password, PASSWORD_ARGON2ID);
        $statement = DBGlobal::getRawDB()->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $statement->bind_param("ss", $username, $hashpwd);

        if($statement->execute()) {
            logger()->info(sprintf("User.register: User %s successfully registered", $username));
            return new User($statement->insert_id, $username);
        }

        if($statement->errno === 1062)
            logger()->info(sprintf("User.register: User %s already existed", $username));
        else
            logger()->error(sprintf("User.register: User %s caused unhandled sql error: %d", $username, $statement->errno));
        return null;
    }

    public function __get($name) {
        switch($name) {
            case "id": return $this->id;
            case "username": return $this->username;
            case "password": throw new Exception("Why would you do this LMAO!! You cant try and get user's password.");
            default: throw new Exception("User Class: Uhhh, unavailable..");
        }
    }
    // API/BACKEND DEV NOTICE: Add your own search functionality if needed and construct/return Contact class as a way to store the info.

    /** Partial match either firstName or lastName or both. If none is supplied, we assume return entire record */
    public function searchContactByName(?string $firstName = null, ?string $lastName = null): ?array {
        $statement = null;
        $fixedQuery = "SELECT * FROM contacts WHERE ownerid=? %s";
        if($firstName === null && $lastName === null) {
            logger()->debug(sprintf("User.searchContactByName: Query without name for userid %s", $this->id));
            $statement = DBGlobal::getRawDB()->prepare(sprintf($fixedQuery, ""));
            $statement->bind_param("i", $this->id);
        }
        if($firstName !== null && $lastName === null) {
            logger()->debug(sprintf("User.searchContactByName: Query with only first name for userid %s", $this->id));
            $statement = DBGlobal::getRawDB()->prepare(sprintf($fixedQuery, "AND firstName LIKE '?%'"));
            $statement->bind_param("is", $this->id, $firstName);
        }
        if($firstName !== null && $lastName === null) {
            logger()->debug(sprintf("User.searchContactByName: Query with only last name for userid %s", $this->id));
            $statement = DBGlobal::getRawDB()->prepare(sprintf($fixedQuery, "AND lastName LIKE '?%'"));
            $statement->bind_param("is", $this->id, $lastName);
        }
        if($firstName !== null && $lastName !== null) {
            logger()->debug(sprintf("User.searchContactByName: Query with both first and last name for userid %s", $this->id));
            $statement = DBGlobal::getRawDB()->prepare(sprintf($fixedQuery, "AND firstName LIKE '?%' AND lastName LIKE '?%'"));
            $statement->bind_param("iss", $this->id, $firstName, $lastName);
        }
        if($statement === null)
            return null; // This shouldn't happen at all lmfao

        if (!$statement->execute()) {
            logger()->error(sprintf("User.searchContactByName: UserID (%d) cause SQL Error: %d", $this->id, $statement->errno));
            return null;
        }

        $result = $statement->get_result();
        logger()->info(sprintf("User.searchContactByName: UserID (%d) found with %d records", $this->id, $result->num_rows));
        $contactLists = array();
        while($contact = $result->fetch_assoc())
            $contactLists[] = new Contact($contact["id"], $contact["firstName"], $contact["lastName"], $contact["email"], $contact["phoneNum"],(bool)$contact["favorite"]);

        return $contactLists;
    }
    public function getContactByID(int $id): ?Contact {
        logger()->debug("User.getContactByID: UserID (%d) query contactID: %d", array($this->id, $id));
        $statement = DBGlobal::getRawDB()->prepare("SELECT * FROM contacts WHERE id=?");
        $statement->bind_param("i", $id);

        if(!$statement->execute()) {
            logger()->error(sprintf("User.getContactByID: UserID (%d) cause SQL Error: %d", $this->id, $statement->errno));
            return null;
        }

        $result = $statement->get_result()->fetch_assoc();
        if(!$result) {
            logger()->warn("User.getContactByID: ContactID (%d) not found", [$id]);
            return null;
        }

        logger()->info("User.getContactByID: Successfully pulled ContactID (%d)", [$id]);
        $tags = array();

        return new Contact($result["id"], $result["firstName"], $result["lastName"], $result["email"], $result["phoneNum"], (bool)$result["favorite"]);
    }
}

class Contact {
    private int $id;
    private string $firstName;
    private ?string $lastName;
    private string $email;
    private string $phoneNum;
    private bool $isFavorite;

    /** !! Do not call it outside of User class !! */
    public function __construct($id, $firstName, $lastName, $email, $phoneNum, $isFavorite)
    {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->phoneNum = $phoneNum;
        $this->isFavorite = $isFavorite;
    }
    public static function create(int $user_id, string $firstName, string $email, string $phoneNum, ?string $lastName = null, ?bool $isFavorite = false): ?Contact {
        logger()->debug("Contact.create: Query for contact %d", [$user_id]);
        $statement = DBGlobal::getRawDB()->prepare("INSERT INTO contacts (ownerid, firstName, lastName, email, phoneNum, favorite) VALUES (?, ?, ?, ?, ?, ?)");
        $favInt = (bool)$isFavorite;
        $statement->bind_param("issssi", $user_id, $firstName, $lastName, $email, $phoneNum, $favInt);

        if(!$statement->execute()) {
            logger()->error("Contact.create: Query failed for userID (%d) with err: %d", [$user_id, $statement->errno]);
            return null;
        }

        if($statement->affected_rows === 0) {
            logger()->error("Failed to create contact for userID %d", [$user_id]);
            return null;
        }

        logger()->info("Contact.create: Successfully added contactID (%d) for user %d", [$statement->insert_id, $user_id]);
        return new Contact(
            $statement->insert_id,
            $firstName,
            $lastName,
            $email,
            $phoneNum,
            $isFavorite
        );
    }
    public function __get($name) {
        switch($name) {
            case "id": return $this->id;
            case "firstName": return $this->firstName;
            case "lastName": return $this->lastName;
            case "email": return $this->email;
            case "phoneNum": return $this->phoneNum;
            case "tags": return $this->tags;
            case "isFavorite": return $this->isFavorite;
            default: throw new Exception("Contact Class: Uhhh can't get this variable...");
        }
    }

    // Stacked Data Update Method
    public function setName(?string $firstName = null, ?string $lastName = null): Contact {
        if($firstName)
            $this->firstName = $firstName;
        if($lastName)
            $this->lastName = $lastName;
        return $this;
    }
    public function setEmail(string $email): Contact {
        $this->email = $email;
        return $this;
    }
    public function setPhoneNum(string $phoneNum): Contact {
        $this->phoneNum = $phoneNum;
        return $this;
    }
    public function setFavorite(bool $isFavorite): Contact {
        $this->isFavorite = $isFavorite;
        return $this;
    }
    public function save(): bool {
        logger()->debug("Contact.save: Query for contactID %d", [$this->id]);
        $statement = DBGlobal::getRawDB()->prepare("UPDATE contacts SET firstName=?, lastName=?, email=?, phoneNum=?, favorite=? WHERE id=?");
        $favInt = (int)$this->isFavorite;
        $statement->bind_param("ssssii", $this->firstName, $this->lastName, $this->email, $this->phoneNum, $favInt, $this->id);

        if(!$statement->execute()) {
            logger()->error("Contact.save: ContactID (%d) caused unhandled sql error: %d", [$this->id, $statement->errno]);
            return false;
        }

        if($statement->get_result()->affected_rows === 0) {
            logger()->error("Contact.save: ContactID (%d) potentially not found??", [$this->id]);
            return false;
        }

        logger()->info("Contact.save: Contact %id successfully updated", [$this->id]);
        return true;
    }

    /** Delete the contact record */
    public function destroy(): bool {
        logger()->debug(sprintf("Contact.destroy: Query for contactID %d", $this->id));
        $statement = DBGlobal::getRawDB()->prepare("DELETE FROM contacts WHERE id = ?");
        $statement->bind_param("i", $this->id);

        if(!$statement->execute()) {
            logger()->error(sprintf("Contact.destroy: Deleting contactID (%d) cause SQL Error: %d", $this->id, $statement->errno));
            return false;
        }

        if($statement->affected_rows === 0) {
            logger()->warn(sprintf("Contact.destroy: contactID (%d) not found", $this->id));
            return false;
        }

        $this->id = 0;
        logger()->info(sprintf("Contact.destroy: contactID (%d) successfully deleted", $this->id));
        return true;
    }
}