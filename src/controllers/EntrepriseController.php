<?php

namespace Controllers;

use Core\database;
use PDO;
use PDOException;

class EntrepriseController extends BaseController {
    private $pdo;

    public function __construct() {
        parent::__construct();
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
                'totalPages' => $totalPages,
                'successMessage' => $_SESSION['success'] ?? null,
                'errorMessage' => $_SESSION['error'] ?? null
            ]);
            
            // Nettoyer les messages après affichage
            unset($_SESSION['success'], $_SESSION['error']);
    
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur : " . $e->getMessage();
            $this->render('entreprise.twig', [
                'errorMessage' => $_SESSION['error'],
                'entreprises' => [],
                'currentPage' => 1,
                'totalPages' => 1
            ]);
        }
    }
    
    /**
     * Ajout d'une entreprise
     */
    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $nom = filter_input(INPUT_POST, 'NameCompany', FILTER_SANITIZE_STRING);
                $email = filter_input(INPUT_POST, 'EmailCompany', FILTER_SANITIZE_STRING);
                $secteur = filter_input(INPUT_POST, 'Sector', FILTER_SANITIZE_STRING);
                $ville = filter_input(INPUT_POST, 'City', FILTER_SANITIZE_STRING);
    
                if (!empty($nom) && !empty($email) && !empty($secteur) && !empty($ville)) {
                    $stmt = $this->pdo->prepare("INSERT INTO Company (NameCompany, EmailCompany, Sector, City) VALUES (:nom, :email, :secteur, :ville)");
                    $stmt->execute([
                        ':nom' => $nom,
                        ':email'=> $email,
                        ':secteur' => $secteur,
                        ':ville' => $ville
                    ]);
    
                    $_SESSION['success'] = "Entreprise ajoutée avec succès !";
                    header('Location: ?page=entreprises');
                    exit;
                } else {
                    throw new \Exception("Tous les champs doivent être remplis.");
                }
            } catch (PDOException $e) {
                $this->render('ajout-entreprise.twig', [
                    'errorMessage' => "Erreur lors de l'ajout de l'entreprise : " . $e->getMessage()
                ]);
                return;
            } catch (\Exception $e) {
                $this->render('ajout-entreprise.twig', [
                    'errorMessage' => $e->getMessage()
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
    
                    $_SESSION['success'] = "Entreprise modifiée avec succès !";
                    header('Location: index.php?page=entreprises');
                    exit;
                } else {
                    throw new \Exception("Tous les champs doivent être remplis.");
                }
            } catch (PDOException $e) {
                $this->render('edit-entreprise.twig', [
                    'errorMessage' => "Erreur lors de la modification : " . $e->getMessage(),
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
                $_SESSION['error'] = "Entreprise non trouvée";
                header('Location: index.php?page=entreprises');
                exit;
            }
    
            $this->render('edit-entreprise.twig', [
                'entreprise' => $entreprise,
                'errorMessage' => $_SESSION['error'] ?? null
            ]);
            unset($_SESSION['error']);
            
        } catch (PDOException $e) {
            $this->render('edit-entreprise.twig', [
                'errorMessage' => "Erreur lors de la récupération de l'entreprise : " . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Suppression d'une entreprise
     */
    public function delete() {
        try {
            $id = $_POST['id'] ?? $_GET['id'] ?? null;
            
            if (!$id || !is_numeric($id)) {
                throw new \Exception("ID d'entreprise invalide");
            }
            $id = (int)$id;
    
            // Vérification si l'entreprise existe
            $stmt = $this->pdo->prepare("SELECT * FROM Company WHERE idCompany = ?");
            $stmt->execute([$id]);
            $entreprise = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$entreprise) {
                throw new \Exception("Entreprise introuvable");
            }
    
            // Si confirmation reçue
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Suppression de l'entreprise
                $delete = $this->pdo->prepare("DELETE FROM Company WHERE idCompany = ?");
                $delete->execute([$id]);
                
                if ($delete->rowCount() > 0) {
                    $_SESSION['success'] = "L'entreprise a été supprimée avec succès !";
                    header('Location: index.php?page=entreprises');
                    exit;
                }
                throw new \Exception("Erreur lors de la suppression");
            }
    
            // Affichage de la confirmation de suppression
            $this->render('delete-entreprise.twig', [
                'entreprise' => $entreprise,
                'errorMessage' => $_SESSION['error'] ?? null
            ]);
            unset($_SESSION['error']);
            
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: index.php?page=entreprises');
            exit;
        }
    }
}