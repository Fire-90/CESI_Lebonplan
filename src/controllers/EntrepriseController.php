<?php

namespace Controllers;

use Core\Twig;

class EntrepriseController
{
    public function index()
    {
        $entreprises = [
            ['nom' => 'TechCorp', 'secteur' => 'Technologie', 'ville' => 'Paris'],
            ['nom' => 'FinSoft', 'secteur' => 'Finance', 'ville' => 'Londres'],
            ['nom' => 'MediHealth', 'secteur' => 'Santé', 'ville' => 'Berlin']
        ];

        $twig = Twig::getTwig();
        echo $twig->render('entreprises.twig', [
            'title' => 'Entreprises',
            'entreprises' => $entreprises
        ]);
    }
}
