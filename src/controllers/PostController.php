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
    
            // Récupérer le terme de recherche
            $searchTerm = filter_input(INPUT_GET, 'recherche', FILTER_SANITIZE_STRING);
            $searchTerm = trim($searchTerm);
    
            // Requête de base
            $sqlBase = "SELECT Offer.idOffer, Offer.NameOffer, Offer.DescOffer, Offer.RemunOffer, 
                       Offer.DateOffer, Company.NameCompany, Company.City 
                       FROM Offer JOIN Company ON Offer.idCompany = Company.idCompany";
    
            // Clause WHERE si recherche
            $whereClause = "";
            $params = [];
            if (!empty($searchTerm)) {
                $whereClause = " WHERE (Offer.NameOffer LIKE :searchTerm OR 
                              Offer.DescOffer LIKE :searchTerm OR 
                              Company.NameCompany LIKE :searchTerm OR 
                              Company.City LIKE :searchTerm)";
                $params[':searchTerm'] = "%$searchTerm%";
            }
    
            // Comptage total
            $stmtTotal = $this->pdo->prepare("SELECT COUNT(*) as total FROM Offer 
                                            JOIN Company ON Offer.idCompany = Company.idCompany" . $whereClause);
            foreach ($params as $key => $value) {
                $stmtTotal->bindValue($key, $value);
            }
            $stmtTotal->execute();
            $totalOffre = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];
            $totalPages = ceil($totalOffre / $perPage);
    
            // Récupération paginée
            $sql = $sqlBase . $whereClause . " LIMIT :start, :perPage";
            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':start', $start, PDO::PARAM_INT);
            $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
            $stmt->execute();
            $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            // Gestion des favoris
            if ($this->user) {
                foreach ($offers as &$offer) {
                    $offer['isFavorite'] = $this->isInWishlist($offer['idOffer']);
                }
            }
    
            // Passage des données au template
            $this->render('offres.twig', [
                'Offer' => $offers,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'searchTerm' => $searchTerm
            ]);
    
        } catch (PDOException $e) {
            // Gestion des erreurs
        }
    }

    /**
     * Ajout d'une offre
     */
    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Récupération et filtrage des données
                $nom = filter_input(INPUT_POST, 'NameOffer', FILTER_SANITIZE_STRING);
                $desc = filter_input(INPUT_POST, 'DescOffer', FILTER_SANITIZE_STRING);
                $renum = filter_input(INPUT_POST, 'RemunOffer', FILTER_SANITIZE_STRING);
                $idCompany = filter_input(INPUT_POST, 'idCompany', FILTER_SANITIZE_NUMBER_INT);

                $renum = number_format($renum, 0, ',', ' ') . '€/mois';
    
                // Validation des données
                if (empty($nom) || empty($desc) || empty($renum) || empty($idCompany)) {
                    throw new \Exception("Tous les champs doivent être remplis.");
                }
    
                // Insertion dans la base de données
                $stmt = $this->pdo->prepare("INSERT INTO Offer 
                    (NameOffer, DescOffer, RemunOffer, DateOffer, idCompany) 
                    VALUES (:nom, :desc, :renum, NOW(), :idCompany)");
    
                $stmt->execute([
                    ':nom' => $nom,
                    ':desc' => $desc,
                    ':renum' => $renum,
                    ':idCompany' => $idCompany
                ]);
    
                // Redirection après succès
                header('Location: ?page=offres');
                exit;
    
            } catch (PDOException $e) {
                $error = "Erreur lors de l'ajout de l'offre : " . $e->getMessage();
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }
    
        // Affichage du formulaire (avec erreur le cas échéant)
        $this->render('add-offer.twig', [
            'error' => $error ?? null,
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
                    $renum = number_format($renum, 0, ',', ' ') . '€/mois';
                    
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
        // Récupération de l'offre de base
        $stmt = $this->pdo->prepare("
            SELECT Offer.*, Company.NameCompany 
            FROM Offer
            JOIN Company ON Offer.idCompany = Company.idCompany
            WHERE Offer.idOffer = :id
        ");
        $stmt->execute([':id' => $id]);
        $offer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$offer) {
            throw new \Exception("Offre non trouvée");
        }

        // Récupération des compétences associées à l'offre avec leurs noms
        $stmtCompetences = $this->pdo->prepare("
            SELECT Competence.NameCompetence 
            FROM OfferCompetence
            JOIN Competence ON OfferCompetence.idCompetence = Competence.idCompetence
            WHERE OfferCompetence.idOffer = :id
        ");
        $stmtCompetences->execute([':id' => $id]);
        $competences = $stmtCompetences->fetchAll(PDO::FETCH_COLUMN);

        // Ajout des compétences à l'offre pour le template
        $offer['Competences'] = $competences;

        // Traitement du formulaire
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validation des données
            $errors = [];
            $gender = filter_input(INPUT_POST, 'Gender', FILTER_SANITIZE_STRING);
            $lastname = filter_input(INPUT_POST, 'lastname', FILTER_SANITIZE_STRING);
            $firstname = filter_input(INPUT_POST, 'surname', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $message = filter_input(INPUT_POST, 'feedbacks', FILTER_SANITIZE_STRING);
            $hasLicense = isset($_POST['Permis']) ? 1 : 0;
            $hasVehicle = isset($_POST['Car']) ? 1 : 0;
            $hasCertifications = isset($_POST['Certication']) ? 1 : 0;
            $isAdult = ($_POST['Majeur'] === 'YES') ? 1 : 0;

            // Validation du fichier
            $resumePath = "/files";
            if (isset($_FILES['file-upload'])) {
                $file = $_FILES['file-upload'];
                $allowedFormats = ['pdf', 'doc', 'docx', 'odt', 'rtf', 'jpg', 'png'];
                $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                if (!in_array($fileExtension, $allowedFormats)) {
                    $errors[] = "Format de fichier non valide.";
                } elseif ($file['size'] > 2 * 1024 * 1024) {
                    $errors[] = "Le fichier est trop volumineux (max 2Mo).";
                } else {
                    $uploadDir = '../uploads/resumes/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $resumePath = $uploadDir . uniqid() . '.' . $fileExtension;
                    move_uploaded_file($file['tmp_name'], $resumePath);
                }
            }

            if (empty($lastname)) $errors[] = "Le nom est requis.";
            if (empty($firstname)) $errors[] = "Le prénom est requis.";
            if (!$email) $errors[] = "L'email est invalide.";
            if (empty($message)) $errors[] = "Le message est requis.";
            if (!$resumePath) $errors[] = "Le CV est requis.";

            if (empty($errors)) {
                // Enregistrement en base de données
                $stmt = $this->pdo->prepare("
                    INSERT INTO Apply (
                        DateApply, ResumeApply, idUser, idOffer,
                        Gender, LastName, FirstName, Email, HasLicense, 
                        HasVehicle, HasCertifications, IsAdult, Message
                    ) VALUES (
                        NOW(), :resume, :idUser, :idOffer,
                        :gender, :lastname, :firstname, :email, :hasLicense,
                        :hasVehicle, :hasCertifications, :isAdult, :messageText
                    )
                ");

                $stmt->execute([
                    ':resume' => $resumePath,
                    ':idUser' => $this->user ? $this->user['id'] : null,
                    ':idOffer' => $id,
                    ':gender' => $gender,
                    ':lastname' => $lastname,
                    ':firstname' => $firstname,
                    ':email' => $email,
                    ':hasLicense' => $hasLicense,
                    ':hasVehicle' => $hasVehicle,
                    ':hasCertifications' => $hasCertifications,
                    ':isAdult' => $isAdult,
                    ':messageText' => $message
                ]);

                $_SESSION['success'] = "Votre candidature a été envoyée avec succès !";
                header('Location: ?page=offres');
                exit;
            }

            // Si erreurs, réafficher le formulaire avec les erreurs
            $this->render('postuler.twig', [
                'offer' => $offer,
                'errors' => $errors,
                'formData' => $_POST
            ]);
            return;
        }

        // Affichage initial du formulaire
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
