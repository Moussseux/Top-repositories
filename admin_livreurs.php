<?php
session_start();
require 'config/db.php';
if (!isset($_SESSION['admin'])) header("Location: admin_login.php");

$message="";

// Ajouter un livreur
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_livreur'])){
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    if($nom && filter_var($email,FILTER_VALIDATE_EMAIL)){
        $stmt=$db->prepare("INSERT INTO livreurs (nom,email,telephone) VALUES (?,?,?)");
        $stmt->execute([$nom,$email,$telephone]);
        $message="Livreur ajouté.";
    } else $message="Nom et email valides requis.";
}

// Supprimer un livreur
if(isset($_GET['delete'])){
    $stmt=$db->prepare("DELETE FROM livreurs WHERE id=?");
    $stmt->execute([(int)$_GET['delete']]);
    header("Location: admin_livreurs.php");
    exit;
}

// Récupérer tous les livreurs
$livreurs = $db->query("SELECT * FROM livreurs ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Gestion des livreurs</title>
<style>
body{background:#0a0f1c;color:#e0e0e0;font-family:sans-serif;padding:20px;}
h1{color:#00bfff;}
table{width:100%;border-collapse:collapse;margin-top:20px;}
th,td{padding:10px;border:1px solid #00bfff;text-align:left;}
th{background:#111827;}
form{margin-top:30px;background:#111827;padding:20px;border-radius:10px;border:2px solid #00bfff;width:400px;}
input{width:100%;padding:10px;margin:10px 0;border-radius:5px;border:1px solid #00bfff;background:#020617;color:#e0e0e0;}
button{padding:10px;border:none;border-radius:5px;background:#00bfff;color:#020617;font-weight:bold;cursor:pointer;}
.message{color:#00ff99;}
.logout{float:right;margin-top:-50px;}
a.delete{color:#ff4d4d;text-decoration:none;}
a.back{color:#00bfff;text-decoration:none;display:block;margin-bottom:15px;}
</style>
</head>
<body>
<h1>Gestion des livreurs</h1>
<a href="admin_dashboard.php" class="back">← Retour au Dashboard</a>

<?php if($message): ?><div class="message"><?= htmlspecialchars($message) ?></div><?php endif; ?>

<form method="POST">
<input type="text" name="nom" placeholder="Nom" required>
<input type="email" name="email" placeholder="Email" required>
<input type="text" name="telephone" placeholder="Téléphone">
<button type="submit" name="add_livreur">Ajouter le livreur</button>
</form>

<table>
<tr><th>ID</th><th>Nom</th><th>Email</th><th>Téléphone</th><th>Action</th></tr>
<?php foreach($livreurs as $l): ?>
<tr>
<td><?= $l['id'] ?></td>
<td><?= htmlspecialchars($l['nom']) ?></td>
<td><?= htmlspecialchars($l['email']) ?></td>
<td><?= htmlspecialchars($l['telephone']) ?></td>
<td><a href="?delete=<?= $l['id'] ?>" class="delete" onclick="return confirm('Supprimer ?');">Supprimer</a></td>
</tr>
<?php endforeach; ?>
</table>
</body>
</html>