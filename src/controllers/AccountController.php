<?php

namespace Controllers;

use Core\database;
use Core\TemplateEngine;
use PDO;

class AccountController extends BaseController {
    private $pdo;

    public function __construct() {
        parent::__construct(); // Utilise le constructeur parent
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
                        'email' => $user['EmailUser'],
                        'role' => 'user'
                    ];
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

        $this->render('login.twig', [
            'errorMessage' => $errorMessage
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

        $this->render('signup.twig', [
            'errorMessage' => $errorMessage
        ]);
    }

    public function logout() {
        session_unset();
        session_destroy();
        header('Location: /login');
        exit;
    }
}