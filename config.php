<?php
// ============================================
// FICHIER DE CONFIGURATION
// ============================================
// Ce fichier contient la connexion à la base de données
// et le démarrage de la session
// Il doit être inclus au début de chaque page avec : require_once 'config.php';

// --------------------------------------------
// 1. PARAMÈTRES DE CONNEXION À LA BASE DE DONNÉES
// --------------------------------------------
$host = 'localhost';        // Adresse du serveur MySQL (localhost = sur le même ordinateur)
$dbname = 'livreor';        // Nom de la base de données
$username = 'root';         // Nom d'utilisateur MySQL
$password = '780662aB2';    // Mot de passe MySQL

// --------------------------------------------
// 2. CONNEXION À LA BASE DE DONNÉES AVEC PDO
// --------------------------------------------
// PDO (PHP Data Objects) est une extension PHP pour se connecter à une base de données
// C'est plus sécurisé que mysqli car il protège contre les injections SQL

try {
    // Tentative de connexion
    // "mysql:" indique qu'on utilise MySQL
    // "host=$host" indique l'adresse du serveur
    // "dbname=$dbname" indique le nom de la base
    // "charset=utf8" permet d'utiliser les accents correctement
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    // Configuration pour afficher les erreurs SQL
    // PDO::ERRMODE_EXCEPTION = si une erreur SQL arrive, on aura un message d'erreur
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch(PDOException $e) {
    // Si la connexion échoue, on arrête le script et on affiche l'erreur
    // die() = arrête l'exécution du script
    die("Erreur de connexion : " . $e->getMessage());
}

// --------------------------------------------
// 3. DÉMARRAGE DE LA SESSION
// --------------------------------------------
// Les sessions permettent de garder des informations sur l'utilisateur
// entre les différentes pages (par exemple : savoir si l'utilisateur est connecté)
// $_SESSION est un tableau qui stocke les données de la session
session_start();
?>

