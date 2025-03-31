<?php

namespace Controllers;

use Core\Database;
use Core\TemplateEngine;
use PDO;
use PDOException;

class EntrepriseController {
    private $twig; 
    private $pdo;

    public function __construct() {
        // Initialisation de Twig
        $this->twig = TemplateEngine::getTwig();
        // Connexion à la base de données
        $this->pdo = Database::getConnection();
    }

    /**
     * Affichage de la liste des entreprises avec pagination
     */
    public function index($page = 1) {
        try {
            $perPage = 10;
    
            // Vérification de la validité de la page
            $page = max(1, (int)$page);
    
            // Calcul du point de départ SQL
            $start = ($page - 1) * $perPage;
    
            // Nombre total d'entreprises
            $stmtTotal = $this->pdo->query("SELECT COUNT(*) as total FROM entreprises");
            $totalEntreprises = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];
            $totalPages = ceil($totalEntreprises / $perPage);
    
            // Récupération des entreprises paginées
            $stmt = $this->pdo->prepare("SELECT * FROM entreprises LIMIT :start, :perPage");
            $stmt->bindValue(':start', $start, PDO::PARAM_INT);
            $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
            $stmt->execute();
            $entreprises = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            // Affichage avec Twig
            echo $this->twig->render('entreprise.twig', [
                'entreprises' => $entreprises,
                'currentPage' => $page,
                'totalPages' => $totalPages
            ]);
    
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }
    
    

    /**
     * Ajout d'une entreprise
     */
    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
            $secteur = filter_input(INPUT_POST, 'secteur', FILTER_SANITIZE_STRING);
            $ville = filter_input(INPUT_POST, 'ville', FILTER_SANITIZE_STRING);

            if (!empty($nom) && !empty($secteur) && !empty($ville)) {
                try {
                    $stmt = $this->pdo->prepare("INSERT INTO entreprises (nom, secteur, ville) VALUES (:nom, :secteur, :ville)");
                    $stmt->execute([
                        ':nom' => $nom,
                        ':secteur' => $secteur,
                        ':ville' => $ville
                    ]);
                    header('Location: /entreprises');
                    exit;
                } catch (PDOException $e) {
                    echo "Erreur lors de l'ajout : " . $e->getMessage();
                }
            } else {
                echo "Tous les champs sont requis.";
            }
        }

        echo $this->twig->render('ajout-entreprise.twig');
    }

    /**
     * Modification d'une entreprise
     */
    public function edit($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
            $secteur = filter_input(INPUT_POST, 'secteur', FILTER_SANITIZE_STRING);
            $ville = filter_input(INPUT_POST, 'ville', FILTER_SANITIZE_STRING);

            if (!empty($nom) && !empty($secteur) && !empty($ville)) {
                try {
                    $stmt = $this->pdo->prepare("UPDATE entreprises SET nom = :nom, secteur = :secteur, ville = :ville WHERE id = :id");
                    $stmt->execute([
                        ':nom' => $nom,
                        ':secteur' => $secteur,
                        ':ville' => $ville,
                        ':id' => $id
                    ]);
                    header('Location: /entreprises');
                    exit;
                } catch (PDOException $e) {
                    echo "Erreur lors de la modification : " . $e->getMessage();
                }
            } else {
                echo "Tous les champs doivent être remplis.";
            }
        } else {
            // Récupération des infos de l'entreprise à modifier
            $stmt = $this->pdo->prepare("SELECT * FROM entreprises WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $entreprise = $stmt->fetch(PDO::FETCH_ASSOC);

            echo $this->twig->render('edit-entreprise.twig', ['entreprise' => $entreprise]);
        }
    }

    /**
     * Suppression d'une entreprise
     */
    public function delete($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM entreprises WHERE id = :id");
            $stmt->execute([':id' => $id]);
            header('Location: /entreprises');
            exit;
        } catch (PDOException $e) {
            echo "Erreur lors de la suppression : " . $e->getMessage();
        }
    }
}
