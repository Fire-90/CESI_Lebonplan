<?php

namespace Controllers;

use Core\database;
use Core\TemplateEngine;
use PDO;

class AccountController {
    private $twig;
    private $pdo;

    public function __construct() {
        $this->twig = TemplateEngine::getTwig();
        $this->pdo = Database::getConnection();
    }

    public function login() {
        $errorMessage = null;
        $user = null;

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
                    setcookie('user', json_encode([
                        'id' => $admin['idAdmin'],
                        'name' => $admin['NameAdmin'],
                        'email' => $admin['EmailAdmin'],
                        'role' => 'admin'
                    ]), time() + (86400 * 30), "/");
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
                    setcookie('user', json_encode([
                        'id' => $user['idUser'],
                        'name' => $user['NameUser'],
                        'email' => $user['EmailUser'],
                        'role' => 'user'
                    ]), time() + (86400 * 30), "/");
                    header('Location: /user/profile');
                    exit;
                }

                setcookie('errorMessage', 'Email ou mot de passe incorrect', time() + 3600, "/");
                error_log("Erreur de connexion: Email ou mot de passe incorrect");

            } catch (\PDOException $e) {
                setcookie('errorMessage', 'Une erreur est survenue lors de la connexion', time() + 3600, "/");
                error_log("Erreur PDO: " . $e->getMessage());
            } catch (\Exception $e) {
                setcookie('errorMessage', 'Une erreur inattendue est survenue', time() + 3600, "/");
                error_log("Erreur inattendue: " . $e->getMessage());
            }
        }

        // Vérifiez si le cookie 'errorMessage' est défini
        if (isset($_COOKIE['errorMessage'])) {
            $errorMessage = $_COOKIE['errorMessage'];
            setcookie('errorMessage', '', time() - 3600, "/"); // Supprimez le cookie après l'avoir lu
        }

        if (isset($_COOKIE['user'])) {
            $user = json_decode($_COOKIE['user'], true);
        }

        echo $this->twig->render('login.twig', ['errorMessage' => $errorMessage, 'user' => $user]);
    }

    public function signup() {
        $errorMessage = null;
        $user = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Ajoutez un log pour voir les données reçues
                error_log(print_r($_POST, true));

                $name = $_POST['txt'] ?? null;
                $email = $_POST['email'] ?? null;
                $phone = $_POST['broj'] ?? null; // Note: 'broj' et non 'broj' dans votre HTML
                $password = $_POST['pswd'] ?? null;

                // Validation basique
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
                    // Redirection après succès
                    header("Location: ?page=login&success=1");
                    exit;
                } else {
                    throw new \Exception("Erreur lors de l'insertion en base");
                }

            } catch (\Exception $e) {
                // Log l'erreur complète
                error_log("Erreur inscription: " . $e->getMessage());

                // Message utilisateur plus clair
                setcookie('errorMessage', 'Erreur: ' . $e->getMessage(), time() + 3600, "/");
            }
        }

        // Vérifiez si le cookie 'errorMessage' est défini
        if (isset($_COOKIE['errorMessage'])) {
            $errorMessage = $_COOKIE['errorMessage'];
            setcookie('errorMessage', '', time() - 3600, "/"); // Supprimez le cookie après l'avoir lu
        }

        if (isset($_COOKIE['user'])) {
            $user = json_decode($_COOKIE['user'], true);
        }

        echo $this->twig->render('login.twig', ['errorMessage' => $errorMessage, 'user' => $user]);
    }

    public function logout() {
        setcookie('user', '', time() - 3600, "/");
        header('Location: /login');
        exit;
    }
}
