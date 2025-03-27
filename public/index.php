<?php

require_once 'src/controllers/HomeController.php';

$controller = new HomeController();

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
