<?php
session_start();
require 'config/db.php';

if (empty($_SESSION['panier']) || empty($_POST)) {
    header("Location: index.php");
    exit;
}

// Sécurité basique
$prenom = htmlspecialchars($_POST['prenom']);
$nom = htmlspecialchars($_POST['nom']);
$email = htmlspecialchars($_POST['email']);
$adresse = htmlspecialchars($_POST['adresse']);
$cp = htmlspecialchars($_POST['cp']);
$ville = htmlspecialchars($_POST['ville']);
$livraison = htmlspecialchars($_POST['livraison']);

$userId = $_SESSION['user']['id'] ?? null;

// Recalcul panier côté serveur
$total = 0;
$items = [];

foreach ($_SESSION['panier'] as $id => $qty) {
    $stmt = $db->prepare("SELECT * FROM jeux WHERE id = ?");
    $stmt->execute([$id]);
    $jeu = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($jeu) {
        $jeu['quantite'] = $qty;
        $jeu['total'] = $qty * $jeu['prix'];
        $items[] = $jeu;
        $total += $jeu['total'];
    }
}

// Sauvegarde commande
$stmt = $db->prepare("
    INSERT INTO commandes 
    (user_id, prenom, nom, email, adresse, cp, ville, livraison, total)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt->execute([
    $userId, $prenom, $nom, $email,
    $adresse, $cp, $ville, $livraison, $total
]);

$commandeId = $db->lastInsertId();

// Sauvegarde des jeux commandés
$stmtItem = $db->prepare("
    INSERT INTO commande_items 
    (commande_id, jeu_id, titre, prix, quantite)
    VALUES (?, ?, ?, ?, ?)
");

foreach ($items as $jeu) {
    $stmtItem->execute([
        $commandeId,
        $jeu['id'],
        $jeu['titre'],
        $jeu['prix'],
        $jeu['quantite']
    ]);
}

// Vider le panier
unset($_SESSION['panier']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Commande confirmée</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="payment-page">
    <div class="payment-card">

        <h1>✅ Paiement confirmé</h1>
        <p class="subtitle">Merci pour votre commande !</p>

        <p><strong>Commande n° :</strong> <?= $commandeId ?></p>
        <p><strong>Livraison :</strong> <?= $livraison ?></p>

        <ul class="payment-items">
            <?php foreach ($items as $jeu): ?>
                <li>
                    <span><?= htmlspecialchars($jeu['titre']) ?> x<?= $jeu['quantite'] ?></span>
                    <strong><?= number_format($jeu['total'], 2) ?> €</strong>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="payment-total">
            Total payé : <strong><?= number_format($total, 2) ?> €</strong>
        </div>

        <a href="index.php" class="btn-pay">Retour à la boutique</a>

    </div>
</div>

</body>
</html>