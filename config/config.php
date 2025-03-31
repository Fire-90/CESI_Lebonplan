<?php
// config.php : Ce fichier contient les informations de connexion à la base de données

define('DB_HOST', 'localhost'); // Adresse du serveur MySQL (par défaut "localhost" pour un serveur local)
define('DB_USER', 'root'); // Utilisateur MySQL (par défaut "root" pour un serveur local)
define('DB_PASSWORD', ''); // Mot de passe MySQL (par défaut vide pour un serveur local)
define('DB_NAME', 'lebonplan'); // Nom de la base de données que tu utilises

// Créer la connexion
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}
?>
