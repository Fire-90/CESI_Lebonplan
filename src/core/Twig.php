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
            // Assurez-vous que le chemin vers le dossier des vues est correct
            $loader = new FilesystemLoader(__DIR__ . '/../views');
            self::$twig = new Environment($loader, [
                'cache' => __DIR__ . '/../cache', // Dossier de cache (optionnel)
                'debug' => true,                   // Active le mode debug
            ]);
        }
        return self::$twig;
    }
}
?>