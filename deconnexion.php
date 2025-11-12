<?php
// ============================================
// PAGE DE DÉCONNEXION
// ============================================
// Cette page déconnecte l'utilisateur et le redirige vers l'accueil

// --------------------------------------------
// 1. INCLURE LA CONFIGURATION
// --------------------------------------------
require_once 'config.php';

// --------------------------------------------
// 2. VIDER TOUTES LES VARIABLES DE SESSION
// --------------------------------------------
// $_SESSION = array(); transforme $_SESSION en tableau vide
// Cela supprime toutes les données de la session (user_id, user_login, etc.)
$_SESSION = array();

// --------------------------------------------
// 3. DÉTRUIRE LA SESSION
// --------------------------------------------
// session_destroy() détruit complètement la session côté serveur
// La session n'existe plus du tout
session_destroy();

// --------------------------------------------
// 4. REDIRIGER VERS L'ACCUEIL
// --------------------------------------------
// On redirige l'utilisateur vers la page d'accueil
header("Location: index.php");
exit();
?>
