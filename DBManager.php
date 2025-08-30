<?php
require_once "./loader.php";
use function Sentry\logger;

class DBGlobal {
    private static $mysql;
    public static function getRawDB() {
        if(!DBGlobal::$mysql)
            return DBGlobal::$mysql = mysqli_connect("localhost", getenv("DB_USERNAME"), getenv("DB_PASSWORD"), getenv("DB_USERNAME"));
        return DBGlobal::$mysql;
    }

    /** Return array of tags uniquely from the DB */
    public static function getAllTags(): ?array
    {
        logger()->debug("DBGlobal.getAllTags: Query tag table");
        $statement = DBGlobal::getRawDB()->prepare("SELECT DISTINCT `value` FROM tags");

        if($statement->execute()) {
            $res = $statement->get_result();
            $data = array();
            while($tag = $res->fetch_assoc())
                $data[] = $tag['value'];
            logger()->info("DBGlobal.getAllTags: Operation Success!");
            return $data;
        }

        logger()->error(sprintf("DBGlobal.getAllTags: Query Failed, SQL Err: %d", $statement->errno));
        return null;
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
        $statement = sprintf("SELECT * FROM users WHERE id = '%d'", DBGlobal::getRawDB()->escape_string($id));
        $result = DBGlobal::getRawDB()->query($statement)->fetch_assoc();

        if(!$result) {
            logger()->error(sprintf("User.getByID: Userid %d not found", $id));
            return null;
        }

        logger()->debug(sprintf("User.getByID: Found Userid %d", $id));
        return new User($result["id"], $result["username"]);
    }
    public static function login(string $username, string $password): ?User
    {
        logger()->debug(sprintf("User.login: Query for user %s", $username));
        $statement = sprintf("SELECT * FROM users WHERE username = '%s'", DBGlobal::getRawDB()->escape_string($username));
        $result = DBGlobal::getRawDB()->query($statement)->fetch_assoc();
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
        $statement = DBGlobal::getRawDB()->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $statement->bind_param("ss", $username, $password);

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
    // API/BACKEND DEV NOTICE: Add your own search functionality and construct/return Contact class as a way to store the info.
}

class Contact {
    private int $id;
    private string $firstName;
    private string $lastName;
    private string $email;
    private array $tags;
    private bool $isFavorite;

    /** !! Do not call it outside of User.searchContact !! */
    public function __construct($id, $firstName, $lastName, $email, $tags, $isFavorite)
    {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->tags = $tags;
        $this->isFavorite = $isFavorite;
    }
    public function __get($name) {
        switch($name) {
            case "id": return $this->id;
            case "firstName": return $this->firstName;
            case "lastName": return $this->lastName;
            case "email": return $this->email;
            case "tags": return $this->tags;
            case "isFavorite": return $this->isFavorite;
            default: throw new Exception("Contact Class: Uhhh can't get this variable...");
        }
    }

    public function addTag(string $tag): bool
    {
        // For real-world project, use multi-insertion technique instead
        logger()->debug(sprintf("Contact.addTag: Query for contactID %d", $this->id));
        $statement = DBGlobal::getRawDB()->prepare("INSERT INTO tags (contactid, value) VALUES (?, ?)");
        $statement->bind_param("is", $this->id, $tag);

        if($statement->execute()) {
            logger()->info(sprintf("Contact.addTag: tag %s successfully added to contactID %d", $tag, $this->id));
            $this->tags[] = $tag;
            return true;
        }

        if($statement->errno === 1062)
            logger()->info(sprintf("Contact.addTag: Duplicate tag (%s) for contactID %d", $tag, $this->id));
        else
            logger()->error(sprintf("Contact.addTag: Tag %s caused unhandled sql error (for contactID %d): %d", $tag, $this->id, $statement->errno));
        return false;
    }
    public function removeTag(string $tag): bool
    {
        logger()->debug(sprintf("Contact.removeTag: Query for contactID %d", $this->id));
        $statement = DBGlobal::getRawDB()->prepare("DELETE FROM tags WHERE contactid = '?' AND value = '?'");
        $statement->bind_param("is", $this->id, $tag);

        if($statement->execute()) {
            // No need to check if the number is greater than 1 because of configured database constraint
            if($statement->affected_rows === 0) {
                logger()->warn(sprintf("Contact.removeTag: tag %s doesn't exist for contactID %d", $tag, $this->id));
                return false;
            }

            $arrKey = array_search($tag, $this->tags);
            if($arrKey)
                unset($this->tags[$arrKey]);
            if(!$arrKey)
                logger()->warn(sprintf("Contact.removeTag: tag %s failed to remove from contactID's (%d) current class field", $tag, $this->id));

            logger()->info(sprintf("Contact.removeTag: tag %s successfully deleted from contactID %d", $tag, $this->id));
            return true;
        }

        logger()->error(sprintf("Contact.removeTag: Tag %s caused unhandled sql error (for contactID %d): %d", $tag, $this->id, $statement->errno));
        return false;
    }

    /** Reverse the current favorite status */
    public function setFavorite(): bool {
        logger()->debug(sprintf("Contact.setFavorite: Query for contactID %d", $this->id));
        $revFavState = $this->isFavorite ? 0 : 1;
        $statement = DBGlobal::getRawDB()->prepare("UPDATE contacts SET favorite = ? WHERE id = ?");
        $statement->bind_param("ii", $revFavState, $this->id);

        if($statement->execute()) {
            if($statement->affected_rows === 0) {
                logger()->warn(sprintf("Contact.setFavorite: contactID (%d) not found", $this->id));
                return false;
            }

            $this->isFavorite = $revFavState;
            logger()->info(sprintf("Contact.setFavorite: contactID (%d) favorite status changed to %d", $this->id, $revFavState));
            return true;
        }

        logger()->error(sprintf("Contact.setFavorite: Changing favorite status for contactID (%d) cause SQL Error: %d", $this->id, $statement->errno));
        return false;
    }
    /** Delete the contact record */
    public function destroy(): bool {
        logger()->debug(sprintf("Contact.destroy: Query for contactID %d", $this->id));
        $statement = DBGlobal::getRawDB()->prepare("DELETE FROM contacts WHERE id = ?");
        $statement->bind_param("i", $this->id);

        if($statement->execute()) {
            if($statement->affected_rows === 0) {
                logger()->warn(sprintf("Contact.destroy: contactID (%d) not found", $this->id));
                return false;
            }

            $this->id = 0;
            logger()->info(sprintf("Contact.destroy: contactID (%d) successfully deleted", $this->id));
            return true;
        }

        logger()->error(sprintf("Contact.destroy: Deleting contactID (%d) cause SQL Error: %d", $this->id, $statement->errno));
        return false;
    }

    // I'm assuming contact information isn't designed to be changed afterward?
}