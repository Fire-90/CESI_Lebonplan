<?php

namespace Controllers;

use Core\Database;
use PDO;
use PDOException;

class EntrepriseController extends BaseController {
    private $pdo;

    public function __construct() {
        parent::__construct(); // Appel du constructeur parent
        $this->pdo = Database::getConnection();
    }

    /**
     * Affichage de la liste des entreprises avec pagination
     */
    public function index($page = 1) {
        try {
            $perPage = 10;
            $page = max(1, (int)$page);
            $start = ($page - 1) * $perPage;
    
            // Nombre total d'entreprises
            $stmtTotal = $this->pdo->query("SELECT COUNT(*) as total FROM Company");
            $totalEntreprises = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];
            $totalPages = ceil($totalEntreprises / $perPage);
    
            // Récupération des entreprises paginées
            $stmt = $this->pdo->prepare("SELECT * FROM Company LIMIT :start, :perPage");
            $stmt->bindValue(':start', $start, PDO::PARAM_INT);
            $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
            $stmt->execute();
            $entreprises = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            // Affichage avec la méthode render() parente
            $this->render('entreprise.twig', [
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
            try {
                $nom = filter_input(INPUT_POST, 'NameCompany', FILTER_SANITIZE_STRING);
                $secteur = filter_input(INPUT_POST, 'Sector', FILTER_SANITIZE_STRING);
                $ville = filter_input(INPUT_POST, 'City', FILTER_SANITIZE_STRING);
    
                if (!empty($nom) && !empty($secteur) && !empty($ville)) {
                    $stmt = $this->pdo->prepare("INSERT INTO Company (NameCompany, Sector, City) VALUES (:nom, :secteur, :ville)");
                    $stmt->execute([
                        ':nom' => $nom,
                        ':secteur' => $secteur,
                        ':ville' => $ville
                    ]);
    
                    header('Location: ?page=entreprises');
                    exit;
                } else {
                    throw new \Exception("Tous les champs doivent être remplis.");
                }
            } catch (PDOException $e) {
                $this->render('ajout-entreprise.twig', [
                    'error' => "Erreur lors de l'ajout de l'entreprise : " . $e->getMessage()
                ]);
                return;
            } catch (\Exception $e) {
                $this->render('ajout-entreprise.twig', [
                    'error' => $e->getMessage()
                ]);
                return;
            }
        }
    
        $this->render('ajout-entreprise.twig');
    }
    
    /**
     * Modification d'une entreprise
     */
    public function edit($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
                $secteur = filter_input(INPUT_POST, 'secteur', FILTER_SANITIZE_STRING);
                $ville = filter_input(INPUT_POST, 'ville', FILTER_SANITIZE_STRING);
    
                if (!empty($nom) && !empty($secteur) && !empty($ville)) {
                    $stmt = $this->pdo->prepare("UPDATE Company SET NameCompany = :nom, Sector = :secteur, City = :ville WHERE idCompany = :id");
                    $stmt->execute([
                        ':nom' => $nom,
                        ':secteur' => $secteur,
                        ':ville' => $ville,
                        ':id' => $id
                    ]);
    
                    header('Location: index.php?page=entreprises');
                    exit;
                } else {
                    throw new \Exception("Tous les champs doivent être remplis.");
                }
            } catch (PDOException $e) {
                $this->render('edit-entreprise.twig', [
                    'error' => "Erreur lors de la modification : " . $e->getMessage(),
                    'entreprise' => ['idCompany' => $id]
                ]);
                return;
            }
        }

        try {
            $stmt = $this->pdo->prepare("SELECT * FROM Company WHERE idCompany = :id");
            $stmt->execute([':id' => $id]);
            $entreprise = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$entreprise) {
                header('Location: index.php?page=entreprises');
                exit;
            }
    
            $this->render('edit-entreprise.twig', ['entreprise' => $entreprise]);
        } catch (PDOException $e) {
            $this->render('edit-entreprise.twig', [
                'error' => "Erreur lors de la récupération de l'entreprise : " . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Suppression d'une entreprise
     */
    public function delete($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM Company WHERE idCompany = :id");
            $stmt->execute([':id' => $id]);
            header('Location: /index.php?page=entreprises');
            exit;
        } catch (PDOException $e) {
            $this->render('entreprise.twig', [
                'error' => "Erreur lors de la suppression : " . $e->getMessage()
            ]);
        }
    }
}