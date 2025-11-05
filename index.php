<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$pageTitle = 'Accueil - Livre d\'Or';
include 'includes/header.php';
?>

<div class="hero">
    <h1>Bienvenue sur notre Livre d'Or</h1>
    <p class="subtitle">Partagez votre expérience avec nous et découvrez les avis de notre communauté</p>
</div>

<div class="content-section">
    <?php displayFlashMessage(); ?>

    <div class="cards">
        <div class="card">
            <h2>📖 Consulter le Livre d'Or</h2>
            <p>Découvrez les messages et témoignages laissés par nos visiteurs.</p>
            <a href="pages/livre-or.php" class="btn btn-primary">Voir les commentaires</a>
        </div>

        <?php if (isLoggedIn()): ?>
            <div class="card">
                <h2>✍️ Laisser un message</h2>
                <p>Partagez votre expérience en laissant un commentaire.</p>
                <a href="pages/commentaire.php" class="btn btn-primary">Ajouter un commentaire</a>
            </div>

            <div class="card">
                <h2>👤 Mon Profil</h2>
                <p>Gérez vos informations personnelles et votre compte.</p>
                <a href="pages/profil.php" class="btn btn-primary">Accéder au profil</a>
            </div>
        <?php else: ?>
            <div class="card">
                <h2>🔐 Se connecter</h2>
                <p>Connectez-vous pour laisser un message dans notre livre d'or.</p>
                <a href="pages/connexion.php" class="btn btn-primary">Se connecter</a>
            </div>

            <div class="card">
                <h2>📝 S'inscrire</h2>
                <p>Créez un compte pour pouvoir laisser vos commentaires.</p>
                <a href="pages/inscription.php" class="btn btn-primary">S'inscrire</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="info-section">
    <h2>À propos de notre Livre d'Or</h2>
    <p>Notre livre d'or vous permet de partager vos impressions, témoignages et expériences. Rejoignez notre communauté et faites entendre votre voix !</p>

    <div class="features">
        <div class="feature">
            <h3>Simple</h3>
            <p>Interface intuitive et facile à utiliser</p>
        </div>
        <div class="feature">
            <h3>Sécurisé</h3>
            <p>Vos données sont protégées</p>
        </div>
        <div class="feature">
            <h3>Communautaire</h3>
            <p>Partagez avec d'autres utilisateurs</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
