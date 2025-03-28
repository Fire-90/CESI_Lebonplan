<?php

function login($pdo, $email, $password) {
    if (!empty($email) && !empty($password)) {
        // Requête préparée pour éviter les injections SQL
        $sql = "SELECT * FROM User WHERE EmailUser = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            // Comparaison des mots de passe
            if (password_verify($password, $user['PassWordUser'])) { // Utilisation correcte de password_verify()
                echo "<p style='color: green;'>Connexion réussie ! Bienvenue, " . htmlspecialchars($user['NameUser']) . ".</p>";
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


function register($pdo, $email, $password, $first_name, $last_name) {
    if (!empty($email) && !empty($password) && !empty($first_name) && !empty($last_name)) {
        // Vérifier si l'email existe déjà
        $sql_check = "SELECT COUNT(*) FROM User WHERE EmailUser = :email";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute(['email' => $email]);

        if ($stmt_check->fetchColumn() > 0) {
            echo "<p style='color: red;'>Cet email est déjà enregistré.</p>";
        } else {
            // Hacher le mot de passe avant de l'enregistrer
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Requête préparée pour insérer un nouvel utilisateur
            $sql_insert = "INSERT INTO User (EmailUser, PassWordUser, NameUser, FNameUser) VALUES (:email, :password, :first_name, :last_name)";
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->execute([
                'email' => $email,
                'password' => $hashed_password,
                'first_name' => $first_name,
                'last_name' => $last_name
            ]);

            echo "<p style='color: green;'>Inscription réussie ! Vous pouvez maintenant vous connecter.</p>";
        }
    } else {
        echo "<p style='color: red;'>Veuillez remplir tous les champs.</p>";
    }
}

