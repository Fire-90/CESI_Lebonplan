<?php

namespace Controllers;

use Core\TemplateEngine;

class BaseController {
    protected $twig;
    protected $user;

    public function __construct() {
        $this->startSession();
        $this->twig = TemplateEngine::getTwig();
        $this->user = $this->getUserFromSession();
        $this->twig->addGlobal('user', $this->user);
    }

    protected function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    protected function getUserFromSession() {
        return $_SESSION['user'] ?? null;
    }

    protected function render(string $template, array $data = []) {
        $mergedData = array_merge(['user' => $this->user], $data);
        echo $this->twig->render($template, $mergedData);
    }
}