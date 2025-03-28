<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Controllers\HomeController;
use Controllers\EntrepriseController;

// Récupérer la page demandée dans l'URL
$page = $_GET['page'] ?? 'home';

switch ($page) {
    case 'entreprises':
        $controller = new HomeController();
        $controller->entreprises();
        break;
    case 'ajouter-entreprise':  // Ajout du cas pour la page d'ajout d'entreprise
        $controller = new EntrepriseController();
        $controller->add();  // Appel à la méthode add() du EntrepriseController
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
    default:
        $controller = new HomeController();
        $controller->home();
        break;
}
?>
