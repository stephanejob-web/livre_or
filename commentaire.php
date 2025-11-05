<?php
require_once 'config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

$message = '';
$error = '';

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $commentaire = trim($_POST['commentaire']);

    if (empty($commentaire)) {
        $error = "Le commentaire ne peut pas être vide";
    } else {
        // Insérer le commentaire
        $stmt = $pdo->prepare("INSERT INTO commentaires (commentaire, id_utilisateur, date) VALUES (?, ?, NOW())");

        if ($stmt->execute([$commentaire, $_SESSION['user_id']])) {
            header("Location: livre-or.php");
            exit();
        } else {
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

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="commentaire">Votre commentaire :</label>
                <textarea id="commentaire" name="commentaire" required placeholder="Écrivez votre message ici..."></textarea>
            </div>

            <button type="submit" class="btn">Publier le commentaire</button>
        </form>

        <div class="links">
            <a href="livre-or.php">Voir le livre d'or</a> |
            <a href="index.php">Retour à l'accueil</a>
        </div>
    </div>
</body>
</html>
