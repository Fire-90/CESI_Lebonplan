<?php

namespace Controllers;

use Core\Database;
use Core\TemplateEngine;
use PDO;

class AccountController extends BaseController {
    private $pdo;

    public function __construct() {
        session_start(); // Démarrer la session
        $this->twig = TemplateEngine::getTwig();
        $this->pdo = Database::getConnection();
    }

    public function login() {
        $errorMessage = $_SESSION['errorMessage'] ?? null;
        unset($_SESSION['errorMessage']); // Supprimer le message après lecture

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $email = $_POST['email'];
                $password = $_POST['pswd'];

                // Vérification admin
                $adminQuery = "SELECT * FROM Admin WHERE EmailAdmin = :email";
                $adminStmt = $this->pdo->prepare($adminQuery);
                $adminStmt->bindParam(':email', $email);
                $adminStmt->execute();
                $admin = $adminStmt->fetch(PDO::FETCH_ASSOC);

                if ($admin && password_verify($password, $admin['PassWordAdmin'])) {
                    $_SESSION['user'] = [
                        'id' => $admin['idAdmin'],
                        'name' => $admin['NameAdmin'],
                        'email' => $admin['EmailAdmin'],
                        'role' => 'admin'
                    ];
                    header('Location: /admin/dashboard');
                    exit;
                }

                // Vérification user
                $userQuery = "SELECT * FROM User WHERE EmailUser = :email";
                $userStmt = $this->pdo->prepare($userQuery);
                $userStmt->bindParam(':email', $email);
                $userStmt->execute();
                $user = $userStmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['PassWordUser'])) {
                    $_SESSION['user'] = [
                        'id' => $user['idUser'],
                        'name' => $user['NameUser'],
                        'email' => $user['EmailUser'],
                        'role' => 'user'
                    ];
                    header('Location: /user/profile');
                    exit;
                }

                $_SESSION['errorMessage'] = 'Email ou mot de passe incorrect';
                header('Location: ' . $_SERVER['REQUEST_URI']);
                exit;

            } catch (\PDOException $e) {
                $_SESSION['errorMessage'] = 'Une erreur est survenue lors de la connexion';
                error_log("Erreur PDO: " . $e->getMessage());
                header('Location: ' . $_SERVER['REQUEST_URI']);
                exit;
            } catch (\Exception $e) {
                $_SESSION['errorMessage'] = 'Une erreur inattendue est survenue';
                error_log("Erreur inattendue: " . $e->getMessage());
                header('Location: ' . $_SERVER['REQUEST_URI']);
                exit;
            }
        }

        echo $this->twig->render('login.twig', [
            'errorMessage' => $errorMessage,
            'user' => $_SESSION['user'] ?? null
        ]);
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

                // Vérification email
                $checkStmt = $this->pdo->prepare("SELECT EmailUser FROM User WHERE EmailUser = ?");
                $checkStmt->execute([$email]);

                if ($checkStmt->fetch()) {
                    throw new \Exception("Cet email est déjà utilisé");
                }

                // Hash du mot de passe
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Insertion
                $stmt = $this->pdo->prepare(
                    "INSERT INTO User (NameUser, EmailUser, PhoneUser, PassWordUser)
                     VALUES (?, ?, ?, ?)"
                );

                $success = $stmt->execute([$name, $email, $phone, $hashedPassword]);

                if ($success) {
                    $_SESSION['successMessage'] = 'Inscription réussie! Vous pouvez maintenant vous connecter.';
                    header("Location: /login");
                    exit;
                } else {
                    throw new \Exception("Erreur lors de l'insertion en base");
                }

            } catch (\Exception $e) {
                error_log("Erreur inscription: " . $e->getMessage());
                $_SESSION['errorMessage'] = 'Erreur: ' . $e->getMessage();
                header('Location: ' . $_SERVER['REQUEST_URI']);
                exit;
            }
        }

        echo $this->twig->render('login.twig', [
            'errorMessage' => $errorMessage,
            'user' => $_SESSION['user'] ?? null
        ]);
    }

    public function logout() {
        session_unset(); // Supprime toutes les variables de session
        session_destroy(); // Détruit la session
        header('Location: /login');
        exit;
    }
}