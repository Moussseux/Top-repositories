<?php
session_start();
require 'config/db.php';

// Initialisation du panier 
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Ajouter un jeu au panier 
if (isset($_GET['add'])) {
    $id = (int) $_GET['add'];
    if (isset($_SESSION['panier'][$id])) {
        $_SESSION['panier'][$id] += 1;
    } else {
        $_SESSION['panier'][$id] = 1;
    }
    header("Location: index.php");
    exit;
}

// Récupération des jeux pour affichage 
$req = $db->query("SELECT * FROM jeux");
$games = $req->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>HeinzGamer</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'header.php'; ?>

<div class="shop-container">
    <h1>Nos Jeux</h1>
    <div class="games-grid">
        <?php foreach ($games as $game): ?>
            <div class="game-card">
                <img src="images/<?= htmlspecialchars($game['image']) ?>" alt="<?= htmlspecialchars($game['titre']) ?>">
                <h2><?= htmlspecialchars($game['titre']) ?></h2>
                <p><?= htmlspecialchars($game['description']) ?></p>
                <p><strong><?= number_format($game['prix'], 2) ?> €</strong></p>
                <a href="index.php?add=<?= $game['id'] ?>" class="btn">Ajouter au panier</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>
