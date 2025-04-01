    <?php

    require_once __DIR__ . '/../vendor/autoload.php';

    use Controllers\HomeController;
    use Controllers\EntrepriseController;
    use Controllers\AccountController;

    // Récupérer l'utilisateur depuis le cookie (si existant)
    $user = isset($_COOKIE['user']) ? json_decode($_COOKIE['user'], true) : null;

    // Définir la pagination
    $pagination = isset($_GET['pagination']) ? (int) $_GET['pagination'] : 1;
    $pagination = max(1, $pagination);

    // Récupérer la page demandée
    $page = $_GET['page'] ?? 'home';

    switch ($page) {
        case 'entreprises':
            $controller = new EntrepriseController();
            $controller->index($pagination); 
            break;
        case 'ajout-entreprise':
            $controller = new EntrepriseController();
            $controller->add(); 
            break;
        case 'edit-entreprise':
            $controller = new EntrepriseController();
            if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                $controller->edit((int) $_GET['id']);
            } else {
                header('Location: index.php?page=entreprises'); // Redirection si pas d'ID valide
                exit;
            }
            break;
        case 'delete-entreprise':
            $controller = new EntrepriseController();
            $id = $_GET['id'] ?? null;
            if ($id) {
                $controller->delete();
            } else {
                header('Location: index.php?page=entreprises'); // Redirection si pas d'ID
                exit;
            }
            break;    
        case 'offres':
            $controller = new HomeController();
            $controller->offres(); 
            break;
        case 'whishlist':
            $controller = new HomeController();
            $controller->whishlist(); 
            break;
        case 'contact':
            $controller = new HomeController();
            $controller->contact(); 
            break;
        case 'postuler':
            $controller = new HomeController();
            $controller->postuler(); 
            break;
        case 'login':
            $controller = new AccountController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->login();
            } else {
                echo $controller->login();
            }
            break;
        case 'signup':
            $controller = new AccountController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->signup();
            } else {
                echo $controller->signup();
            }
            break;
        case 'logout':
            $controller = new AccountController();
            $controller->logout();
            break;
        case 'legal-notice':    
            $controller = new HomeController();
            $controller->legal(); 
            break;
        default:
            $controller = new HomeController();
            $controller->home(); 
            break;
    }

    ?>