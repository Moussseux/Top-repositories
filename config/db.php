<?php
$host = "localhost";
$dbname = "heinzgamer";
$user = "root";
$pass = "";

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
} catch (PDOException $e) {
    die("Erreur DB : " . $e->getMessage());
}