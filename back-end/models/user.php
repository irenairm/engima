<?php namespace Models;

class User
{
    private $connection;
    private $table = 'engima.user';
    // Attributes for MYSQL table:
    public $id_user;
    public $username;
    public $email;
    public $no_hp;
    public $picture_profile;
    public $picture_profile_path;
    public $token;
    public $token_expdate;
    public $password;
    public function __construct($database)
    {
        $this->connection = $database;
    }
    // Register User
    public function register($database)
    {
        $query = "INSERT INTO " . $this->table
                 . " VALUES " . "('" . $this->username. "', '" . $this->email . "', '" . $this->no_hp . "', '" . $this->picture_profile_path . "', '" . $this->password . "', '" . $this->token . "', '" . $this->token_expdate . "')";
        if (mysqli_query($database, $query)) {
            return '200';
        } else {
            return 'Error ' . mysqli_error($database);
        }
    }

    public function login($database)
    {
        $query = "SELECT password  FROM " . $this->table . " WHERE email = '" . $this->email . "';";
        // echo $query;
        $execute = mysqli_query($database, $query);
        $result = mysqli_fetch_array($execute);
        if ($result) {
            $hashed_password = $result[0];
            $result = password_verify($this->password, $hashed_password);
            if ($result) {
                return '200';
            } else {
                return 'Wrong Password.' ;
            }
        }
        if (!$result) {
            return 'Please register yourself!' ;
        }
    }

    public function fetchUsername($database, $access_token)
    {
        $query = "SELECT username  FROM " . $this->table . " WHERE token = '" . $access_token . "';";
        $execute = mysqli_query($database, $query);
        $username_arr = mysqli_fetch_array($execute);
        if ($username_arr) {
            return $username_arr[0];
        } else {
            return '500';
        }
    }

    public function fetchID($database, $access_token){
        $query = "SELECT id_user FROM " . $this->table . " WHERE token = '" . $access_token . "';";
        $execute = mysqli_query($database, $query);
        $id_arr = mysqli_fetch_array($execute);
        if ($id_arr) {
            return $id_arr[0];
        } else {
            return '500';
        }
    }
    public function updateExpiryTime($database)
    {
        $query = "UPDATE " . $this->table . " SET token_expdate = '" . $this->token_expdate . "' WHERE email = '" . $this->email . "';";
        $result = mysqli_query($database, $query);
        if ($result) {
            return '200';
        } else {
            return '500';
        }
    }

    public function getAccessToken($database)
    {
        $query = "SELECT token  FROM " . $this->table . " WHERE email = '" . $this->email . "';";
        $execute = mysqli_query($database, $query);
        $result = mysqli_fetch_array($execute);
        if ($result) {
            return $result;
        } else {
            return '500';
        }
    }

    public function validateAccessToken($database, $access_token)
    {
        $query = "SELECT token_expdate  FROM " . $this->table . " WHERE token = '" . $access_token . "';";
        $execute = mysqli_query($database, $query);
        $token_expdate_arr = mysqli_fetch_array($execute);
        if ($token_expdate_arr) {
            return $token_expdate_arr;
        } else {
            return '500';
        }
    }

    // Register validation for duplicates.
    public function validateDuplicate($database, $value, $attribute)
    {
        $query = "SELECT " . $attribute . " FROM " . $this->table . " WHERE  "
                 . $attribute . " =  " . "'" . $value . "'";
        $execute = mysqli_query($database, $query);
        $result = mysqli_fetch_array($execute);
        echo $result;
        if ($result) {
            return false;
        } else {
            return true;
        }
    }
}
