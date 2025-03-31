<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Controllers\HomeController;
use Controllers\EntrepriseController;

// Définir d'abord la pagination avant d'utiliser $page
$pagination = isset($_GET['pagination']) ? (int) $_GET['pagination'] : 1;
$pagination = max(1, $pagination); // Assurer que la page ne soit pas < 1

// Récupérer la page demandée dans l'URL
$page = $_GET['page'] ?? 'home';

switch ($page) {
    case 'entreprises':
        $controller = new EntrepriseController();
        $controller->index($pagination);  // On passe bien la pagination
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
                header('Location: index.php?page=entreprises'); // Redirection si pas d'ID valide
                exit;
            }
            break;
        
    case 'offres':
        $controller = new HomeController();
        $controller->offres();
        break;
    case 'whishlist':
        $controller = new HomeController();
        $controller->whishlist();
        break;
    case 'contact':
        $controller = new HomeController();
        $controller->contact();
        break;
    case 'postuler':
        $controller = new HomeController();
        $controller->postuler();
        break;   
        case 'login':
            $controller = new HomeController();
            $controller->login();
            break;
    default:
        $controller = new HomeController();
        $controller->home();
        break;
}

?>
