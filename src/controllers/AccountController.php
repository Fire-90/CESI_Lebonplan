<?php

namespace Controllers;

use Core\database;
use PDO;

class AccountController extends BaseController {
    private $pdo;

    public function __construct() {
        parent::__construct();
        $this->pdo = Database::getConnection();
    }

    public function login() {
        $errorMessage = $_SESSION['errorMessage'] ?? null;
        unset($_SESSION['errorMessage']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $email = $_POST['email'];
                $password = $_POST['pswd'];

                $userQuery = "SELECT * FROM User WHERE EmailUser = :email";
                $userStmt = $this->pdo->prepare($userQuery);
                $userStmt->bindParam(':email', $email);
                $userStmt->execute();
                $user = $userStmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['PassWordUser'])) {
                    $_SESSION['user'] = [
                        'id' => $user['idUser'],
                        'name' => $user['NameUser'],
                        'email' => $user['EmailUser']
                    ];
                    $_SESSION['permLVL'] = $user['PermLVL']; // Stocke le niveau de permission
                    
                    header('Location: /');
                    exit;
                }

                $_SESSION['errorMessage'] = 'Email ou mot de passe incorrect';
                header('Location: /login');
                exit;

            } catch (\PDOException $e) {
                $_SESSION['errorMessage'] = 'Une erreur est survenue lors de la connexion';
                header('Location: /login');
                exit;
            }
        }

        $this->render('login.twig', ['errorMessage' => $errorMessage]);
    }

    public function signup() {
        $errorMessage = $_SESSION['errorMessage'] ?? null;
        unset($_SESSION['errorMessage']);
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $name = $_POST['txt'] ?? null;
                $email = $_POST['email'] ?? null;
                $phone = $_POST['broj'] ?? null;
                $password = $_POST['pswd'] ?? null;
    
                if (!$name || !$email || !$password) {
                    throw new \Exception('Tous les champs obligatoires ne sont pas remplis');
                }
    
                $checkStmt = $this->pdo->prepare("SELECT EmailUser FROM User WHERE EmailUser = ?");
                $checkStmt->execute([$email]);
    
                if ($checkStmt->fetch()) {
                    throw new \Exception("Cet email est déjà utilisé");
                }
    
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $this->pdo->prepare(
                    "INSERT INTO User (NameUser, EmailUser, PhoneUser, PassWordUser, PermLVL)
                     VALUES (?, ?, ?, ?, ?)"
                );
    
                $success = $stmt->execute([$name, $email, $phone, $hashedPassword, 0]);
                if ($success) {
                    $_SESSION['successMessage'] = 'Inscription réussie! Vous pouvez maintenant vous connecter.';
                    header("Location: /login");
                    exit;
                } else {
                    throw new \Exception("Erreur lors de l'insertion en base");
                }
    
            } catch (\Exception $e) {
                $_SESSION['errorMessage'] = 'Erreur: ' . $e->getMessage();
                header('Location: /signup');
                exit;
            }
        }
    
        $this->render('login.twig', ['errorMessage' => $errorMessage]);
    }

    public function logout() {
        session_unset();
        session_destroy();
        header('Location: /login');
        exit;
    }

    public function profile() {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
    
        try {
            $userId = $_SESSION['user']['id'];
            $stmt = $this->pdo->prepare("SELECT * FROM User WHERE idUser = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($user) {
                $_SESSION['user'] = [
                    'id' => $user['idUser'],
                    'name' => $user['NameUser'],
                    'email' => $user['EmailUser'],
                    'phone' => $user['PhoneUser'] ?? null
                ];
                $_SESSION['permLVL'] = $user['PermLVL'];
            }
    
            $isEditing = isset($_GET['edit']) && $_GET['edit'] === 'true';
    
            $this->render('profile.twig', [
                'successMessage' => $_SESSION['successMessage'] ?? null,
                'errorMessage' => $_SESSION['errorMessage'] ?? null,
                'isEditing' => $isEditing
            ]);
            unset($_SESSION['successMessage'], $_SESSION['errorMessage']);
    
        } catch (\PDOException $e) {
            $_SESSION['errorMessage'] = 'Une erreur est survenue lors de la récupération des informations du profil';
            header('Location: /');
            exit;
        }
    }

    public function updateProfile() {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $userId = $_SESSION['user']['id'];
                $name = $_POST['name'] ?? null;
                $email = $_POST['email'] ?? null;
                $phone = $_POST['phone'] ?? null;
                $currentPassword = $_POST['current_password'] ?? null;
                $newPassword = $_POST['new_password'] ?? null;
    
                // Vérification obligatoire du mot de passe actuel
                if (empty($currentPassword)) {
                    throw new \Exception("Le mot de passe actuel est requis");
                }
    
                // Vérifier si l'email est déjà utilisé par un autre utilisateur
                if ($email !== $_SESSION['user']['email']) {
                    $checkStmt = $this->pdo->prepare("SELECT idUser FROM User WHERE EmailUser = ? AND idUser != ?");
                    $checkStmt->execute([$email, $userId]);
                    if ($checkStmt->fetch()) {
                        throw new \Exception("Cet email est déjà utilisé par un autre compte");
                    }
                }
    
                // Récupérer le mot de passe actuel pour vérification
                $userStmt = $this->pdo->prepare("SELECT PassWordUser FROM User WHERE idUser = ?");
                $userStmt->execute([$userId]);
                $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    
                if (!$user || !password_verify($currentPassword, $user['PassWordUser'])) {
                    throw new \Exception("Le mot de passe actuel est incorrect");
                }
    
                // Préparer la requête de mise à jour
                $updateData = [
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'id' => $userId
                ];
    
                $query = "UPDATE User SET NameUser = :name, EmailUser = :email, PhoneUser = :phone";
                if ($newPassword) {
                    $query .= ", PassWordUser = :password";
                    $updateData['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                }
                $query .= " WHERE idUser = :id";
    
                $stmt = $this->pdo->prepare($query);
                $stmt->execute($updateData);
    
                // Mettre à jour la session
                $_SESSION['user']['name'] = $name;
                $_SESSION['user']['email'] = $email;
                $_SESSION['user']['phone'] = $phone;
                $_SESSION['successMessage'] = 'Profil mis à jour avec succès';
    
            } catch (\Exception $e) {
                $_SESSION['errorMessage'] = 'Erreur: ' . $e->getMessage();
            }
    
            header('Location: /profile');
            exit;
        }
    }

    public function deleteAccount() {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $userId = $_SESSION['user']['id'];
                $password = $_POST['password'] ?? null;

                // Vérifier le mot de passe avant suppression
                $userStmt = $this->pdo->prepare("SELECT PassWordUser FROM User WHERE idUser = ?");
                $userStmt->execute([$userId]);
                $user = $userStmt->fetch(PDO::FETCH_ASSOC);

                if (!$user || !password_verify($password, $user['PassWordUser'])) {
                    throw new \Exception("Mot de passe incorrect");
                }

                // Supprimer l'utilisateur
                $deleteStmt = $this->pdo->prepare("DELETE FROM User WHERE idUser = ?");
                $deleteStmt->execute([$userId]);

                // Déconnecter l'utilisateur
                session_unset();
                session_destroy();

                $_SESSION['successMessage'] = 'Votre compte a été supprimé avec succès';
                header('Location: /');
                exit;

            } catch (\Exception $e) {
                $_SESSION['errorMessage'] = 'Erreur: ' . $e->getMessage();
                header('Location: /profile');
                exit;
            }
        }
    }
}