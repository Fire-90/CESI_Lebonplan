<?php

namespace Controllers;

use Core\TemplateEngine;

class HomeController {
    private $twig;

    public function __construct() {
        $this->twig = TemplateEngine::getTwig();
    }

    public function home() {
        // Données simulées (en pratique, elles viendraient d'une base de données)
        $categories = ["Immobilier", "Véhicule", "Vêtements", "Multimédia", "Maison", "Loisir", "Service"];
        $articles = [
            ["titre" => "Maison 5 pièces 120m²", "localisation" => "Saint-Nazaire", "vendeur" => "Stéphane Plaza", "prix" => "305 000€"],
            ["titre" => "Volkswagen Polo 1.0 TSI 95ch", "localisation" => "Nanterre", "vendeur" => "Michael Schumacher", "prix" => "16 990€"],
            ["titre" => "Canapé 3 places IKEA", "localisation" => "Bordeaux", "vendeur" => "Ingvar Kamprad", "prix" => "149€"],
        ];

        echo $this->twig->render('home.twig', [
            'categories' => $categories,
            'articles' => $articles
        ]);
    }

    public function entreprises() {
        // Logique pour afficher les entreprises
        echo $this->twig->render('entreprise.twig');
    }

    public function offres() {
        // Logique pour afficher les offres
        echo $this->twig->render('offres.twig');
    }

    public function whishlist() {
        // Logique pour afficher la whishlist
        echo $this->twig->render('wishlist.twig');
    }

    public function contact() {
        // Logique pour afficher la page de contact
        echo $this->twig->render('contact.twig');
    }
    public function postuler()
{
    echo $this->twig->render('postuler.twig', []);
}

}
?>
