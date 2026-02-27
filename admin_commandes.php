<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

$message = "";

// Récupérer tous les livreurs
$livreurs = $db->query("SELECT id, nom FROM livreurs ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

// Mettre à jour une commande
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_commande'])) {
    $id = (int)$_POST['commande_id'];
    $status = $_POST['status'];
    $livreur_id = $_POST['livreur_id'] !== "" ? (int)$_POST['livreur_id'] : null;

    $stmt = $db->prepare("UPDATE commandes SET status = ?, livreur_id = ? WHERE id = ?");
    $stmt->execute([$status, $livreur_id, $id]);

    $message = "Commande mise à jour.";
}

// Récupérer commandes
$commandes = $db->query("
    SELECT c.*, l.nom AS livreur_nom 
    FROM commandes c
    LEFT JOIN livreurs l ON c.livreur_id = l.id
    ORDER BY c.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Gestion des commandes</title>
<style>
body { background:#0a0f1c; color:#e0e0e0; font-family:sans-serif; padding:20px; }
h1 { color:#00bfff; }
table { width:100%; border-collapse:collapse; margin-top:20px; }
th, td { padding:10px; border:1px solid #00bfff; text-align:left; }
th { background:#111827; }
form { margin:0; }
select, button { padding:5px; border-radius:5px; border:1px solid #00bfff; background:#020617; color:#e0e0e0; cursor:pointer; }
button { background:#00bfff; color:#020617; font-weight:bold; border:none; }
.message { color:#00ff99; margin-bottom:10px; }
a.back { color:#00bfff; text-decoration:none; display:block; margin-bottom:15px; }
</style>
</head>
<body>

<h1>Gestion des commandes</h1>
<a href="admin_dashboard.php" class="back">← Retour au Dashboard</a>

<?php if($message): ?>
    <div class="message"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<table>
<tr>
    <th>ID</th>
    <th>Client</th>
    <th>Adresse</th>
    <th>Livraison</th>
    <th>Total</th>
    <th>Date</th>
    <th>Statut</th>
    <th>Livreur</th>
    <th>Action</th>
</tr>

<?php foreach($commandes as $c): ?>
<tr>
    <td><?= $c['id'] ?></td>
    <td><?= htmlspecialchars($c['prenom'].' '.$c['nom']) ?><br><?= htmlspecialchars($c['email']) ?></td>
    <td><?= htmlspecialchars($c['adresse'].' '.$c['cp'].' '.$c['ville']) ?></td>
    <td><?= htmlspecialchars($c['livraison']) ?></td>
    <td><?= number_format($c['total'], 2) ?> €</td>
    <td><?= $c['created_at'] ?></td>

    <form method="POST">
        <input type="hidden" name="commande_id" value="<?= $c['id'] ?>">
        <td>
            <select name="status">
                <?php foreach(['En attente','En cours','Livrée','Annulée'] as $s): ?>
                    <option value="<?= $s ?>" <?= $c['status']==$s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td>
            <select name="livreur_id">
                <option value="">-- Aucun --</option>
                <?php foreach($livreurs as $l): ?>
                    <option value="<?= $l['id'] ?>" <?= $c['livreur_id']==$l['id'] ? 'selected' : '' ?>><?= htmlspecialchars($l['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td>
            <button type="submit" name="update_commande">Mettre à jour</button>
        </td>
    </form>
</tr>
<?php endforeach; ?>
</table>

</body>
</html>