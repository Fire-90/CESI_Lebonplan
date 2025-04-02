<?php

namespace Core;

use PDO;
use PDOException;

class Database {
    private static $pdo = null;

    public static function getConnection() {
        if (self::$pdo === null) {
            try {
                $host = 'lebonplan.westeurope.cloudapp.azure.com'; // Utilisez le nom d'hôte de votre serveur
                $dbname = 'lebonplan_data'; // Vérifiez que c'est le bon nom de la base
                $username = 'user';
                $password = 'Furry_is_better666';

                // Utilisation de la chaîne de connexion PDO pour se connecter à la base de données
                self::$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                // Gérer l'erreur de manière sécurisée sans exposer d'informations sensibles
                error_log('Erreur de connexion à la base de données: ' . $e->getMessage());  // Enregistrer l'erreur dans les logs
                die("Une erreur est survenue. Veuillez réessayer plus tard.");  // Message générique pour l'utilisateur
            }
        }
        return self::$pdo;
    }
}
?>