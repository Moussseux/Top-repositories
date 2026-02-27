<?php
session_start();
require 'config/db.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin'] = $admin['username'];
        header("Location: admin_dashboard.php");
        exit;
    } else {
        $message = "Identifiant ou mot de passe incorrect";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Connexion Admin</title>
<style>
body{display:flex;justify-content:center;align-items:center;height:100vh;background:#111827;color:#e0e0e0;font-family:sans-serif;}
form{background:#1e293b;padding:30px;border-radius:10px;width:300px;}
input{width:100%;padding:10px;margin:10px 0;border-radius:5px;border:1px solid #00bfff;background:#020617;color:#e0e0e0;}
button{width:100%;padding:10px;background:#00bfff;border:none;color:#020617;font-weight:bold;cursor:pointer;}
.message{color:#ff6b6b;text-align:center;}
</style>
</head>
<body>


<div class="auth-container">
    <form method="POST" class="auth-form">
        <h2>Connexion Admin</h2>

        <?php if (!empty($message)): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <input type="text" name="username" placeholder="Nom d'utilisateur" required>
        <input type="password" name="password" placeholder="Mot de passe" required>

        <button type="submit">Se connecter</button>
    </form>
</div>


</body>
</html>