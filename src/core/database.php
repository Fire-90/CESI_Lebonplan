<?php
$host = 'localhost';
$dbname = 'entreprise';
$username = 'louka';
$password = '123456789';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connexion réussie!";
} catch (PDOException $e) {
    die("Connexion échouée : " . $e->getMessage());
}
