<?php

namespace Core;

use Controllers\HomeController;
use Controllers\EntrepriseController;

class Router {
    public function run() {
        $url = $_GET['url'] ?? 'home';

        switch ($url) {
            case 'home':
                (new HomeController())->home();
                break;
            case 'entreprises':
                (new EntrepriseController())->index();
                break;
            default:
                echo "404 - Page non trouvée";
        }
    }
}
