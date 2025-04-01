<?php

namespace Controllers;

use Core\TemplateEngine;

class BaseController {
    protected $twig;
    protected $user;

    public function __construct() {
        $this->twig = TemplateEngine::getTwig();
        $this->user = $this->getUserFromCookie();
        $this->twig->addGlobal('user', $this->user); // Ajout global
    }

    protected function getUserFromCookie() {
        if (isset($_COOKIE['user'])) {
            return json_decode($_COOKIE['user'], true);
        }
        return null;
    }

    protected function render(string $template, array $data = []) {
        // Fusionne les données spécifiques avec les données globales
        $mergedData = array_merge(['user' => $this->user], $data);
        echo $this->twig->render($template, $mergedData);
    }
}