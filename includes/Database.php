<?php

namespace App;

class Database
{
    private $host = 'localhost';
    private $db_name = 'blog_db';
    private $user = 'root';
    private $password = '';
    private $conn;

    public function connect()
    {
        $this->conn = new \mysqli($this->host, $this->user, $this->password, $this->db_name);

        if ($this->conn->connect_error) {
            die('Connection Error: ' . $this->conn->connect_error);
        }

        $this->conn->set_charset("utf8mb4");
        return $this->conn;
    }

    public function getConnection()
    {
        return $this->conn;
    }
}

