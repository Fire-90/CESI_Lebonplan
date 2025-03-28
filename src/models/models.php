<?php

function login($pdo, $email, $password) {
    if (!empty($email) && !empty($password)) {
        // Requête préparée pour éviter les injections SQL
        $sql = "SELECT * FROM Users WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            // Comparaison des mots de passe
            if (password_verify($password, $user['password'])) { // Utilisation correcte de password_verify()
                echo "<p style='color: green;'>Connexion réussie ! Bienvenue, " . htmlspecialchars($user['first_name']) . ".</p>";
            } else {
                echo "<p style='color: red;'>Email ou mot de passe incorrect.</p>";
            }
        } else {
            echo "<p style='color: red;'>Email ou mot de passe incorrect.</p>";
        }
    } else {
        echo "<p style='color: red;'>Veuillez remplir tous les champs.</p>";
    }
}