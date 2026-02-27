<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

// Supprimer un jeu
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $db->prepare("DELETE FROM jeux WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin_dashboard.php");
    exit;
}

// Ajouter un jeu
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_game'])) {
    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);
    $prix = floatval($_POST['prix']);

    if (empty($titre) || empty($description) || empty($_FILES['image']['name'])) {
        $message = "Tous les champs sont obligatoires";
    } else {
        $imageName = time().'_'.$_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "images/$imageName");

        $stmt = $db->prepare("INSERT INTO jeux (titre, description, prix, image) VALUES (?, ?, ?, ?)");
        $stmt->execute([$titre, $description, $prix, $imageName]);
        $message = "Jeu ajouté avec succès !";
    }
}

// Mettre à jour un jeu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_game'])) {
    $id = (int) $_POST['id'];
    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);
    $prix = floatval($_POST['prix']);
    $imageName = null;

    if (!empty($_FILES['image']['name'])) {
        $imageName = time().'_'.$_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "images/$imageName");
    }

    if ($imageName) {
        $stmt = $db->prepare("UPDATE jeux SET titre = ?, description = ?, prix = ?, image = ? WHERE id = ?");
        $stmt->execute([$titre, $description, $prix, $imageName, $id]);
    } else {
        $stmt = $db->prepare("UPDATE jeux SET titre = ?, description = ?, prix = ? WHERE id = ?");
        $stmt->execute([$titre, $description, $prix, $id]);
    }

    $message = "Jeu mis à jour avec succès !";
    header("Location: admin_dashboard.php");
    exit;
}

// Récupération des jeux
$games = $db->query("SELECT * FROM jeux ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Si on veut modifier un jeu
$editGame = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM jeux WHERE id = ?");
    $stmt->execute([$id]);
    $editGame = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Dashboard Admin</title>
<style>
body{background:#0a0f1c;color:#e0e0e0;font-family:sans-serif;padding:20px;}
h1{color:#00bfff;}
table{width:100%;border-collapse:collapse;margin-top:20px;}
th,td{padding:10px;border:1px solid #00bfff;text-align:left;}
th{background:#111827;}
img{width:60px;}
form{margin-top:30px;background:#111827;padding:20px;border-radius:10px;border:2px solid #00bfff;width:400px;}
input,textarea{width:100%;padding:10px;margin:10px 0;border-radius:5px;border:1px solid #00bfff;background:#020617;color:#e0e0e0;}
button{padding:10px;border:none;border-radius:5px;background:#00bfff;color:#020617;font-weight:bold;cursor:pointer;}
.message{color:#00ff99;}
.logout{float:right;margin-top:-50px;}
a.delete{color:#ff4d4d;text-decoration:none;}
a.admin-link{display:inline-block;margin:10px 0;color:#00ff99;font-weight:bold;}
</style>
</head>
<body>
<h1>Dashboard Admin</h1>
<a href="admin_logout.php" class="logout">Se déconnecter</a>

<?php if($message): ?><div class="message"><?= htmlspecialchars($message) ?></div><?php endif; ?>

<h2>Accès rapide</h2>
<a href="admin_livreurs.php" class="admin-link">🚚 Gestion des livreurs</a><br>
<a href="admin_commandes.php" class="admin-link">📝 Gestion des commandes</a><br>

<?php if($editGame): ?>
<h2>Modifier le jeu</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $editGame['id'] ?>">
    <input type="text" name="titre" placeholder="Titre du jeu" value="<?= htmlspecialchars($editGame['titre']) ?>" required>
    <textarea name="description" placeholder="Description" required><?= htmlspecialchars($editGame['description']) ?></textarea>
    <input type="number" step="0.01" name="prix" placeholder="Prix (€)" value="<?= $editGame['prix'] ?>" required>
    <p>Image actuelle :</p>
    <img src="images/<?= htmlspecialchars($editGame['image']) ?>" width="100" alt="<?= htmlspecialchars($editGame['titre']) ?>"><br>
    <input type="file" name="image" accept="image/*">
    <button type="submit" name="update_game">Mettre à jour le jeu</button>
</form>
<?php else: ?>
<h2>Ajouter un nouveau jeu</h2>
<form method="POST" enctype="multipart/form-data">
<input type="text" name="titre" placeholder="Titre du jeu" required>
<textarea name="description" placeholder="Description" required></textarea>
<input type="number" step="0.01" name="prix" placeholder="Prix (€)" required>
<input type="file" name="image" accept="image/*" required>
<button type="submit" name="add_game">Ajouter le jeu</button>
</form>
<?php endif; ?>

<h2>Liste des jeux</h2>
<table>
<tr>
<th>ID</th><th>Image</th><th>Titre</th><th>Description</th><th>Prix</th><th>Action</th>
</tr>
<?php foreach($games as $game): ?>
<tr>
<td><?= $game['id'] ?></td>
<td><img src="images/<?= htmlspecialchars($game['image']) ?>" alt="<?= htmlspecialchars($game['titre']) ?>"></td>
<td><?= htmlspecialchars($game['titre']) ?></td>
<td><?= htmlspecialchars($game['description']) ?></td>
<td><?= number_format($game['prix'], 2) ?> €</td>
<td>
    <a href="admin_dashboard.php?delete=<?= $game['id'] ?>" class="delete">Supprimer</a> | 
    <a href="admin_dashboard.php?edit=<?= $game['id'] ?>" class="admin-link">Modifier</a>
</td>
</tr>
<?php endforeach; ?>
</table>
</body>
</html>