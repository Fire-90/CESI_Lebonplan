<?php

namespace Controllers;

use Core\TemplateEngine;

class BaseController {
    protected $twig;
    protected $user;
    protected $permLVL;

    public function __construct() {
        $this->startSession();
        $this->twig = TemplateEngine::getTwig();
        $this->user = $this->getUserFromSession();
        $this->permLVL = $this->getPermLVLFromSession();
        
        // Ajout des variables globales pour Twig
        $this->twig->addGlobal('user', $this->user);
        $this->twig->addGlobal('permLVL', $this->permLVL);
    }

    protected function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    protected function getUserFromSession() {
        return $_SESSION['user'] ?? null;
    }

    protected function getPermLVLFromSession() {
        return $_SESSION['permLVL'] ?? 0; // 0 = guest par défaut
    }

    protected function render(string $template, array $data = []) {
        // Les variables user et permLVL sont déjà disponibles globalement
        echo $this->twig->render($template, $data);
    }
}