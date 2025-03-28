<?php

namespace Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class TemplateEngine {
    public static function getTwig() {
        $loader = new FilesystemLoader(__DIR__ . '/../views');
        $twig = new Environment($loader);

        // Ajouter la fonction "path"
        $twig->addFunction(new TwigFunction('path', function ($route) {
            return "/public/index.php?page=" . $route;
        }));

        return $twig;
    }
}

