<?php

namespace Controllers;

class HomeController extends BaseController {

    public function home() {
        $categories = ["Immobilier", "Véhicule", "Vêtements", "Multimédia", "Maison", "Loisir", "Service"];
        $articles = [
            ["titre" => "Maison 5 pièces 120m²", "localisation" => "Saint-Nazaire", "vendeur" => "Stéphane Plaza", "prix" => "305 000€"],
            ["titre" => "Volkswagen Polo 1.0 TSI 95ch", "localisation" => "Nanterre", "vendeur" => "Michael Schumacher", "prix" => "16 990€"],
            ["titre" => "Canapé 3 places IKEA", "localisation" => "Bordeaux", "vendeur" => "Ingvar Kamprad", "prix" => "149€"],
        ];

        $this->render('home.twig', [
            'categories' => $categories,
            'articles' => $articles
        ]);
    }

    public function entreprises() {
        $this->render('entreprise.twig');
    }

    public function offres() {
        $this->render('offres.twig');
    }

    public function whishlist() {
        $this->render('wishlist.twig');
    }

    public function contact() {
        $this->render('contact.twig');
    }

    public function login() {
        $this->render('login.twig');
    }

    public function postuler() {
        $this->render('postuler.twig');
    }

    public function legal() {
        $this->render('legal-notice.twig');
    }

    public function profile() {
        $this->render('profile.twig');
    }
}