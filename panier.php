<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user'])) {
    // Redirige vers login.php si non connecté
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}


// Supprimer un jeu du panier
if (isset($_GET['remove'])) {
    $id = (int) $_GET['remove'];
    if ($id > 0 && isset($_SESSION['panier'][$id])) {
        unset($_SESSION['panier'][$id]);
    }
    header("Location: panier.php");
    exit;
}


// Ajouter un jeu au panier
if (isset($_GET['add'])) {
    $id = (int) $_GET['add'];
    if ($id > 0) {
        if (isset($_SESSION['panier'][$id])) {
            $_SESSION['panier'][$id] += 1;
        } else {
            $_SESSION['panier'][$id] = 1;
        }
    }
    header("Location: panier.php");
    exit;
}


// Récupération des jeux dans le panier
$games = [];
$total = 0;

foreach ($_SESSION['panier'] as $id => $qty) {
    $req = $db->prepare("SELECT * FROM jeux WHERE id = ?");
    $req->execute([$id]);
    $game = $req->fetch(PDO::FETCH_ASSOC);

    if ($game) { // Vérifie que le jeu existe
        $game['quantity'] = $qty;
        $game['total'] = $qty * $game['prix'];
        $games[] = $game;
        $total += $game['total'];
    } else {
        // Supprime les ID invalides du panier
        unset($_SESSION['panier'][$id]);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Panier 🛒</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'header.php'; ?>

<div class="cart-container">
    <h1>🛒 Ton Panier de Jeux</h1>

    <?php if (empty($games)): ?>
        <p class="empty-cart">Il n'y a rien dans ton panier !</p>
        <a href="index.php" class="btn">Retour à la boutique</a>
    <?php else: ?>
        <?php foreach ($games as $game): ?>
            <div class="cart-item">
                <img src="images/<?= htmlspecialchars($game['image']) ?>" alt="<?= htmlspecialchars($game['titre']) ?>">

                <div class="cart-info">
                    <h2><?= htmlspecialchars($game['titre']) ?></h2>
                    <p><?= htmlspecialchars($game['description']) ?></p>
                    <p>Quantité : <?= $game['quantity'] ?></p>
                </div>

                <div class="cart-price">
                    <span><?= number_format($game['total'], 2) ?> €</span>
                    <a href="panier.php?remove=<?= $game['id'] ?>" class="remove">✖</a>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="cart-total">
            Total : <strong><?= number_format($total, 2) ?> €</strong>
        </div>

        <div class="cart-actions">
            <a href="index.php" class="btn secondary">Continuer les achats</a>
            <a href="paiement.php" class="btn">Passer au paiement</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>