<?php
// Configuration simple de la base de données
$host = 'localhost';
$dbname = 'livreor';
$username = 'root';
$password = '780662aB2';

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Démarrage de la session
session_start();
?>
