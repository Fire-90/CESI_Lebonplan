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
    case 'ajouter-entreprise':  // Ajouter la route pour l'ajout d'une entreprise
        $controller = new EntrepriseController();
        $controller->add();
        break;
    case 'modifier-entreprise':  // Ajouter la route pour la modification d'une entreprise
        // Vous devez également récupérer l'ID de l'entreprise à modifier
        $id = $_GET['id'] ?? null;
        if ($id) {
            $controller = new EntrepriseController();
            $controller->edit($id);
        } else {
            // Gérer le cas où l'ID n'est pas fourni
            echo "ID de l'entreprise manquant.";
        }
        break;
    case 'supprimer-entreprise':  // Ajouter la route pour la suppression d'une entreprise
        // Vous devez également récupérer l'ID de l'entreprise à supprimer
        $id = $_GET['id'] ?? null;
        if ($id) {
            $controller = new EntrepriseController();
            $controller->delete($id);
        } else {
            // Gérer le cas où l'ID n'est pas fourni
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
