<?php

class Database
{
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $conn;

    public function connect()
    {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->db_name);

        if ($this->conn->connect_error) {
            error_log('[SIAP-MAJU] DB connection failed: ' . $this->conn->connect_error);
            if (defined('APP_ENV') && APP_ENV === 'production') {
                die('Layanan sedang gangguan. Silakan coba beberapa saat lagi.');
            }
            die('Connection Error: ' . $this->conn->connect_error);
        }

        $this->conn->set_charset(DB_CHARSET);
        return $this->conn;
    }

    public function getConnection()
    {
        if (!$this->conn) {
            $this->connect();
        }
        return $this->conn;
    }
}
