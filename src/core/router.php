<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Controllers\HomeController;
use Controllers\EntrepriseController;

// Récupérer la page demandée dans l'URL
$page = $_GET['page'] ?? 'home';

switch ($page) {
    case 'entreprises':
        $controller = new EntrepriseController();
        $controller->index();
        break;
    case 'offres':
        // Assurez-vous que la méthode offres() existe dans le bon contrôleur
        $controller = new HomeController();
        $controller->offres();
        break;
    case 'whishlist':
        // Assurez-vous que la méthode whishlist() existe dans le bon contrôleur
        $controller = new HomeController();
        $controller->whishlist();
        break;
    case 'contact':
        // Assurez-vous que la méthode contact() existe dans le bon contrôleur
        $controller = new HomeController();
        $controller->contact();
        break;
    default:
        $controller = new HomeController();
        $controller->home();
        break;
}
?>
