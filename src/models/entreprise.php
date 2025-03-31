<?php

namespace Models;

use PDO;
use Core\Database;

class Entreprise {
    private $pdo;

    public function __construct() {
        // Récupérer la connexion PDO depuis la classe Database
        $this->pdo = Database::getConnection();
    }

    // Récupérer toutes les entreprises
    public function getEntreprises() {
        $stmt = $this->pdo->query("SELECT * FROM Company");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ajouter une entreprise
    public function addEntreprise($nom, $secteur, $ville) {
        $stmt = $this->pdo->prepare("INSERT INTO Company (NameCompany, Sector, City) VALUES (:nom, :secteur, :ville)");
        return $stmt->execute([
            ':nom' => $nom,
            ':secteur' => $secteur,
            ':ville' => $ville
        ]);
    }

    // Supprimer une entreprise
    public function deleteEntreprise($id) {
        $stmt = $this->pdo->prepare("DELETE FROM Company WHERE idCompany = ?");
        return $stmt->execute([$id]);
    }

    // Mettre à jour une entreprise
    public function updateEntreprise($id, $nom, $secteur, $ville) {
        $stmt = $this->pdo->prepare("UPDATE Company SET NameCompany = ?, Sector = ?, City = ? WHERE idCompany = ?");
        return $stmt->execute([$nom, $secteur, $ville, $id]);
    }

    // Récupérer une entreprise par son ID
    public function getEntrepriseById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM Company WHERE idCompany = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
