<?php
session_start();
require 'config/db.php';

$search = trim($_GET['q'] ?? '');
if ($search === '') { echo json_encode([]); exit; }

$searchWildcard = "%$search%";
$stmt = $db->prepare("SELECT id, titre FROM jeux WHERE LOWER(titre) LIKE LOWER(:search) LIMIT 10");
$stmt->bindParam(':search', $searchWildcard, PDO::PARAM_STR);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($results);