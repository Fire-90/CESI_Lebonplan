<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TemplateEngine {
    private static $twig = null;

    public static function getTwig() {
        if (self::$twig === null) {
            $loader = new FilesystemLoader(__DIR__ . '/../views'); // Emplacement des templates
            self::$twig = new Environment($loader, [
                'cache' => __DIR__ . '/../../cache', // Dossier de cache Twig
                'debug' => true, // Active le mode debug
            ]);
        }
        return self::$twig;
    }

    public static function render($template, $data = []) {
        return self::getTwig()->render($template, $data);
    }
}
