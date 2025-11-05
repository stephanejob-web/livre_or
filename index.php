<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livre d'Or - Accueil</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Bienvenue sur le Livre d'Or</h1>
        <p class="subtitle">Partagez votre expérience avec nous</p>

        <!-- Menu de navigation -->
        <div class="menu">
            <a href="index.php" class="btn">Accueil</a>
            <a href="livre-or.php" class="btn">Voir le livre d'or</a>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="commentaire.php" class="btn">Ajouter un commentaire</a>
                <a href="profil.php" class="btn">Mon profil</a>
                <a href="deconnexion.php" class="btn">Déconnexion</a>
            <?php else: ?>
                <a href="inscription.php" class="btn">S'inscrire</a>
                <a href="connexion.php" class="btn">Se connecter</a>
            <?php endif; ?>
        </div>

        <!-- Message de bienvenue si connecté -->
        <?php if(isset($_SESSION['user_login'])): ?>
            <div class="info">
                Bonjour <strong><?php echo htmlspecialchars($_SESSION['user_login']); ?></strong> !
            </div>
        <?php endif; ?>

        <!-- Cartes d'information -->
        <div class="cards">
            <div class="card">
                <h2>📖 Consulter</h2>
                <p>Découvrez les messages de nos visiteurs</p>
                <a href="livre-or.php" class="btn">Voir</a>
            </div>

            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="card">
                    <h2>✍️ Écrire</h2>
                    <p>Laissez votre message</p>
                    <a href="commentaire.php" class="btn">Écrire</a>
                </div>
            <?php else: ?>
                <div class="card">
                    <h2>🔐 Connexion</h2>
                    <p>Connectez-vous pour participer</p>
                    <a href="connexion.php" class="btn">Se connecter</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
