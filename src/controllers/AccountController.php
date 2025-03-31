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
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
            $email = trim($_POST['email']);
            $password = $_POST['pswd'];

            // Vérification admin
            $adminQuery = "SELECT * FROM Admin WHERE EmailAdmin = :email";
            $adminStmt = $this->pdo->prepare($adminQuery);
            $adminStmt->bindParam(':email', $email);
            $adminStmt->execute();
            $admin = $adminStmt->fetch();

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
            $user = $userStmt->fetch();

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

            $error = "Email ou mot de passe incorrect";
        }

        echo $this->twig->render('login.twig', [
            'error' => $error,
            'success' => $success
        ]);
    }

    public function signup() {
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
            $name = trim($_POST['txt']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['broj']);
            $password = $_POST['pswd'];
            $confirmPassword = $_POST['confirm_pswd'];

            // Validation
            if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
                $error = "Tous les champs sont obligatoires";
            } elseif ($password !== $confirmPassword) {
                $error = "Les mots de passe ne correspondent pas";
            } elseif (strlen($password) < 8) {
                $error = "Le mot de passe doit contenir au moins 8 caractères";
            } else {
                // Vérifier si l'email existe déjà
                $checkEmail = $this->pdo->prepare("SELECT EmailUser FROM User WHERE EmailUser = :email");
                $checkEmail->bindParam(':email', $email);
                $checkEmail->execute();

                if ($checkEmail->fetch()) {
                    $error = "Cet email est déjà utilisé";
                } else {
                    // Création du compte
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $this->pdo->prepare("INSERT INTO User (NameUser, EmailUser, PhoneUser, PassWordUser) VALUES (:name, :email, :phone, :password)");
                    $stmt->bindParam(':name', $name);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':phone', $phone);
                    $stmt->bindParam(':password', $hashedPassword);

                    if ($stmt->execute()) {
                        $success = "Inscription réussie! Vous pouvez maintenant vous connecter.";
                    } else {
                        $error = "Une erreur est survenue lors de l'inscription";
                    }
                }
            }
        }

        echo $this->twig->render('login.twig', [
            'error' => $error,
            'success' => $success
        ]);
    }

    public function logout() {
        session_unset();
        session_destroy();
        header('Location: /login');
        exit;
    }
}