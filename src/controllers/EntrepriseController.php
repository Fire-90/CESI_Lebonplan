
<?php
/*
namespace Controllers;

use Core\Twig;
use Models\Entreprise;

class EntrepriseController {
    private $entrepriseModel;

    public function __construct() {
        $this->entrepriseModel = new Entreprise();
    }

    public function index() {
        $entreprises = $this->entrepriseModel->getEntreprises();

        $twig = Twig::getTwig();
        echo $twig->render('entreprises.twig', [
            'title' => 'Liste des Entreprises',
            'entreprises' => $entreprises
        ]);
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->entrepriseModel->addEntreprise($_POST['nom'], $_POST['secteur'], $_POST['ville']);
            header('Location: /entreprises');
            exit;
        }
    }

    public function delete() {
        if (isset($_POST['id'])) {
            $this->entrepriseModel->deleteEntreprise($_POST['id']);
            header('Location: /entreprises');
            exit;
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->entrepriseModel->updateEntreprise($_POST['id'], $_POST['nom'], $_POST['secteur'], $_POST['ville']);
            header('Location: /entreprises');
            exit;
        }
    }
}
*/
?>
