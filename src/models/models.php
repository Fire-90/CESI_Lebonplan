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

function authenticate($pdo, $email, $password) {
    $sql = "SELECT * FROM User WHERE EmailUser = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['PassWordUser'])) {
        return $user;
    }
    return false;
}

function searchCompany($pdo, $name) {
    $sql = "SELECT c.NameCompany, c.EmailCompany, COUNT(io.idOffer) AS NombreDeStagiaires, AVG(c.Evaluation) AS MoyenneEvaluations
            FROM Company c
            LEFT JOIN InternshipOffer io ON c.idCompany = io.idCompany
            WHERE c.NameCompany LIKE :name
            GROUP BY c.idCompany";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['name' => '%' . $name . '%']);
    return $stmt->fetchAll();
}

function createCompany($pdo, $name, $email, $password) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO Company (NameCompany, EmailCompany, PassWordCompany) VALUES (:name, :email, :password)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['name' => $name, 'email' => $email, 'password' => $hashed_password]);
}

function updateCompany($pdo, $id, $name, $email) {
    $sql = "UPDATE Company SET NameCompany = :name, EmailCompany = :email WHERE idCompany = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['name' => $name, 'email' => $email, 'id' => $id]);
}

function evaluateCompany($pdo, $id, $evaluation) {
    $sql = "UPDATE Company SET Evaluation = :evaluation WHERE idCompany = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['evaluation' => $evaluation, 'id' => $id]);
}

function deleteCompany($pdo, $id) {
    $sql = "DELETE FROM Company WHERE idCompany = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['id' => $id]);
}

function searchOffer($pdo, $title) {
    $sql = "SELECT io.NameOffer, io.DescOffer, io.RemunOffer, io.DateOffer, io.NumApplications, GROUP_CONCAT(c.NameCompetence) AS Competences, cmp.NameCompany
            FROM InternshipOffer io
            JOIN OfferCompetence oc ON io.idOffer = oc.idOffer
            JOIN Competence c ON oc.idCompetence = c.idCompetence
            JOIN Company cmp ON io.idCompany = cmp.idCompany
            WHERE io.NameOffer LIKE :title
            GROUP BY io.idOffer";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['title' => '%' . $title . '%']);
    return $stmt->fetchAll();
}

function createOffer($pdo, $title, $description, $remuneration, $date, $idCompany) {
    $sql = "INSERT INTO InternshipOffer (NameOffer, DescOffer, RemunOffer, DateOffer, idCompany) VALUES (:title, :description, :remuneration, :date, :idCompany)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['title' => $title, 'description' => $description, 'remuneration' => $remuneration, 'date' => $date, 'idCompany' => $idCompany]);
}

function updateOffer($pdo, $id, $title, $description, $remuneration, $date) {
    $sql = "UPDATE InternshipOffer SET NameOffer = :title, DescOffer = :description, RemunOffer = :remuneration, DateOffer = :date WHERE idOffer = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['title' => $title, 'description' => $description, 'remuneration' => $remuneration, 'date' => $date, 'id' => $id]);
}

function deleteOffer($pdo, $id) {
    $sql = "DELETE FROM InternshipOffer WHERE idOffer = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['id' => $id]);
}

function offerStatistics($pdo) {
    $sql = "SELECT c.NameCompetence, COUNT(io.idOffer) AS NombreOffres, AVG(io.NumApplications) AS MoyenneCandidatures
            FROM InternshipOffer io
            JOIN OfferCompetence oc ON io.idOffer = oc.idOffer
            JOIN Competence c ON oc.idCompetence = c.idCompetence
            GROUP BY c.idCompetence";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

function searchPilot($pdo, $firstName, $lastName) {
    $sql = "SELECT NamePilot, FNamePilot FROM Pilot WHERE NamePilot LIKE :lastName AND FNamePilot LIKE :firstName";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['lastName' => '%' . $lastName . '%', 'firstName' => '%' . $firstName . '%']);
    return $stmt->fetchAll();
}

