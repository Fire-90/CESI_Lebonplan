<?php
namespace Controllers;

use Core\database;
use Core\TemplateEngine;
use PDO;

class AccountController
{
    private $twig;
    private $pdo;

    public function __construct()
    {
        $this->twig = TemplateEngine::getTwig();
        $this->pdo = Database::getConnection();
        session_start();
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['pswd'] ?? '';

            try {
                // Vérification Admin
                $stmt = $this->pdo->prepare("SELECT * FROM Admin WHERE EmailAdmin = ?");
                $stmt->execute([$email]);
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($admin && password_verify($password, $admin['PassWordAdmin'])) {
                    $_SESSION['user'] = [
                        'id' => $admin['idAdmin'],
                        'name' => $admin['NameAdmin'],
                        'email' => $admin['EmailAdmin'],
                        'role' => 'admin'
                    ];
                    header('Location: /?page=home');
                    exit;
                }

                // Vérification User
                $stmt = $this->pdo->prepare("SELECT * FROM User WHERE EmailUser = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['PassWordUser'])) {
                    $_SESSION['user'] = [
                        'id' => $user['idUser'],
                        'name' => $user['NameUser'],
                        'email' => $user['EmailUser'],
                        'role' => 'user'
                    ];
                    header('Location: /?page=profile');
                    exit;
                }

                throw new \Exception("Identifiants incorrects");
                
            } catch (\Exception $e) {
                echo $this->twig->render('login.twig', [
                    'error' => $e->getMessage()
                ]);
                return;
            }
        }

        echo $this->twig->render('login.twig');
    }

    public function signup()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'name' => trim($_POST['txt'] ?? ''),
                    'email' => filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL),
                    'phone' => $_POST['broj'] ?? null,
                    'password' => $_POST['pswd'] ?? ''
                ];

                // Validation
                if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
                    throw new \Exception("Tous les champs obligatoires doivent être remplis");
                }

                if (strlen($data['password']) < 8) {
                    throw new \Exception("Le mot de passe doit contenir au moins 8 caractères");
                }

                // Vérification email unique
                $stmt = $this->pdo->prepare("SELECT idUser FROM User WHERE EmailUser = ?");
                $stmt->execute([$data['email']]);
                if ($stmt->fetch()) {
                    throw new \Exception("Cet email est déjà utilisé");
                }

                // Création du compte
                $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
                $stmt = $this->pdo->prepare(
                    "INSERT INTO User (NameUser, EmailUser, PhoneUser, PassWordUser) 
                     VALUES (?, ?, ?, ?)"
                );

                $stmt->execute([
                    $data['name'],
                    $data['email'],
                    $data['phone'],
                    $hashedPassword
                ]);

                // Connexion automatique
                $_SESSION['user'] = [
                    'id' => $this->pdo->lastInsertId(),
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'role' => 'user'
                ];

                header('Location: /?page=profile');
                exit;

            } catch (\Exception $e) {
                echo $this->twig->render('login.twig', [
                    'signup_error' => $e->getMessage(),
                    'form_data' => $_POST
                ]);
                return;
            }
        }

        echo $this->twig->render('login.twig');
    }

    public function logout()
    {
        session_unset();
        session_destroy();
        header('Location: /?page=home');
        exit;
    }

    public function profile()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: /?page=login');
            exit;
        }

        echo $this->twig->render('profile.twig', [
            'user' => $_SESSION['user']
        ]);
    }
}