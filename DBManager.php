<?php

require_once "./loader.php";
// Internal Note: Function for password hashing
//password_hash();
//password_verify()


class DBMGR {
    private static $mysql = mysqli_connect("localhost", getenv("DB_USERNAME"), getenv("DB_PASSWORD"), getenv("DB_USERNAME"));;
    private $username;
    public function __construct($username) {
        $this->username = $username;
    }
    public function login(, $password) {

    }
    public function register(, $password) {

    }
    public function getUserPosts() {

    }
    /** Return array of tags uniquely from the DB */
    public static function getAllTags() {

    }
}