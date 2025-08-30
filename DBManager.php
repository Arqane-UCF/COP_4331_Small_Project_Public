<?php

require_once "./loader.php";
// Internal Note: Function for password hashing
//password_hash();
//password_verify()

class DBGlobal {
    private static $mysql;
    /** Return array of tags uniquely from the DB */

    public static function getRawDB() {
        if(!DBGlobal::$mysql)
            return DBGlobal::$mysql = mysqli_connect("localhost", getenv("DB_USERNAME"), getenv("DB_PASSWORD"), getenv("DB_USERNAME"));;
        return DBGlobal::$mysql;
    }
    public static function getAllTags() {

    }
}

class User {
    private $id;
    private $username;

    private function __construct($id, $username) {
        $this->id = $id;
        $this->username = $username;
    }
    /** !!!ONLY USE THIS TO PULL USER DATA FROM SESSION!!! */
    public static function getByID($id) {
        $statement = sprintf("SELECT * FROM users WHERE id = '%s'", DBGlobal::getRawDB()->escape_string($id));
        $result = DBGlobal::getRawDB()->query($statement)->fetch_assoc();
        if(!$result)
            return null;
        return new User($result["id"], $result["username"]);
    }
    public static function login($username, $password) {
        $statement = sprintf("SELECT * FROM users WHERE username = '%s'", DBGlobal::getRawDB()->escape_string($username));
        $result = DBGlobal::getRawDB()->query($statement)->fetch_assoc();
        if(!$result)
            return null;
        if(!password_verify($password, $result["password"]))
            return null;
        return new User($result["id"], $result["username"]);
    }

    public static function register($username, $password) {
        $statement = DBGlobal::getRawDB()->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $statement->bind_param("ss", $username, $password);
        if($statement->execute())
            return new User($statement->insert_id, $username);
        return null;
    }

    public function __get($name) {
        if($name === "id")
            return $this->id;
        if($name === "username")
            return $this->username;
        if($name === "password")
            throw new Exception("Why would you do this LMAO!! You cant try and get user's password.");
        throw new Exception("User Class: Uhhh, unavailable..");
    }

    /** BACKEND DEVS, ADD ANY USER ENDPOINT YOU NEED HERE */
}

class Post {

}