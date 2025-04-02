<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Controllers\HomeController;
use Controllers\EntrepriseController;
use Controllers\AccountController;
use Controllers\PostController;
use Controllers\WishlistController;

// Récupérer l'utilisateur depuis le cookie (si existant)
$user = isset($_COOKIE['user']) ? json_decode($_COOKIE['user'], true) : null;

// Définir la pagination
$pagination = isset($_GET['pagination']) ? (int) $_GET['pagination'] : 1;
$pagination = max(1, $pagination);

// Récupérer la page demandée
$page = $_GET['page'] ?? 'home';

switch ($page) {
    case 'entreprises':
        $controller = new EntrepriseController();
        $controller->index($pagination); 
        break;
    case 'ajout-entreprise':
        $controller = new EntrepriseController();
        $controller->add(); 
        break;
    case 'edit-entreprise':
        $controller = new EntrepriseController();
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $controller->edit((int) $_GET['id']);
        } else {
            header('Location: index.php?page=entreprises');
            exit;
        }
        break;
    case 'delete-entreprise':
        $controller = new EntrepriseController();
        $id = $_GET['id'] ?? null;
        if ($id) {
            $controller->delete();
        } else {
            header('Location: index.php?page=entreprises');
            exit;
        }
        break;

    case 'offres':
        $controller = new PostController();
            $controller->index($pagination); 
            break;
    case 'add-offer':
        $controller = new PostController();
    $controller->add(); 
        break;
    case 'edit-offer':
        $controller = new PostController();
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $controller->edit((int) $_GET['id']);
        } else {
            header('Location: index.php?page=offer'); // Redirection si pas d'ID valide
            exit;
        }
        break;
    case 'delete-offer':
        $controller = new PostController();
        $id = $_GET['id'] ?? null;
        if ($id) {
            $controller->delete();
        } else {
            header('Location: index.php?page=offer'); // Redirection si pas d'ID
            exit;
        }
    break;

    // Nouveau cas pour la wishlist
    case 'wishlist':
        $controller = new WishlistController();
        $controller->index(); 
        break;
    case 'toggle-wishlist':
        $controller = new PostController();
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $controller->toggleWishlist((int) $_GET['id']);
        } else {
            header('Location: index.php?page=offres');
            exit;
        }
        break;
    case 'contact':
        $controller = new HomeController();
        $controller->contact(); 
        break;
    case 'postuler':
        $controller = new PostController();
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $controller->postuler((int) $_GET['id']);
        } else {
            header('Location: index.php?page=offres');
            exit;
        }
        break;
    case 'login':
        $controller = new AccountController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->login();
        } else {
            echo $controller->login();
        }
        break;
    case 'signup':
        $controller = new AccountController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->signup();
        } else {
            echo $controller->signup();
        }
        break;
    case 'logout':
        $controller = new AccountController();
        $controller->logout();
        break;
    case 'legal-notice':    
        $controller = new HomeController();
        $controller->legal(); 
        break;
    case 'profile':
        $controller = new AccountController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_GET['action'] ?? $_POST['action'] ?? null;
            switch ($action) {
                case 'update':
                    $controller->updateProfile();
                    break;
                case 'delete':
                    $controller->deleteAccount();
                    break;
                default:
                    // Mode visualisation ou édition
                    $editMode = isset($_GET['edit']) && $_GET['edit'] === 'true';
                    $controller->profile();
                    break;
            }
        } else {
            $editMode = isset($_GET['edit']) && $_GET['edit'] === 'true';
            $controller->profile();
        }
        break;
    default:
        $controller = new HomeController();
        $controller->home(); 
        break;
}

?>