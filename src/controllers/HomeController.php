<?php

namespace Controllers;

use Core\TemplateEngine;

class HomeController {
    public function home() {
        $twig = TemplateEngine::getTwig();
        
        // Données simulées (en pratique, elles viendraient d'une base de données)
        $categories = ["Immobilier", "Véhicule", "Vêtements", "Multimédia", "Maison", "Loisir", "Service"];
        $articles = [
            ["titre" => "Maison 5 pièces 120m²", "localisation" => "Saint-Nazaire", "vendeur" => "Stéphane Plaza", "prix" => "305 000€"],
            ["titre" => "Volkswagen Polo 1.0 TSI 95ch", "localisation" => "Nanterre", "vendeur" => "Michael Schumacher", "prix" => "16 990€"],
            ["titre" => "Canapé 3 places IKEA", "localisation" => "Bordeaux", "vendeur" => "Ingvar Kamprad", "prix" => "149€"],
        ];

        echo $twig->render('home.twig', [
            'categories' => $categories,
            'articles' => $articles
        ]);
    }
}
