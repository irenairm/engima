<?php

namespace Config;

class Database
{
    // Engima Database Parameters
    private $host = "127.0.0.1";
    // private $port = '3306';
    private $username = 'root';
    private $password = '1234';
    private $db_name = 'engima';
    public $connection;

    // Connect Database
    public function connect()
    {
        $this->connection = mysqli_connect($this->host, $this->username, $this->password, $this->db_name);
        if (!$this->connection) {
            echo "Connection failed: " . mysqli_connect_error();
        }
    }
}
