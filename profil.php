<?php
session_start();
require 'config/db.php';

// Redirection si pas connecté //
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user']['id'];

// Récupération infos utilisateur 
$stmt = $db->prepare("SELECT pseudo, email FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupération adresse utilisateur 
$stmt = $db->prepare("SELECT * FROM adresse WHERE id = ?");
$stmt->execute([$userId]);
$adresse = $stmt->fetch(PDO::FETCH_ASSOC);

// ** Récupération historique commandes avec statut et livreur **
$stmt = $db->prepare("
    SELECT c.*, l.nom AS livreur_nom 
    FROM commandes c
    LEFT JOIN livreurs l ON c.livreur_id = l.id
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$userId]);
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$message = "";

// Suppression adresse 
if (isset($_POST['delete_adresse'])) {
    $stmt = $db->prepare("DELETE FROM adresse WHERE id = ?");
    $stmt->execute([$userId]);
    $adresse = null;
    $message = "Adresse supprimée avec succès.";
}

// Traitement du formulaire profil 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profil'])) {
    $pseudo = trim($_POST['pseudo']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $passwordConfirm = $_POST['password_confirm'];

    if ($pseudo === "" || $email === "") {
        $message = "Pseudo et email sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Email invalide.";
    } elseif ($password !== $passwordConfirm) {
        $message = "Les mots de passe ne correspondent pas.";
    } else {

        // Mise à jour pseudo et email 
        $stmt = $db->prepare("UPDATE users SET pseudo = ?, email = ? WHERE id = ?");
        $stmt->execute([$pseudo, $email, $userId]);

        // Mise à jour mot de passe si rempli 
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $userId]);
        }

        $message = "Profil mis à jour avec succès !";

        // Mise à jour session 
        $_SESSION['user']['pseudo'] = $pseudo;
        $_SESSION['user']['email'] = $email;
        $user['pseudo'] = $pseudo;
        $user['email'] = $email;
    }
}

