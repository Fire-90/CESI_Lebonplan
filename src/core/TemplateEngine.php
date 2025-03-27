<?php

namespace Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TemplateEngine {
    private static $twig = null;

    public static function getTwig() {
        if (self::$twig === null) {
            $loader = new FilesystemLoader(__DIR__ . '/../views');
            self::$twig = new Environment($loader, [
                'cache' => __DIR__ . '/../../cache', // Cache des templates (peut être désactivé en dev)
                'debug' => true
            ]);
        }
        return self::$twig;
    }
}
