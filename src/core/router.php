<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Controllers\HomeController;
use Controllers\EntrepriseController;

// Récupérer la page demandée dans l'URL
$page = $_GET['page'] ?? 'home';

switch ($page) {
    case 'entreprises':
        // Afficher la liste des entreprises
        $controller = new EntrepriseController();
        $controller->index();
        break;
        
    case 'ajout-entreprise':
        // Afficher le formulaire d'ajout d'entreprise
        $controller = new EntrepriseController();
        $controller->add();
        break;

    case 'modifier-entreprise':
        // Afficher le formulaire de modification d'entreprise
        // Récupérer l'ID de l'entreprise à modifier
        $id = $_GET['id'] ?? null;
        if ($id) {
            $controller = new EntrepriseController();
            $controller->edit($id);
        } else {
            // Gestion d'erreur si l'ID n'est pas fourni
            echo "ID de l'entreprise manquant.";
        }
        break;

    case 'supprimer-entreprise':
        // Supprimer une entreprise
        // Récupérer l'ID de l'entreprise à supprimer
        $id = $_GET['id'] ?? null;
        if ($id) {
            $controller = new EntrepriseController();
            $controller->delete($id);
        } else {
            // Gestion d'erreur si l'ID n'est pas fourni
            echo "ID de l'entreprise manquant.";
        }
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

    case 'login':
        // Assurez-vous que la méthode login() existe dans le bon contrôleur
        $controller = new HomeController();
        $controller->login();
        break;

    case 'contact':
        // Assurez-vous que la méthode contact() existe dans le bon contrôleur
        $controller = new HomeController();
        $controller->contact();
        break;

    default:
        // Page d'accueil par défaut
        $controller = new HomeController();
        $controller->home();
        break;
}
?>
