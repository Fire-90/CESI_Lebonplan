<?php
/*
namespace Models;

use PDO;
use Core\Database;

class Entreprise {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function getEntreprises() {
        $stmt = $this->pdo->query("SELECT * FROM entreprises");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addEntreprise($nom, $secteur, $ville) {
        $stmt = $this->pdo->prepare("INSERT INTO entreprises (nom, secteur, ville) VALUES (?, ?, ?)");
        return $stmt->execute([$nom, $secteur, $ville]);
    }

    public function deleteEntreprise($id) {
        $stmt = $this->pdo->prepare("DELETE FROM entreprises WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function updateEntreprise($id, $nom, $secteur, $ville) {
        $stmt = $this->pdo->prepare("UPDATE entreprises SET nom = ?, secteur = ?, ville = ? WHERE id = ?");
        return $stmt->execute([$nom, $secteur, $ville, $id]);
    }
}
*/
?>
