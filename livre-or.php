<?php
require_once 'config.php';

// Récupérer tous les commentaires (du plus récent au plus ancien)
$stmt = $pdo->query("
    SELECT c.commentaire, c.date, u.login
    FROM commentaires c
    JOIN utilisateurs u ON c.id_utilisateur = u.id
    ORDER BY c.date DESC
");
$commentaires = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livre d'Or</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container livre-container">
        <h1>📖 Livre d'Or</h1>

        <div class="menu">
            <a href="index.php" class="btn">Accueil</a>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="commentaire.php" class="btn">Ajouter un commentaire</a>
                <a href="deconnexion.php" class="btn">Déconnexion</a>
            <?php else: ?>
                <a href="connexion.php" class="btn">Se connecter pour commenter</a>
            <?php endif; ?>
        </div>

        <?php if (count($commentaires) > 0): ?>
            <?php foreach($commentaires as $comm): ?>
                <div class="commentaire">
                    <div class="commentaire-header">
                        Posté le <strong><?php echo date('d/m/Y', strtotime($comm['date'])); ?></strong>
                        par <strong><?php echo htmlspecialchars($comm['login']); ?></strong>
                    </div>
                    <div class="commentaire-text">
                        <?php echo nl2br(htmlspecialchars($comm['commentaire'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-comments">
                Aucun commentaire pour le moment. Soyez le premier à laisser un message !
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
