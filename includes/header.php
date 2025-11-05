<?php
// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Livre d\'Or'; ?></title>
    <link rel="stylesheet" href="/livre_or/assets/css/style.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="container">
                <div class="logo">
                    <a href="/livre_or/index.php">📖 Livre d'Or</a>
                </div>
                <ul class="nav-menu">
                    <li><a href="/livre_or/index.php">Accueil</a></li>
                    <li><a href="/livre_or/pages/livre-or.php">Livre d'Or</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="/livre_or/pages/commentaire.php">Ajouter un commentaire</a></li>
                        <li><a href="/livre_or/pages/profil.php">Mon Profil</a></li>
                        <li><a href="/livre_or/pages/deconnexion.php">Déconnexion</a></li>
                        <li class="user-info">Connecté: <?php echo htmlspecialchars($_SESSION['user_login']); ?></li>
                    <?php else: ?>
                        <li><a href="/livre_or/pages/inscription.php">Inscription</a></li>
                        <li><a href="/livre_or/pages/connexion.php">Connexion</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>
    <main class="container">
