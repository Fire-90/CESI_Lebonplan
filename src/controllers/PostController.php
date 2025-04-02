<?php

namespace Controllers;

use Core\database;
use PDO;
use PDOException;

class PostController extends BaseController {
    private $pdo;

    public function __construct() {
        parent::__construct(); // Appel du constructeur parent
        $this->pdo = Database::getConnection();
    }

    /**
     * Affichage de la liste des offres avec pagination
     */
    public function index($page = 1) {
        try {
            $perPage = 10;
            $page = max(1, (int)$page);
            $start = ($page - 1) * $perPage;
    
            // Nombre total d'offres
            $stmtTotal = $this->pdo->query("SELECT COUNT(*) as total FROM Offer" );
            $totalOffre = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];
            $totalPages = ceil($totalOffre / $perPage);
    
            // Récupération des offres paginées
            $stmt = $this->pdo->prepare("
                SELECT Offer.idOffer, Offer.NameOffer, Offer.DescOffer, Offer.RemunOffer, Offer.DateOffer, 
                Company.NameCompany 
                FROM Offer
                JOIN Company ON Offer.idCompany = Company.idCompany
                LIMIT :start, :perPage
            ");
            $stmt->bindValue(':start', $start, PDO::PARAM_INT);
            $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
            $stmt->execute();
            $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            // Vérification que les offres existent
            if (!$offers) {
                throw new \Exception("Aucune offre trouvée dans la base de données.");
            }

            if ($this->user) {
                foreach ($offers as &$offer) {
                    $offer['isFavorite'] = $this->isInWishlist($offer['idOffer']);
                }
            }
    
            // Affichage avec Twig
            $this->render('offres.twig', [
                'Offer' => $offers,
                'currentPage' => $page,
                'totalPages' => $totalPages
            ]);
    
        } catch (PDOException $e) {
            echo "Erreur SQL : " . $e->getMessage();
        } catch (\Exception $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }
    

    /**
     * Ajout d'une offre
     */
    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $nom = filter_input(INPUT_POST, 'NameOffer', FILTER_SANITIZE_STRING);
                $desc = filter_input(INPUT_POST, 'DescOffer', FILTER_SANITIZE_STRING);
                $renum = filter_input(INPUT_POST, 'RemunOffer', FILTER_SANITIZE_STRING);
                $date = filter_input(INPUT_POST, 'DateOffer', FILTER_SANITIZE_STRING);
                $idCompany = filter_input(INPUT_POST, 'idCompany', FILTER_SANITIZE_NUMBER_INT);
    
                if (!empty($nom) && !empty($desc) && !empty($renum) && !empty($date) && !empty($idCompany)) {
                    $stmt = $this->pdo->prepare("INSERT INTO Offer (NameOffer, DescOffer, RemunOffer, DateOffer, idCompany) 
                        VALUES (:nom, :descoffer, :renum, :dateoffer, :idCompany)
                    ");
                    $stmt->execute([
                        ':nom' => $nom,
                        ':descoffer' => $desc,
                        ':renum' => $renum,
                        ':dateoffer' => $date,
                        ':idCompany' => $idCompany
                    ]);
    
                    header('Location: ?page=offer');
                    exit;
                } else {
                    throw new \Exception("Tous les champs doivent être remplis.");
                }
            } catch (PDOException $e) {
                $this->render('add-offer.twig', [
                    'error' => "Erreur lors de l'ajout de l'offre : " . $e->getMessage(),
                    'companies' => $this->getCompanies()
                ]);
                return;
            } catch (\Exception $e) {
                $this->render('add-offer.twig', [
                    'error' => $e->getMessage(),
                    'companies' => $this->getCompanies()
                ]);
                return;
            }
        }
    
