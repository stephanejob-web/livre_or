<?php

require_once 'config.php';
require_once 'MqttPublisher.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}


$message = '';
$error = '';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {


    $commentaire = trim($_POST['commentaire']);


    if (empty($commentaire)) {
        $error = "Le commentaire ne peut pas être vide";
    }
    else {


        $stmt = $pdo->prepare("INSERT INTO commentaires (commentaire, id_utilisateur, date) VALUES (?, ?, NOW())");


        if ($stmt->execute([$commentaire, $_SESSION['user_id']])) {


            $mqttPublisher = new MqttPublisher();
            $mqttPublisher->publishUserMessage(
                $_SESSION['user_id'],
                $_SESSION['user_login'],
                $commentaire
            );


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
