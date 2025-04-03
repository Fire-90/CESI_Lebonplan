<?php

namespace Controllers;

class HomeController extends BaseController {

    public function home() {
        $this->render('home.twig');
    }

    public function entreprises() {
        $this->render('entreprise.twig');
    }

    public function offres() {
        $this->render('offres.twig');
    }

    public function whishlist() {
        $this->render('wishlist.twig');
    }

    public function contact() {
        $this->render('contact.twig');
    }

    public function login() {
        $this->render('login.twig');
    }

    public function postuler() {
        $this->render('postuler.twig');
    }

    public function legal() {
        $this->render('legal-notice.twig');
    }

    public function cookie() {
        $this->render('cookie.twig');
    }

    public function profile() {
        $this->render('profile.twig');
    }
}