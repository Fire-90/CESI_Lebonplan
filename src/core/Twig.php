<?php

namespace Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Twig
{
    private static $twig = null;

    public static function getTwig()
    {
        if (self::$twig === null) {
            $loader = new FilesystemLoader(__DIR__ . '/../views');
            self::$twig = new Environment($loader, [
                'cache' => false, // Mettre un dossier de cache en prod
                'debug' => true,  // Active le mode debug
            ]);
        }
        return self::$twig;
    }
}
