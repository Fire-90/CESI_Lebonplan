<?php

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class BaseController {
    protected $twig;

    public function __construct() {
        $loader = new FilesystemLoader(__DIR__ . '/../views');
        $this->twig = new Environment($loader);
    }
}
