<?php

if (!isset($_SESSION['panier'])) $_SESSION['panier'] = [];
$panierCount = array_sum($_SESSION['panier']);
?>

<header class="site-header">
    <div class="logo"><a href="index.php">HeinzGamer</a></div>

    
    <form action="search.php" method="GET" class="search-form" autocomplete="off">
        <input type="text" id="search-input" name="q" placeholder="Rechercher un jeu...">
        <button type="submit">confirmer</button>
        <div id="search-suggestions"></div>
    </form>

    <nav class="main-nav">
        <a href="index.php">Accueil</a>
        <?php if (isset($_SESSION['user'])): ?>
            <a href="panier.php">Panier des Jeux 🛒 (<?= $panierCount ?>)</a>
            <a href="profil.php">Profil</a>
            <a href="logout.php">Déconnexion</a>
        <?php else: ?>
            <a href="login.php">Connexion</a>
            <a href="register.php">Inscription</a>
        <?php endif; ?>
    </nav>
</header>

<script src="js/search.js"></script>

<style>
.site-header { display: flex; align-items: center; justify-content: space-between; background: #111827; padding: 15px 40px; border-bottom: 2px solid #00bfff; }
.logo a { color: #00bfff; font-size: 24px; font-weight: bold; text-decoration: none; }
.main-nav a { margin-left: 15px; color: #00bfff; text-decoration: none; font-weight: bold; }
.search-form { position: relative; display: flex; align-items: center; }
.search-form input { width: 250px; padding: 6px 12px; border-radius: 8px 0 0 8px; border: 2px solid #00bfff; background: #111827; color: #e0e0e0; outline: none; }
.search-form button { padding: 6px 12px; border: 2px solid #00bfff; border-left: none; border-radius: 0 8px 8px 0; background: #00bfff; color: #020617; cursor: pointer; }
#search-suggestions { position: absolute; top: 38px; left: 0; width: 100%; background: #111827; border: 2px solid #00bfff; border-top: none; border-radius: 0 0 8px 8px; max-height: 200px; overflow-y: auto; display: none; z-index: 1000; }
#search-suggestions div { padding: 8px 12px; cursor: pointer; color: #e0e0e0; border-bottom: 1px solid #00bfff; }
#search-suggestions div:hover { background: #00bfff; color: #020617; }
</style>