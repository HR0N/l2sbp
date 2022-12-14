<?php

namespace mydb;

class myDB{
    private $mysql_host;
    private $mysql_user;
    private $mysql_password;
    private $mysql_database;
    public function __construct($env)
    {
        $this->mysql_host = $env::$DB_HOST;
        $this->mysql_user  = $env::$DB_USERNAME;
        $this->mysql_password = $env::$DB_PASSWORD;
        $this->mysql_database = $env::$DB_DATABASE;
    }
    public function connect(){
        $connect=mysqli_connect($this->mysql_host, $this->mysql_user, $this->mysql_password, $this->mysql_database);
        mysqli_set_charset($connect, "utf8mb4");
        if ($connect->connect_error) {
            die("Connection failed: " . $connect->connect_error);
        }
        return $connect;
    }
    public function get_all($sql){
        $result = $this->connect()->query($sql);
        return mysqli_fetch_all($result);
    }
    public function set_rss_x5($sJson){
        $sJson = str_replace('"', "`", $sJson);

        echo '<pre>';
        echo var_dump($sJson);
        echo '</pre>';

        $sql = "UPDATE `asterios_rss_x5` SET `data`=\"".$sJson."\" WHERE 1";
        $result = $this->connect()->query($sql);
        return ($result);
    }
}

