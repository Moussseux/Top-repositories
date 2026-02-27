<?php
session_start();
require 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pseudo = $_POST['pseudo'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $req = $db->prepare("INSERT INTO users (pseudo, email, password) VALUES (?, ?, ?)");
    $req->execute([$pseudo, $email, $password]);

    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Inscription</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="auth-container">
    <form class="auth-form" method="post">
        <h2>Inscription</h2>
        <input type="text" name="pseudo" placeholder="Pseudo" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit">S'inscrire</button>
        <p>Déjà inscrit ? <a href="login.php">Connexion</a></p>
    </form>
</div>

</body>
</html>