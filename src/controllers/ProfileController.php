<?php

namespace Models;

use PDO;
use Core\Database;

class ProfileController extends BaseController{
    private $pdo;

    public function __construct() {
        parent::__construct(); // Appel du constructeur parent
        $this->pdo = Database::getConnection();
    }



}