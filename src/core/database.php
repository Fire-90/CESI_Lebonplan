<?php

namespace Core;

use PDO;
use PDOException;

class Database {
    private static $pdo = null;

    public static function getConnection() {
        if (self::$pdo === null) {
            try {
                $host = 'localhost';
                $dbname = 'lbp'; // Vérifie bien que c'est le bon nom de la base
                $username = 'root';
                $password = '';

                self::$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Erreur de connexion : " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
