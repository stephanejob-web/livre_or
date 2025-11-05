<?php
/**
 * Fonctions utilitaires pour le projet Livre d'Or
 */

/**
 * Vérifie si l'utilisateur est connecté
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Redirige vers une page
 * @param string $url URL de redirection
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Vérifie si l'utilisateur est connecté, sinon redirige vers la connexion
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('/livre_or/pages/connexion.php');
    }
}

/**
 * Nettoie et sécurise les données utilisateur
 * @param string $data Données à nettoyer
 * @return string Données nettoyées
 */
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Valide un email
 * @param string $email Email à valider
 * @return bool
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Hash un mot de passe
 * @param string $password Mot de passe à hasher
 * @return string Mot de passe hashé
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Vérifie un mot de passe contre un hash
 * @param string $password Mot de passe en clair
 * @param string $hash Hash du mot de passe
 * @return bool
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Formate une date au format français
 * @param string $date Date au format MySQL
 * @return string Date formatée
 */
function formatDate($date) {
    $timestamp = strtotime($date);
    return date('d/m/Y', $timestamp);
}

/**
 * Affiche un message flash
 * @param string $type Type de message (success, error, info)
 * @param string $message Message à afficher
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Récupère et supprime le message flash
 * @return array|null Message flash ou null
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

/**
 * Affiche le message flash s'il existe
 */
function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        echo '<div class="alert alert-' . $flash['type'] . '">';
        echo htmlspecialchars($flash['message']);
        echo '</div>';
    }
}
?>
