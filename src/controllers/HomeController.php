<?php

require_once __DIR__ . '/../core/TemplateEngine.php';

class HomeController {
    public function home() {
        echo TemplateEngine::render('home.twig', ['current_page' => 'home']);
    }

    public function entreprises() {
        echo TemplateEngine::render('entreprises.twig', ['current_page' => 'entreprises']);
    }

    public function offres() {
        echo TemplateEngine::render('offres.twig', ['current_page' => 'offres']);
    }

    public function whishlist() {
        echo TemplateEngine::render('whishlist.twig', ['current_page' => 'whishlist']);
    }

    public function contact() {
        echo TemplateEngine::render('contact.twig', ['current_page' => 'contact']);
    }
}
