<?php

namespace Controllers;

use Models\Entreprise;
use Core\Database;
use Core\TemplateEngine;
use PDO;
use PDOException;

class EntrepriseController {
    private $twig;
    private $pdo;

    public function __construct() {
        // Initialiser Twig
        $this->twig = TemplateEngine::getTwig();

        // Récupérer la connexion à la base de données
        $this->pdo = Database::getConnection();
    }
    
    public function index() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM Company");
            $entreprises = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            echo $this->twig->render('entreprise.twig', [
                'entreprises' => $entreprises
            ]);
        } catch (PDOException $e) {
            echo "Erreur lors de la récupération des entreprises : " . $e->getMessage();
        }
    }
    
    
    // Ajouter une entreprise
    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer et sécuriser les données du formulaire
            $nom = filter_input(INPUT_POST, 'NameCompany', FILTER_SANITIZE_STRING);
            $secteur = filter_input(INPUT_POST, 'Sector', FILTER_SANITIZE_STRING);
            $ville = filter_input(INPUT_POST, 'City', FILTER_SANITIZE_STRING);

            // Vérifier que tous les champs sont remplis
            if (!empty($nom) && !empty($secteur) && !empty($ville)) {
                try {
                    // Préparer la requête SQL avec des paramètres sécurisés
                    $stmt = $this->pdo->prepare("INSERT INTO entreprises (NameCompany, Sector, City) VALUES (:nom, :secteur, :ville)");
                    
                    // Exécuter la requête
                    $stmt->execute([
                        ':nom' => $nom,
                        ':secteur' => $secteur,
                        ':ville' => $ville
                    ]);

                    // Redirection après ajout
                    header('Location: /entreprises');
                    exit;
                } catch (PDOException $e) {
                    echo "Erreur lors de l'ajout de l'entreprise : " . $e->getMessage();
                }
            } else {
                echo "Tous les champs doivent être remplis.";
            }
        }

        // Afficher le formulaire d'ajout
        echo $this->twig->render('ajout-entreprise.twig');
    }
}
