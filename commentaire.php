<?php
// ============================================
// PAGE D'AJOUT DE COMMENTAIRE
// ============================================
// Cette page permet à un utilisateur connecté d'ajouter un commentaire

// --------------------------------------------
// 1. INCLURE LA CONFIGURATION
// --------------------------------------------
require_once 'config.php';
require_once 'MqttPublisher.php';

// --------------------------------------------
// 2. VÉRIFIER SI L'UTILISATEUR EST CONNECTÉ
// --------------------------------------------
// ! = négation (contraire)
// !isset() = si la variable n'existe PAS
// Si l'utilisateur n'est pas connecté, on le redirige vers la connexion
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

// --------------------------------------------
// 3. INITIALISATION DES VARIABLES
// --------------------------------------------
$message = '';
$error = '';

// --------------------------------------------
// 4. TRAITEMENT DU FORMULAIRE
// --------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // --------------------------------------------
    // 4.1. RÉCUPÉRATION DU COMMENTAIRE
    // --------------------------------------------
    $commentaire = trim($_POST['commentaire']);

    // --------------------------------------------
    // 4.2. VÉRIFICATION
    // --------------------------------------------
    if (empty($commentaire)) {
        $error = "Le commentaire ne peut pas être vide";
    }
    else {

        // --------------------------------------------
        // 4.3. INSERTION DANS LA BASE DE DONNÉES
        // --------------------------------------------
        // INSERT INTO commentaires = ajouter dans la table commentaires
        // (commentaire, id_utilisateur, date) = les colonnes à remplir
        // VALUES (?, ?, NOW()) = les valeurs à insérer
        // ? = placeholders pour les valeurs sécurisées
        // NOW() = fonction SQL qui retourne la date et l'heure actuelles
        $stmt = $pdo->prepare("INSERT INTO commentaires (commentaire, id_utilisateur, date) VALUES (?, ?, NOW())");

        // --------------------------------------------
        // 4.4. EXÉCUTION DE LA REQUÊTE
        // --------------------------------------------
        // On passe le commentaire et l'ID de l'utilisateur connecté
        // $_SESSION['user_id'] contient l'ID de l'utilisateur
        if ($stmt->execute([$commentaire, $_SESSION['user_id']])) {

            // --------------------------------------------
            // 4.5. PUBLICATION MQTT DU MESSAGE
            // --------------------------------------------
            // Envoyer le message sur le broker MQTT
            $mqttPublisher = new MqttPublisher();
            $mqttPublisher->publishUserMessage(
                $_SESSION['user_id'],
                $_SESSION['user_login'],
                $commentaire
            );

            // --------------------------------------------
            // 4.6. REDIRECTION VERS LE LIVRE D'OR
            // --------------------------------------------
            // Si l'insertion a réussi, on redirige vers la page du livre d'or
            header("Location: livre-or.php");
            exit();
        }
        else {
            $error = "Erreur lors de l'ajout du commentaire";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un commentaire</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="centered">
    <div class="container comment-container">
        <h1>✍️ Ajouter un commentaire</h1>

        <!-- ========================================== -->
        <!-- AFFICHAGE DES ERREURS -->
        <!-- ========================================== -->
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- ========================================== -->
        <!-- FORMULAIRE D'AJOUT DE COMMENTAIRE -->
        <!-- ========================================== -->
        <form method="POST">
            <div class="form-group">
                <label for="commentaire">Votre commentaire :</label>

                <!-- textarea = zone de texte multiligne -->
                <!-- rows et cols définissent la taille, mais on utilise CSS à la place -->
                <!-- placeholder = texte d'exemple qui s'affiche quand le champ est vide -->
                <textarea id="commentaire" name="commentaire" required placeholder="Écrivez votre message ici..."></textarea>
            </div>

            <!-- Bouton pour publier -->
            <button type="submit" class="btn">Publier le commentaire</button>
        </form>

        <!-- ========================================== -->
        <!-- LIENS UTILES -->
        <!-- ========================================== -->
        <div class="links">
            <a href="livre-or.php">Voir le livre d'or</a> |
            <a href="index.php">Retour à l'accueil</a>
        </div>
    </div>
</body>
</html>