function createPilot($pdo, $firstName, $lastName, $email, $password, $idUser) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO Pilot (NamePilot, FNamePilot, EmailPilot, PassWordPilot, idUser) VALUES (:lastName, :firstName, :email, :password, :idUser)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['lastName' => $lastName, 'firstName' => $firstName, 'email' => $email, 'password' => $hashed_password, 'idUser' => $idUser]);
}

function updatePilot($pdo, $id, $firstName, $lastName) {
    $sql = "UPDATE Pilot SET NamePilot = :lastName, FNamePilot = :firstName WHERE idPilot = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['lastName' => $lastName, 'firstName' => $firstName, 'id' => $id]);
}

function deletePilot($pdo, $id) {
    $sql = "DELETE FROM Pilot WHERE idPilot = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['id' => $id]);
}

function searchStudent($pdo, $firstName, $lastName, $email) {
    $sql = "SELECT NameUser, FNameUser, EmailUser FROM User WHERE NameUser LIKE :lastName AND FNameUser LIKE :firstName AND EmailUser LIKE :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['lastName' => '%' . $lastName . '%', 'firstName' => '%' . $firstName . '%', 'email' => '%' . $email . '%']);
    return $stmt->fetchAll();
}

function createStudent($pdo, $firstName, $lastName, $email, $password) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO User (NameUser, FNameUser, EmailUser, PassWordUser) VALUES (:lastName, :firstName, :email, :password)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['lastName' => $lastName, 'firstName' => $firstName, 'email' => $email, 'password' => $hashed_password]);
}

function updateStudent($pdo, $id, $firstName, $lastName, $email) {
    $sql = "UPDATE User SET NameUser = :lastName, FNameUser = :firstName, EmailUser = :email WHERE idUser = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['lastName' => $lastName, 'firstName' => $firstName, 'email' => $email, 'id' => $id]);
}

function deleteStudent($pdo, $id) {
    $sql = "DELETE FROM User WHERE idUser = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['id' => $id]);
}

function studentStatistics($pdo, $id) {
    $sql = "SELECT u.NameUser, u.FNameUser, u.EmailUser, COUNT(a.idApply) AS NombreCandidatures
            FROM User u
            LEFT JOIN Apply a ON u.idUser = a.idUser
            WHERE u.idUser = :id
            GROUP BY u.idUser";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    return $stmt->fetch();
}

function addToWishlist($pdo, $idUser, $idOffer) {
    $sql = "INSERT INTO WishList (idUser, idOffer) VALUES (:idUser, :idOffer)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['idUser' => $idUser, 'idOffer' => $idOffer]);
}

function removeFromWishlist($pdo, $idUser, $idOffer) {
    $sql = "DELETE FROM WishList WHERE idUser = :idUser AND idOffer = :idOffer";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['idUser' => $idUser, 'idOffer' => $idOffer]);
}

function viewWishlist($pdo, $idUser) {
    $sql = "SELECT io.*
            FROM InternshipOffer io
            JOIN WishList wl ON io.idOffer = wl.idOffer
            WHERE wl.idUser = :idUser";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['idUser' => $idUser]);
    return $stmt->fetchAll();
}

function applyForOffer($pdo, $date, $cv, $lettre, $idUser, $idOffer) {
    $sql = "INSERT INTO Apply (DateApply, ResumeApply, CoverLetter, idUser, idOffer) VALUES (:date, :cv, :lettre, :idUser, :idOffer)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute(['date' => $date, 'cv' => $cv, 'lettre' => $lettre, 'idUser' => $idUser, 'idOffer' => $idOffer]);
}

function viewApplications($pdo, $idUser) {
    $sql = "SELECT io.NameOffer, io.DescOffer, a.DateApply, a.CoverLetter
            FROM Apply a
            JOIN InternshipOffer io ON a.idOffer = io.idOffer
            WHERE a.idUser = :idUser";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['idUser' => $idUser]);
    return $stmt->fetchAll();
}
