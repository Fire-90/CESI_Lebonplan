<?php

namespace Controllers;

use Core\database;
use PDO;

class WishlistController extends BaseController {
    private $pdo;

    public function __construct() {
        parent::__construct();
        $this->pdo = Database::getConnection();
    }

    public function index() {
        if (!$this->user) {
            header('Location: ?page=login');
            exit;
        }

        try {
            $stmt = $this->pdo->prepare("
                SELECT o.* 
                FROM Offer o
                JOIN WishList w ON o.idOffer = w.idOffer
                WHERE w.idUser = :userId
            ");
            $stmt->execute([':userId' => $this->user['id']]);
            $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->render('wishlist.twig', [
                'offers' => $offers
            ]);

        } catch (PDOException $e) {
            $this->render('wishlist.twig', [
                'error' => 'Erreur lors du chargement des favoris'
            ]);
        }
    }
}