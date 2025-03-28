<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Controllers\HomeController;
use Controllers\EntrepriseController;

$controller = new HomeController();

// Récupérer la page demandée dans l'URL
$page = $_GET['page'] ?? 'home';

switch ($page) {
    case 'entreprises':
        $controller->entreprises();
        break;
    case 'offres':
        $controller->offres();
        break;
    case 'whishlist':
        $controller->whishlist();
        break;
    case 'contact':
        $controller->contact();
        break;
    default:
        $controller->home();
        break;
}
