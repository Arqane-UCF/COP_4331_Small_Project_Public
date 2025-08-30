<?php

require_once "./loader.php";
// Internal Note: Function for password hashing
//password_hash();
//password_verify()

class DBGlobal {
    private static $mysql;
    public static function getRawDB() {
        if(!DBGlobal::$mysql)
            return DBGlobal::$mysql = mysqli_connect("localhost", getenv("DB_USERNAME"), getenv("DB_PASSWORD"), getenv("DB_USERNAME"));;
        return DBGlobal::$mysql;
    }

    /** Return array of tags uniquely from the DB */
    public static function getAllTags() {
        \Sentry\logger()->debug("DBGlobal.getAllTags: Query tag table");
        $statement = DBGlobal::getRawDB()->prepare("SELECT DISTINCT value FROM tags");

        if($statement->execute()) {
            $res = $statement->get_result();
            $data = array();
            while($tag = $res->fetch_assoc())
                $data[] = $tag['value'];
            \Sentry\logger()->info("DBGlobal.getAllTags: Operation Success!");
            return $data;
        }

        \Sentry\logger()->error(sprintf("DBGlobal.getAllTags: Query Failed, SQL Err: %d", $statement->errno));
        return null;
    }
}

class User {
    private integer $id;
    private string $username;

    private function __construct($id, $username) {
        $this->id = $id;
        $this->username = $username;
    }
    /** !!!ONLY USE THIS TO PULL USER DATA FROM SESSION!!! */
    public static function getByID(integer $id) {
        \Sentry\logger()->debug(sprintf("User.getByID: Query for userid %d", $id));
        $statement = sprintf("SELECT * FROM users WHERE id = '%d'", DBGlobal::getRawDB()->escape_string($id));
        $result = DBGlobal::getRawDB()->query($statement)->fetch_assoc();

        if(!$result) {
            \Sentry\logger()->error(sprintf("User.getByID: Userid %d not found", $id));
            return null;
        }

        \Sentry\logger()->debug(sprintf("User.getByID: Found Userid %d", $id));
        return new User($result["id"], $result["username"]);
    }
    public static function login(string $username, string $password) {
        \Sentry\logger()->debug(sprintf("User.login: Query for user %s", $username));
        $statement = sprintf("SELECT * FROM users WHERE username = '%s'", DBGlobal::getRawDB()->escape_string($username));
        $result = DBGlobal::getRawDB()->query($statement)->fetch_assoc();
        if(!$result) {
            \Sentry\logger()->info(sprintf("User.login: User %s not found", $username));
            return null;
        }
        if(!password_verify($password, $result["password"])) {
            \Sentry\logger()->info(sprintf("User.login: User %s input incorrect password", $username));
            return null;
        }
        \Sentry\logger()->info(sprintf("User.login: User %s successfully authenticated", $username));
        return new User($result["id"], $result["username"]);
    }

    public static function register(string $username, string $password) {
        \Sentry\logger()->debug(sprintf("User.register: Query for user %s", $username));
        $statement = DBGlobal::getRawDB()->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $statement->bind_param("ss", $username, $password);

        if($statement->execute()) {
            \Sentry\logger()->info(sprintf("User.register: User %s successfully registered", $username));
            return new User($statement->insert_id, $username);
        }

        if($statement->errno === 1062)
            \Sentry\logger()->info(sprintf("User.register: User %s already existed", $username));
        else
            \Sentry\logger()->error(sprintf("User.register: User %s caused unhandled sql error: %d", $username, $statement->errno));
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

    public function searchContact(?string $firstName = null, ?string $lastName = null) {
        // Left as an exercise for reader
        // hint: It should construct a Contact class and use that to handle Contact information
    }
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

    public function addTag(string $tag) {
        // For real-world project, use multi-insertion technique instead
        \Sentry\logger()->debug(sprintf("Contact.addTag: Query for contactID %d", $this->id));
        $statement = DBGlobal::getRawDB()->prepare("INSERT INTO tags (contactid, value) VALUES (?, ?)");
        $statement->bind_param("is", $this->id, $tag);

        if($statement->execute()) {
            \Sentry\logger()->info(sprintf("Contact.addTag: tag %s successfully added to contactID %d", $tag, $this->id));
            $this->tags[] = $tag;
            return true;
        }

        if($statement->errno === 1062)
            \Sentry\logger()->info(sprintf("Contact.addTag: Duplicate tag (%s) for contactID %d", $tag, $this->id));
        else
            \Sentry\logger()->error(sprintf("Contact.addTag: Tag %s caused unhandled sql error (for contactID %d): %d", $tag, $this->id, $statement->errno));
        return false;
    }
    public function removeTag(string $tag) {
        //@TODO: Yes
    }
    public function setFavorite(bool $isFavorite) {
        //@TODO: Yes
    }
    public function destroyContact() {
        //@TODO: Yes
    }

    // I'm assuming contact information isn't designed to be changed afterward?
}