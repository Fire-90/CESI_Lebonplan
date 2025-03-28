<?php
$servername = "localhost";
$username = "root";  // ton utilisateur
$password = "";      // ton mot de passe
$dbname = "lebonplan"; // le nom de ta base de données

// Créer une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
} else {
    echo "Connexion réussie à la base de données !";
}

// Fermer la connexion
$conn->close();
?>
