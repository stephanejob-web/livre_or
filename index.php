<?php
// ============================================
// PAGE D'ACCUEIL
// ============================================
// Cette page affiche l'accueil du livre d'or

// --------------------------------------------
// 1. INCLURE LE FICHIER DE CONFIGURATION
// --------------------------------------------
// require_once = inclure un fichier PHP une seule fois
// Cela permet d'avoir accès à $pdo (connexion BDD) et $_SESSION (session utilisateur)
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livre d'Or - Accueil</title>
    <!-- Lien vers le fichier CSS pour le style -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <!-- Titre principal de la page -->
        <h1>Bienvenue sur le Livre d'Or</h1>
        <p class="subtitle">Partagez votre expérience avec nous</p>

        <!-- ========================================== -->
        <!-- MENU DE NAVIGATION -->
        <!-- ========================================== -->
        <div class="menu">
            <a href="index.php" class="btn">Accueil</a>
            <a href="livre-or.php" class="btn">Voir le livre d'or</a>

            <?php
            // --------------------------------------------
            // AFFICHAGE CONDITIONNEL DU MENU
            // --------------------------------------------
            // isset() = vérifie si une variable existe
            // On vérifie si $_SESSION['user_id'] existe pour savoir si l'utilisateur est connecté

            if(isset($_SESSION['user_id'])):
                // SI L'UTILISATEUR EST CONNECTÉ
                // On affiche les liens pour les utilisateurs connectés
            ?>
                <a href="commentaire.php" class="btn">Ajouter un commentaire</a>
                <a href="profil.php" class="btn">Mon profil</a>
                <a href="deconnexion.php" class="btn">Déconnexion</a>

            <?php else:
                // SINON (si l'utilisateur n'est PAS connecté)
                // On affiche les liens pour s'inscrire ou se connecter
            ?>
                <a href="inscription.php" class="btn">S'inscrire</a>
                <a href="connexion.php" class="btn">Se connecter</a>

            <?php endif; ?>
        </div>

        <!-- ========================================== -->
        <!-- MESSAGE DE BIENVENUE (si connecté) -->
        <!-- ========================================== -->
        <?php if(isset($_SESSION['user_login'])): ?>
            <div class="info">
                Bonjour <strong>
                    <?php
                    // htmlspecialchars() = protège contre les attaques XSS
                    // Cela transforme les caractères spéciaux en code HTML sûr
                    echo htmlspecialchars($_SESSION['user_login']);
                    ?>
                </strong> !
            </div>
        <?php endif; ?>

        <!-- ========================================== -->
        <!-- CARTES D'INFORMATION -->
        <!-- ========================================== -->
        <div class="cards">
            <!-- Carte 1 : Consulter le livre d'or (visible par tous) -->
            <div class="card">
                <h2>📖 Consulter</h2>
                <p>Découvrez les messages de nos visiteurs</p>
                <a href="livre-or.php" class="btn">Voir</a>
            </div>

            <?php if(isset($_SESSION['user_id'])):
                // Si l'utilisateur est connecté, on affiche la carte pour écrire
            ?>
                <!-- Carte 2 : Écrire un message (uniquement si connecté) -->
                <div class="card">
                    <h2>✍️ Écrire</h2>
                    <p>Laissez votre message</p>
                    <a href="commentaire.php" class="btn">Écrire</a>
                </div>

            <?php else:
                // Sinon, on affiche la carte pour se connecter
            ?>
                <!-- Carte 2 : Se connecter (uniquement si non connecté) -->
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
