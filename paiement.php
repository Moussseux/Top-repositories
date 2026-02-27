<?php
session_start();
require 'config/db.php';

if (empty($_SESSION['panier'])) {
    header('Location: panier.php');
    exit;
}

$userId = $_SESSION['user']['id'] ?? null;

// Récupération adresse utilisateur si connecté 
$adresse = null;
$email = $_SESSION['user']['email'] ?? '';
if ($userId) {

    $stmt = $db->prepare("SELECT * FROM adresse WHERE id = ?");
    $stmt->execute([$userId]);
    $adresse = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Préparation panier 
$games = [];
$total = 0;
foreach ($_SESSION['panier'] as $id => $qty) {
    $req = $db->prepare("SELECT * FROM jeux WHERE id = ?");
    $req->execute([$id]);
    $game = $req->fetch(PDO::FETCH_ASSOC);

    if ($game) {
        $game['quantity'] = $qty;
        $game['total'] = $qty * $game['prix'];
        $games[] = $game;
        $total += $game['total'];
    }
}

// Calcul frais livraison
$livraisonPrice = 5.00; // frais de base
if ($total >= 49) {
    $livraisonPrice = 0;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiement sécurisé</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="payment-page">
    <div class="payment-card">

        <h1>💳 Paiement sécurisé</h1>
        <p class="subtitle">Simulation – aucun débit réel</p>

        <ul class="payment-items">
            <?php foreach ($games as $game): ?>
                <li>
                    <span><?= htmlspecialchars($game['titre']) ?> x<?= $game['quantity'] ?></span>
                    <strong><?= number_format($game['total'], 2) ?> €</strong>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="payment-total">
            Total produits : <strong><?= number_format($total, 2) ?> €</strong><br>
            Frais de livraison : <strong><?= number_format($livraisonPrice, 2) ?> €</strong>
            <?php if ($livraisonPrice == 0): ?>
                <br><small>Livraison gratuite pour toute commande supérieure ou égale à 49 €</small>
            <?php endif; ?>
            <br>
            <strong>Total à payer : <?= number_format($total + $livraisonPrice, 2) ?> €</strong>
        </div>

        <form action="success.php" method="post" class="payment-form">

            
            <h2 style="margin-top:20px;">📦 Informations de livraison</h2>

            <div class="row">
                <div>
                    <label>Prénom</label>
                    <input type="text" name="prenom" placeholder="Jean" required
                        value="<?= htmlspecialchars($adresse['prenom'] ?? '') ?>">
                </div>
                <div>
                    <label>Nom</label>
                    <input type="text" name="nom" placeholder="Dupont" required
                        value="<?= htmlspecialchars($adresse['nom'] ?? '') ?>">
                </div>
            </div>

            <label>Email</label>
            <input type="email" name="email" placeholder="jean.dupont@email.com" required
                value="<?= htmlspecialchars($email) ?>">

            <label>Adresse</label>
            <input type="text" name="adresse" placeholder="12 rue de Paris" required
                value="<?= htmlspecialchars($adresse['adresse'] ?? '') ?>">

            <div class="row">
                <div>
                    <label>Code postal</label>
                    <input type="text" name="cp" placeholder="75000" required
                        value="<?= htmlspecialchars($adresse['code_postal'] ?? '') ?>">
                </div>
                <div>
                    <label>Ville</label>
                    <input type="text" name="ville" placeholder="Paris" required
                        value="<?= htmlspecialchars($adresse['ville'] ?? '') ?>">
                </div>
            </div>

            <label>Pays</label>
            <input type="text" name="pays" placeholder="France" required
                value="<?= htmlspecialchars($adresse['pays'] ?? '') ?>">

            <label>Fournisseur de livraison</label>
            <select name="livraison" required>
                <option value="">-- Choisir un fournisseur --</option>
                <option value="DHL">DHL</option>
                <option value="UPS">UPS</option>
                <option value="La Poste">La Poste</option>
                <option value="FedEx">FedEx</option>
            </select>

           
            <h2 style="margin-top:25px;">💳 Informations bancaires</h2>

            <label>Nom sur la carte</label>
            <input type="text" placeholder="Jean Dupont" required>

            <label>Numéro de carte</label>
            <input type="text" placeholder="4242 4242 4242 4242" required>

            <div class="row">
                <div>
                    <label>Date d’expiration</label>
                    <input type="text" placeholder="MM/AA" required>
                </div>
                <div>
                    <label>CVV</label>
                    <input type="password" placeholder="123" required>
                </div>
            </div>

            <button type="submit" class="btn-pay">Confirmer le paiement</button>
            <a href="panier.php" class="btn-cancel">Annuler</a>

        </form>

    </div>
</div>

</body>
</html>