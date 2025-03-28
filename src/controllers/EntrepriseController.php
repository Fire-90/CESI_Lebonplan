<?php

namespace Controllers;

require 'database.php';

use Core\database;

use Core\TemplateEngine;

class EntrepriseController {
    private $twig;
    private $conn;

    public function __construct() {
        // Initialiser Twig
        $this->twig = TemplateEngine::getTwig();

        // Connexion à la base de données
        global $conn;  // Utiliser la connexion définie dans config.php
        $this->conn = $conn;
    }

    // Afficher la liste des entreprises
    public function index() {
        global $pdo; // Utilisation de l'objet PDO
    
        // Récupérer les entreprises depuis la base de données
        $sql = "SELECT * FROM entreprises";
        $stmt = $pdo->query($sql);
        $entreprises = $stmt->fetchAll();
    
        // Rendu du template avec les données des entreprises
        echo $this->twig->render('entreprise.twig', [
            'entreprises' => $entreprises // Envoi des données vers la vue
        ]);
    }
    

    // Ajouter une entreprise
    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer les données du formulaire en utilisant filter_input pour plus de sécurité
            $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
            $secteur = filter_input(INPUT_POST, 'secteur', FILTER_SANITIZE_STRING);
            $ville = filter_input(INPUT_POST, 'ville', FILTER_SANITIZE_STRING);

            // Vérification que les champs ne sont pas vides
            if (!empty($nom) && !empty($secteur) && !empty($ville)) {
                // Préparer la requête SQL pour éviter les injections SQL
                $sql = "INSERT INTO entreprises (nom, secteur, ville) VALUES (?, ?, ?)";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("sss", $nom, $secteur, $ville);

                // Exécuter la requête
                if ($stmt->execute()) {
                    // Redirection après l'ajout de l'entreprise
                    header('Location: /entreprises');
                    exit;
                } else {
                    echo "Erreur lors de l'ajout de l'entreprise : " . $this->conn->error;
                }
                $stmt->close();
            } else {
                echo "Tous les champs doivent être remplis.";
            }
        }

        // Afficher le formulaire d'ajout
        echo $this->twig->render('ajout-entreprise.twig');
    }

    // Modifier une entreprise
    public function edit($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupérer les nouvelles données du formulaire
            $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
            $secteur = filter_input(INPUT_POST, 'secteur', FILTER_SANITIZE_STRING);
            $ville = filter_input(INPUT_POST, 'ville', FILTER_SANITIZE_STRING);

            // Vérification que les champs ne sont pas vides
            if (!empty($nom) && !empty($secteur) && !empty($ville)) {
                // Préparer la requête SQL pour éviter les injections SQL
                $sql = "UPDATE entreprises SET nom = ?, secteur = ?, ville = ? WHERE id = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("sssi", $nom, $secteur, $ville, $id);

                // Exécuter la requête
                if ($stmt->execute()) {
                    // Redirection après la modification de l'entreprise
                    header('Location: /entreprises');
                    exit;
                } else {
                    echo "Erreur lors de la modification de l'entreprise : " . $this->conn->error;
                }
                $stmt->close();
            } else {
                echo "Tous les champs doivent être remplis.";
            }
        }

        // Récupérer l'entreprise à modifier
        $sql = "SELECT * FROM entreprises WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $entreprise = $result->fetch_assoc();

        // Afficher le formulaire de modification
        echo $this->twig->render('modifier-entreprise.twig', [
            'entreprise' => $entreprise
        ]);
        $stmt->close();
    }

    // Supprimer une entreprise
    public function delete($id) {
        // Préparer la requête SQL pour supprimer l'entreprise
        $sql = "DELETE FROM entreprises WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);

        // Exécuter la requête
        if ($stmt->execute()) {
            // Redirection après la suppression de l'entreprise
            header('Location: /entreprises');
            exit;
        } else {
            echo "Erreur lors de la suppression de l'entreprise : " . $this->conn->error;
        }
        $stmt->close();
    }
}