        // Récupération des entreprises et affichage du formulaire
        $this->render('add-offer.twig', [
            'companies' => $this->getCompanies()
        ]);
    }
    
    /**
     * Récupérer toutes les entreprises pour le menu déroulant
     */
    private function getCompanies() {
        try {
            $stmt = $this->pdo->query("SELECT idCompany, NameCompany, City FROM Company");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Modification d'une offre
     */
    public function edit($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $nom = filter_input(INPUT_POST, 'NameOffer', FILTER_SANITIZE_STRING);
                $desc = filter_input(INPUT_POST, 'DescOffer', FILTER_SANITIZE_STRING);
                $renum = filter_input(INPUT_POST, 'RemunOffer', FILTER_SANITIZE_STRING);
                $date = filter_input(INPUT_POST, 'DateOffer', FILTER_SANITIZE_STRING);

                if (!empty($nom) && !empty($desc) && !empty($renum) && !empty($date)) {
                    $stmt = $this->pdo->prepare("UPDATE Offer SET NameOffer = :nom, DescOffer = :descoffer, RemunOffer = :renum, DateOffer = :dateoffer WHERE idOffer = :id");
                    $stmt->execute([
                        ':nom' => $nom,
                        ':descoffer' => $desc,
                        ':renum' => $renum,
                        ':dateoffer' => $date,
                        ':id' => $id
                    ]);

                    header('Location: index.php?page=offer');
                    exit;
                } else {
                    throw new \Exception("Tous les champs doivent être remplis.");
                }
            } catch (PDOException $e) {
                $this->render('edit-offer.twig', [
                    'error' => "Erreur lors de la modification : " . $e->getMessage(),
                    'offer' => ['idOffer' => $id]    
                ]);
                return;
            }
        }

        try {
            $stmt = $this->pdo->prepare("SELECT * FROM Offer WHERE idOffer = :id");
            $stmt->execute([':id' => $id]);
            $offer = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$offer) {
                header('Location: index.php?page=offer');
                exit;
            }

            $this->render('edit-offer.twig', ['offer' => $offer]);
        } catch (PDOException $e) {
            $this->render('edit-offer.twig', [
                'error' => "Erreur lors de la récupération de l'offre : " . $e->getMessage()
            ]);
        }
    }

    /**
     * Suppression d'une offre
     */
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['id']) || empty($_POST['id'])) {
                echo "ID manquant pour la suppression.";
                return;
            }

            $id = (int) $_POST['id'];

            try {
                $stmt = $this->pdo->prepare("DELETE FROM Offer WHERE idOffer = :id");
                $stmt->execute([':id' => $id]);

                header('Location: index.php?page=offer');
                exit;
            } catch (PDOException $e) {
                echo "Erreur lors de la suppression : " . $e->getMessage();
            }
        } else {
            if (!isset($_GET['id']) || empty($_GET['id'])) {
                echo "Aucune offre spécifiée.";
                return;
            }

            $id = (int) $_GET['id'];

            try {
                $stmt = $this->pdo->prepare("SELECT * FROM Offer WHERE idOffer = :id");
                $stmt->execute([':id' => $id]);
                $offer = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$offer) {
                    echo "Offre non trouvée.";
                    return;
                }

                echo $this->twig->render('delete-offer.twig', ['offer' => $offer]);
            } catch (PDOException $e) {
                echo "Erreur : " . $e->getMessage();
            }
        }
    }

    public function postuler($id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT Offer.*, Company.NameCompany 
                FROM Offer
                JOIN Company ON Offer.idCompany = Company.idCompany
                WHERE idOffer = :id
            ");
            $stmt->execute([':id' => $id]);
            $offer = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$offer) {
                throw new \Exception("Offre non trouvée");
            }
    
            $this->render('postuler.twig', [
                'offer' => $offer
            ]);
        } catch (PDOException $e) {
            $this->render('postuler.twig', [
                'error' => "Erreur lors de la récupération de l'offre: " . $e->getMessage()
            ]);
        } catch (\Exception $e) {
            $this->render('postuler.twig', [
                'error' => $e->getMessage()
            ]);
        }
    }


    //================================================================================
    /**
    * Ajouter une offre aux favoris
    */
    public function addToWishlist($idOffer) {
        if (!$this->user) {
            header('Location: /login');
            exit;
        }

        try {
            // Vérifier si l'offre existe déjà dans les favoris
            $checkStmt = $this->pdo->prepare("SELECT * FROM WishList WHERE idUser = :idUser AND idOffer = :idOffer");
            $checkStmt->execute([
                ':idUser' => $this->user['id'],
                ':idOffer' => $idOffer
            ]);

            if ($checkStmt->fetch()) {
                // Si déjà dans les favoris, on le retire
                $stmt = $this->pdo->prepare("DELETE FROM WishList WHERE idUser = :idUser AND idOffer = :idOffer");
            } else {
                // Sinon on l'ajoute
                $stmt = $this->pdo->prepare("INSERT INTO WishList (idUser, idOffer) VALUES (:idUser, :idOffer)");
            }

            $stmt->execute([
                ':idUser' => $this->user['id'],
                ':idOffer' => $idOffer
            ]);

            // Retourner un statut JSON pour une requête AJAX
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;

        } catch (PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * Vérifier si une offre est dans les favoris
     */
    protected function isInWishlist($idOffer) {
        if (!$this->user) return false;

        try {
            $stmt = $this->pdo->prepare("SELECT * FROM WishList WHERE idUser = :idUser AND idOffer = :idOffer");
            $stmt->execute([
                ':idUser' => $this->user['id'],
                ':idOffer' => $idOffer
            ]);
            return (bool) $stmt->fetch();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function toggleWishlist($idOffer) {
        if (!$this->user) {
            $_SESSION['error'] = "Vous devez être connecté pour ajouter aux favoris";
            header('Location: ?page=login');
            exit;
        }
    
        try {
            // Vérifier si l'offre existe déjà dans les favoris
            $checkStmt = $this->pdo->prepare("SELECT * FROM WishList WHERE idUser = :idUser AND idOffer = :idOffer");
            $checkStmt->execute([
                ':idUser' => $this->user['id'],
                ':idOffer' => $idOffer
            ]);
    
            if ($checkStmt->fetch()) {
                // Si déjà dans les favoris, on le retire
                $stmt = $this->pdo->prepare("DELETE FROM WishList WHERE idUser = :idUser AND idOffer = :idOffer");
                $message = "Offre retirée des favoris";
            } else {
                // Sinon on l'ajoute
                $stmt = $this->pdo->prepare("INSERT INTO WishList (idUser, idOffer) VALUES (:idUser, :idOffer)");
                $message = "Offre ajoutée aux favoris";
            }
    
            $stmt->execute([
                ':idUser' => $this->user['id'],
                ':idOffer' => $idOffer
            ]);
    
            $_SESSION['success'] = $message;
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '?page=offres'));
            exit;
    
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur: " . $e->getMessage();
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '?page=offres'));
            exit;
        }
    }
}
