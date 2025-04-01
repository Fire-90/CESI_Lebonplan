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
            $stmtTotal = $this->pdo->query("SELECT COUNT(*) as total FROM Offer");
            $totalOffre = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];
            $totalPages = ceil($totalOffre / $perPage);
    
            // Récupération des offres paginées
            $stmt = $this->pdo->prepare("SELECT * FROM Offer LIMIT :start, :perPage");
            $stmt->bindValue(':start', $start, PDO::PARAM_INT);
            $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
            $stmt->execute();
            $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            // Vérification que les offres existent
            if (!$offers) {
                throw new \Exception("Aucune offre trouvée dans la base de données.");
            }
    
            // Affichage avec Twig
            $this->render('offres.twig', [
                'Offer' => $offers,  // 🔥 Assure-toi que la clé transmise au template est bien 'Offer'
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

                if (!empty($nom) && !empty($desc) && !empty($renum) && !empty($date)) {
                    $stmt = $this->pdo->prepare("INSERT INTO Offer (NameOffer, DescOffer, RemunOffer, DateOffer) VALUES (:nom, :descoffer, :renum, :dateoffer)");
                    $stmt->execute([
                        ':nom' => $nom,
                        ':descoffer' => $desc,
                        ':renum' => $renum,
                        ':dateoffer' => $date
                    ]);

                    header('Location: ?page=offer');
                    exit;
                } else {
                    throw new \Exception("Tous les champs doivent être remplis.");
                }
            } catch (PDOException $e) {
                $this->render('add-offer.twig', [
                    'error' => "Erreur lors de l'ajout de l'offre : " . $e->getMessage()
                ]);
                return;
            } catch (\Exception $e) {
                $this->render('add-offer.twig', [
                    'error' => $e->getMessage()
                ]);
                return;
            }
        }

        $this->render('add-offer.twig');
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
}