// Ajout / Modification adresse 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_adresse'])) {

    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $adresseInput = trim($_POST['adresse']);
    $code_postal = trim($_POST['code_postal']);
    $ville = trim($_POST['ville']);
    $pays = trim($_POST['pays']);

    if ($adresse) {
        // UPDATE
        $stmt = $db->prepare("UPDATE adresse 
            SET nom=?, prenom=?, adresse=?, code_postal=?, ville=?, pays=? 
            WHERE id=?");
        $stmt->execute([$nom, $prenom, $adresseInput, $code_postal, $ville, $pays, $userId]);
        $message = "Adresse mise à jour.";
    } else {
        // INSERT
        $stmt = $db->prepare("INSERT INTO adresse (id, nom, prenom, adresse, code_postal, ville, pays) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $nom, $prenom, $adresseInput, $code_postal, $ville, $pays]);
        $message = "Adresse ajoutée.";
    }

    // Recharger adresse
    $stmt = $db->prepare("SELECT * FROM adresse WHERE id = ?");
    $stmt->execute([$userId]);
    $adresse = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil</title>
    <link rel="stylesheet" href="css/style.css"> 
</head>
<body>
<div class="profile-page">

    <div class="back-home">
        <button onclick="window.location.href='index.php'">🏠 Retour à l'accueil</button>
    </div>

    <div class="container">
        <h2>Mon profil</h2>

        <?php if($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="info"><strong>Pseudo :</strong> <?= htmlspecialchars($user['pseudo']) ?></div>
        <div class="info"><strong>Email :</strong> <?= htmlspecialchars($user['email']) ?></div>

        <button id="btn-edit">Modifier mon profil</button>

        <form method="POST" id="edit-form" style="display:none;">
            <input type="hidden" name="save_profil" value="1">
            <label>Pseudo :</label>
            <input type="text" name="pseudo" value="<?= htmlspecialchars($user['pseudo']) ?>" required>

            <label>Email :</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

            <label>Nouveau mot de passe :</label>
            <input type="password" name="password" placeholder="Laissez vide pour ne pas changer">

            <label>Confirmer mot de passe :</label>
            <input type="password" name="password_confirm" placeholder="Confirmer le mot de passe">

            <button type="submit">Enregistrer les modifications</button>
        </form>

        <hr>
        <h2>Mon adresse</h2>

        <?php if ($adresse): ?>
            <div class="info"><strong>Nom :</strong> <?= htmlspecialchars($adresse['nom']) ?></div>
            <div class="info"><strong>Prénom :</strong> <?= htmlspecialchars($adresse['prenom']) ?></div>
            <div class="info"><strong>Adresse :</strong> <?= htmlspecialchars($adresse['adresse']) ?></div>
            <div class="info"><strong>Code postal :</strong> <?= htmlspecialchars($adresse['code_postal']) ?></div>
            <div class="info"><strong>Ville :</strong> <?= htmlspecialchars($adresse['ville']) ?></div>
            <div class="info"><strong>Pays :</strong> <?= htmlspecialchars($adresse['pays']) ?></div>
        <?php else: ?>
            <p>Aucune adresse enregistrée.</p>
        <?php endif; ?>

        <button id="btn-edit-adresse">Modifier / Ajouter adresse</button>

        <form method="POST" id="adresse-form" style="display:none;">
            <input type="hidden" name="save_adresse" value="1">
            <label>Nom :</label>
            <input type="text" name="nom" value="<?= htmlspecialchars($adresse['nom'] ?? '') ?>" required>

            <label>Prénom :</label>
            <input type="text" name="prenom" value="<?= htmlspecialchars($adresse['prenom'] ?? '') ?>" required>

            <label>Adresse :</label>
            <input type="text" name="adresse" value="<?= htmlspecialchars($adresse['adresse'] ?? '') ?>" required>

            <label>Code postal :</label>
            <input type="text" name="code_postal" value="<?= htmlspecialchars($adresse['code_postal'] ?? '') ?>" required>

            <label>Ville :</label>
            <input type="text" name="ville" value="<?= htmlspecialchars($adresse['ville'] ?? '') ?>" required>

            <label>Pays :</label>
            <input type="text" name="pays" value="<?= htmlspecialchars($adresse['pays'] ?? '') ?>" required>

            <button type="submit">Enregistrer</button>
        </form>

        <?php if ($adresse): ?>
        <form method="POST" onsubmit="return confirm('Supprimer l\'adresse ?');" style="margin-top:10px;">
            <button type="submit" name="delete_adresse">Supprimer l'adresse</button>
        </form>
        <?php endif; ?>

        <hr>
        <h2>Historique de mes commandes</h2>

        <?php if ($commandes): ?>
            <?php foreach ($commandes as $commande): ?>
                <div class="commande-box">
                    <div><strong>Commande #<?= $commande['id'] ?></strong></div>
                    <div>Date : <?= $commande['created_at'] ?></div>
                    <div>Nom : <?= htmlspecialchars($commande['prenom']) ?> <?= htmlspecialchars($commande['nom']) ?></div>
                    <div>Adresse : <?= htmlspecialchars($commande['adresse']) ?>, <?= htmlspecialchars($commande['cp']) ?> <?= htmlspecialchars($commande['ville']) ?></div>
                    <div>Livraison : <?= htmlspecialchars($commande['livraison']) ?></div>
                    <div>Statut : <strong><?= htmlspecialchars($commande['status']) ?></strong></div>
                    <div>Livreur : <?= htmlspecialchars($commande['livreur_nom'] ?? 'Non attribué') ?></div>
                    <div><strong>Total : <?= number_format($commande['total'], 2) ?> €</strong></div>
                </div>
                <hr>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucune commande passée.</p>
        <?php endif; ?>

    </div>
</div>

<script>
    const btnEdit = document.getElementById('btn-edit');
    const editForm = document.getElementById('edit-form');

    btnEdit.addEventListener('click', () => {
        const isHidden = editForm.style.display === 'none' || editForm.style.display === '';
        editForm.style.display = isHidden ? 'block' : 'none';
        btnEdit.textContent = isHidden ? 'Annuler' : 'Modifier mon profil';
    });

    const btnAdresse = document.getElementById('btn-edit-adresse');
    const adresseForm = document.getElementById('adresse-form');

    btnAdresse.addEventListener('click', () => {
        const isHidden = adresseForm.style.display === 'none' || adresseForm.style.display === '';
        adresseForm.style.display = isHidden ? 'block' : 'none';
        btnAdresse.textContent = isHidden ? 'Annuler' : 'Modifier / Ajouter adresse';
    });
</script>

</body>
</html>