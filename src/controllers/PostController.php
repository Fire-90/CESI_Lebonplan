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
                // Récupération et filtrage des données
                $nom = filter_input(INPUT_POST, 'NameOffer', FILTER_SANITIZE_STRING);
                $desc = filter_input(INPUT_POST, 'DescOffer', FILTER_SANITIZE_STRING);
                $renum = filter_input(INPUT_POST, 'RemunOffer', FILTER_SANITIZE_STRING);
                $date = filter_input(INPUT_POST, 'DateOffer', FILTER_SANITIZE_STRING);
                $idCompany = filter_input(INPUT_POST, 'idCompany', FILTER_SANITIZE_NUMBER_INT);
    
                // Validation des données
                if (empty($nom) || empty($desc) || empty($renum) || empty($date) || empty($idCompany)) {
                    throw new \Exception("Tous les champs doivent être remplis.");
                }
    
                // Insertion dans la base de données
                $stmt = $this->pdo->prepare("INSERT INTO Offer 
                    (NameOffer, DescOffer, RemunOffer, DateOffer, idCompany) 
                    VALUES (:nom, :desc, :renum, :date, :idCompany)");
    
                $stmt->execute([
                    ':nom' => $nom,
                    ':desc' => $desc,
                    ':renum' => $renum,
                    ':date' => $date,
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
        $companies = $this->getCompanies();
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Debug: Afficher les données POST brutes
                error_log("POST data: " . print_r($_POST, true));
    
                // Récupération directe depuis $_POST avec trim()
                $data = [
                    'NameOffer' => trim($_POST['NameOffer'] ?? ''),
                    'DescOffer' => trim($_POST['DescOffer'] ?? ''),
                    'RemunOffer' => trim($_POST['RemunOffer'] ?? ''),
                    'DateOffer' => trim($_POST['DateOffer'] ?? ''),
                    'idCompany' => $_POST['idCompany'] ?? null
                ];
    
                // Debug: Afficher les données après traitement
                error_log("Processed data: " . print_r($data, true));
    
                // Validation renforcée
                $errors = [];
                foreach ($data as $key => $value) {
                    if ($value === '' || $value === null) {
                        $errors[] = $key;
                    }
                }
    
                if (!empty($errors)) {
                    error_log("Champs manquants: " . implode(', ', $errors));
                    throw new \Exception("Tous les champs doivent être remplis. Champs manquants: " . implode(', ', $errors));
                }
    
                // Conversion de la date si format français
                if (preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $data['DateOffer'], $matches)) {
                    $data['DateOffer'] = "$matches[3]-$matches[2]-$matches[1]";
                }
    
                // Mise à jour en base
                $stmt = $this->pdo->prepare("UPDATE Offer SET 
                    NameOffer = :nom, 
                    DescOffer = :desc, 
                    RemunOffer = :renum, 
                    DateOffer = :date,
                    idCompany = :idCompany
                    WHERE idOffer = :id");
    
                $success = $stmt->execute([
                    ':nom' => $data['NameOffer'],
                    ':desc' => $data['DescOffer'],
                    ':renum' => (int)preg_replace('/[^0-9]/', '', $data['RemunOffer']), // Nettoyage numérique
                    ':date' => $data['DateOffer'],
                    ':idCompany' => (int)$data['idCompany'],
                    ':id' => $id
                ]);
    
                if (!$success) {
                    throw new \Exception("Échec de la mise à jour en base de données");
                }
    
                header('Location: index.php?page=offres');
                exit;
    
            } catch (\Exception $e) {
                error_log("Erreur dans edit(): " . $e->getMessage());
                $this->render('edit-offer.twig', [
                    'error' => $e->getMessage(),
                    'offer' => array_merge(['idOffer' => $id], $_POST),
                    'companies' => $companies
                ]);
                return;
            }
        }

        try {
            $stmt = $this->pdo->prepare("SELECT * FROM Offer WHERE idOffer = :id");
            $stmt->execute([':id' => $id]);
            $offer = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$offer) {
                header('Location: index.php?page=offres');
                exit;
            }
    
            // Passer l'entreprise à la vue Twig
            $this->render('edit-offer.twig', [
                'offer' => $offer,
                'companies' => $companies,
                'error' => null
            ]);
        } catch (PDOException $e) {
            $this->render('edit-offer.twig', [
                'error' => "Erreur : " . $e->getMessage(),
                'companies' => $companies
            ]);
        }
    }
    /**
     * Suppression d'une offre
     */
    public function delete($id) {
        try {
            // Mode debug
            error_log("Méthode delete() appelée");
            error_log("Méthode HTTP: " . $_SERVER['REQUEST_METHOD']);
            error_log("Données POST: " . print_r($_POST, true));
            error_log("Données GET: " . print_r($_GET, true));
    
            // Récupération de l'ID
            $id = $_POST['id'] ?? $_GET['id'] ?? null;
            error_log("ID reçu: " . var_export($id, true));
    
            if (!$id || !is_numeric($id)) {
                throw new \Exception("ID d'offre invalide");
            }
            $id = (int)$id;
    
            // Vérification si l'offre existe
            $stmt = $this->pdo->prepare("SELECT * FROM Offer WHERE idOffer = ?");
            $stmt->execute([$id]);
            $offer = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$offer) {
                throw new \Exception("Offre introuvable");
            }
    
            // Vérification confirmation suppression
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                error_log("Tentative de suppression de l'offre ID: $id");
                
                $delete = $this->pdo->prepare("DELETE FROM Offer WHERE idOffer = ?");
                $delete->execute([$id]);
                
                $count = $delete->rowCount();
                error_log("Lignes affectées: $count");
                
                if ($count > 0) {
                    error_log("Suppression réussie, redirection vers offres");
                    header("Location: http://cesi-static.local/public/index.php?page=offres");
                    exit;
                }
                throw new \Exception("Erreur lors de la suppression");
            }
    
            // Affichage de la confirmation
            $this->render('delete-offer.twig', [
                'offer' => $offer,
                'current_url' => "http://cesi-static.local/public/index.php?page=delete-offer&id=$id"
            ]);
    
        } catch (\Exception $e) {
            error_log("ERREUR: " . $e->getMessage());
            $this->render('error.twig', [
                'message' => $e->getMessage()
            ]);
        }
    }
    

public function postuler($id) {
    try {
        // Récupération de l'offre
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