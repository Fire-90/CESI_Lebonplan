<?php

namespace Controllers;

use Models\Entreprise;
use Core\TemplateEngine;

class EntrepriseController {
    private $twig;
    private $entrepriseModel;

    public function __construct() {
        // Initialiser Twig
        $this->twig = TemplateEngine::getTwig();
        // Initialiser le modèle Entreprise
        $this->entrepriseModel = new Entreprise();
    }

    // Afficher la liste des entreprises
    public function index() {
        // Récupérer toutes les entreprises depuis la base de données
        $entreprises = $this->entrepriseModel->getEntreprises();

        // Passer les données à la vue
        echo $this->twig->render('entreprise.twig', [
            'entreprises' => $entreprises
        ]);
    }

    // Ajouter une entreprise
    public function add() {
        // Vérifier si la requête est de type POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer les données du formulaire
            $nom = $_POST['nom'];
            $secteur = $_POST['secteur'];
            $ville = $_POST['ville'];

            // Ajouter l'entreprise dans la base de données
            $this->entrepriseModel->addEntreprise($nom, $secteur, $ville);

            // Rediriger vers la page d'accueil des entreprises
            header('Location: /entreprises');
            exit;
        }

        // Si ce n'est pas une requête POST, afficher le formulaire d'ajout
        echo $this->twig->render('ajout-entreprise.twig');
    }

    // Modifier une entreprise
    public function edit($id) {
        // Vérifier si la requête est de type POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer les données du formulaire
            $nom = $_POST['nom'];
            $secteur = $_POST['secteur'];
            $ville = $_POST['ville'];

            // Mettre à jour l'entreprise dans la base de données
            $this->entrepriseModel->updateEntreprise($id, $nom, $secteur, $ville);

            // Rediriger vers la page des entreprises
            header('Location: /entreprises');
            exit;
        }

        // Si ce n'est pas une requête POST, afficher le formulaire de modification
        $entreprise = $this->entrepriseModel->getEntrepriseById($id);
        echo $this->twig->render('modifier-entreprise.twig', [
            'entreprise' => $entreprise
        ]);
    }

    // Supprimer une entreprise
    public function delete($id) {
        // Supprimer l'entreprise de la base de données
        $this->entrepriseModel->deleteEntreprise($id);

        // Rediriger vers la page des entreprises
        header('Location: /entreprises');
        exit;
    }
}
?>
