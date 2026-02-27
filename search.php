<?php
session_start();
require 'config/db.php';

// Initialiser le panier si besoin
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Récupérer la recherche
$search = $_GET['q'] ?? '';
$searchTerm = strtolower(trim($search));
$searchWildcard = "%$searchTerm%";

// Requête sécurisée pour chercher les jeux par titre ou mots-clés
$stmt = $db->prepare("
    SELECT * FROM jeux 
    WHERE LOWER(titre) LIKE :search
       OR LOWER(keywords) LIKE :search
");
$stmt->bindParam(':search', $searchWildcard, PDO::PARAM_STR);
$stmt->execute();

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Compteur du panier pour le header
$panierCount = array_sum($_SESSION['panier'] ?? []);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultats pour "<?= htmlspecialchars($search) ?>"</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'header.php'; ?>

<div class="search-results">
    <h1>Résultats pour : "<?= htmlspecialchars($search) ?>"</h1>

    <?php if (empty($results)): ?>
        <p>Aucun jeu trouvé </p>
    <?php else: ?>
        <div class="games-grid">
            <?php foreach ($results as $game): ?>
                <div class="game-card">
                    <img src="images/<?= htmlspecialchars($game['image']) ?>" alt="<?= htmlspecialchars($game['titre']) ?>">
                    <h3><?= htmlspecialchars($game['titre']) ?></h3>
                    <p><?= htmlspecialchars($game['description']) ?></p>
                    <p><?= number_format($game['prix'], 2) ?> €</p>

                    <?php if (isset($_SESSION['user'])): ?>
                        <a href="index.php?add=<?= $game['id'] ?>" class="btn">Ajouter au panier</a>
                    <?php else: ?>
                        <a href="login.php" class="btn">Connectez-vous pour acheter</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>