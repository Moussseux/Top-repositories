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

// Livraison normale gratuite si ≥ 49€
$livraisonPrice = ($total >= 49) ? 0 : 5;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Paiement sécurisé</title>
<link rel="stylesheet" href="css/style.css">
<style>
.promo-livraison {
    background: #fff3cd;
    border: 1px solid #ffeeba;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 15px;
    font-size: 14px;
}
.promo-livraison.active {
    background: #d4edda;
    border: 1px solid #28a745;
    color: #155724;
    font-weight: bold;
}
</style>
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

<!-- Bloc promo livraison -->
<?php if ($total >= 49): ?>
<div class="promo-livraison active" id="promoLivraison">
🎉 <strong>Avantage activé !</strong><br>
✔ Livraison normale GRATUITE<br>
✔ Livraison rapide à seulement 5€
</div>
<?php else: ?>
<div class="promo-livraison" id="promoLivraison">
💡 Ajoutez encore <strong><?= number_format(49 - $total, 2) ?> €</strong> pour débloquer :<br>
✔ Livraison normale gratuite<br>
✔ Livraison rapide à 5€
</div>
<?php endif; ?>

<div class="payment-total">
Total produits : <strong><?= number_format($total, 2) ?> €</strong><br>
Livraison : <strong><span id="livraisonPrice"><?= number_format($livraisonPrice, 2) ?></span> €</strong><br>
<strong>Total à payer : <span id="totalFinal"><?= number_format($total + $livraisonPrice, 2) ?></span> €</strong>
</div>

<form action="success.php" method="post" class="payment-form">

<h2 style="margin-top:20px;">📦 Mode de livraison</h2>

<label>Type de livraison</label>
<select name="livraison" id="livraisonSelect" required>
<option value="">-- Choisir --</option>
<option value="normale">🚚 Livraison normale (5€ / GRATUITE dès 49€)</option>
<option value="rapide">⚡ Livraison rapide (10€ / 5€ dès 49€)</option>
<option value="click_collect">🏬 Click & Collect (gratuit)</option>
</select>

<div id="fournisseurBlock" style="display:none; margin-top:15px;">
<label>Fournisseur de livraison</label>
<select name="fournisseur">
<option value="DHL">DHL</option>
<option value="UPS">UPS</option>
<option value="La Poste">La Poste</option>
<option value="FedEx">FedEx</option>
</select>
</div>

<div id="magasinBlock" style="display:none; margin-top:15px;">
<label>Choisir un point de retrait</label>
<select name="magasin">
<option value="Paris - Châtelet">Paris - Châtelet</option>
<option value="Paris - La Défense">Paris - La Défense</option>
<option value="Lyon - Part Dieu">Lyon - Part Dieu</option>
<option value="Marseille - Vieux Port">Marseille - Vieux Port</option>
<option value="Bordeaux - Centre">Bordeaux - Centre</option>
<option value="Lille - Euralille">Lille - Euralille</option>
</select>
</div>

<h2 style="margin-top:20px;">📦 Informations de livraison</h2>

<div class="row">
<div>
<label>Prénom</label>
<input type="text" name="prenom" required value="<?= htmlspecialchars($adresse['prenom'] ?? '') ?>">
</div>
<div>
<label>Nom</label>
<input type="text" name="nom" required value="<?= htmlspecialchars($adresse['nom'] ?? '') ?>">
</div>
</div>

<label>Email</label>
<input type="email" name="email" required value="<?= htmlspecialchars($email) ?>">

<div id="adresseBlock">
<label>Adresse</label>
<input type="text" name="adresse" required value="<?= htmlspecialchars($adresse['adresse'] ?? '') ?>">

<div class="row">
<div>
<label>Code postal</label>
<input type="text" name="cp" required value="<?= htmlspecialchars($adresse['code_postal'] ?? '') ?>">
</div>
<div>
<label>Ville</label>
<input type="text" name="ville" required value="<?= htmlspecialchars($adresse['ville'] ?? '') ?>">
</div>
</div>

<label>Pays</label>
<input type="text" name="pays" required value="<?= htmlspecialchars($adresse['pays'] ?? '') ?>">
</div>

<h2 style="margin-top:25px;">💳 Informations bancaires</h2>

<label>Nom sur la carte</label>
<input type="text" required>

<label>Numéro de carte</label>
<input type="text" required>

<div class="row">
<div>
<label>Date d’expiration</label>
<input type="text" required>
</div>
<div>
<label>CVV</label>
<input type="password" required>
</div>
</div>

<button type="submit" class="btn-pay">Confirmer le paiement</button>
<a href="panier.php" class="btn-cancel">Annuler</a>

</form>
</div>
</div>

<script>
const livraisonSelect = document.getElementById("livraisonSelect");
const totalFinal = document.getElementById("totalFinal");
const livraisonPriceDisplay = document.getElementById("livraisonPrice");
const magasinBlock = document.getElementById("magasinBlock");
const fournisseurBlock = document.getElementById("fournisseurBlock");
const adresseBlock = document.getElementById("adresseBlock");
const promoBlock = document.getElementById("promoLivraison");

let totalProduits = <?= $total ?>;

function updatePromo() {
    if(totalProduits >= 49){
        promoBlock.innerHTML = "🎉 <strong>Avantage activé ! Plus de 49 euro d'achats</strong><br>✔ Livraison normale GRATUITE<br>✔ Livraison rapide à seulement 5€";
        promoBlock.classList.add("active");
    } else {
        promoBlock.innerHTML = "💡 Ajoutez encore <strong>"+(49 - totalProduits).toFixed(2)+" €</strong> pour débloquer :<br>✔ Livraison normale gratuite<br>✔ Livraison rapide à 5€";
        promoBlock.classList.remove("active");
    }
}

livraisonSelect.addEventListener("change", function() {
    let livraisonPrice = 5;

    if (this.value === "rapide") {
        fournisseurBlock.style.display = "block";
        magasinBlock.style.display = "none";
        adresseBlock.style.display = "block";
        livraisonPrice = (totalProduits >= 49) ? 5 : 10;
    } else if (this.value === "click_collect") {
        livraisonPrice = 0;
        fournisseurBlock.style.display = "none";
        magasinBlock.style.display = "block";
        adresseBlock.style.display = "none";
    } else if (this.value === "normale") {
        fournisseurBlock.style.display = "block";
        magasinBlock.style.display = "none";
        adresseBlock.style.display = "block";
        livraisonPrice = (totalProduits >= 49) ? 0 : 5;
    } else {
        fournisseurBlock.style.display = "none";
        magasinBlock.style.display = "none";
        adresseBlock.style.display = "block";
    }

    livraisonPriceDisplay.textContent = livraisonPrice.toFixed(2);
    totalFinal.textContent = (totalProduits + livraisonPrice).toFixed(2);
    updatePromo();
});

updatePromo();
</script>

</body>
</html>