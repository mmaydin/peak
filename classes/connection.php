<?php

class Connection
{
    private static $connection;

    public static function create($host, $dbname, $user, $pass) {
        if (self::$connection == null) {
            try {
                self::$connection = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
            } catch (PDOException $e) {
            }
        }

        return self::$connection;
    }

    public static function get() {
        return self::$connection;
    }
}
