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

    public function logout() {
        session_unset();
        session_destroy();
        header('Location: /login');
        exit;
    }
}