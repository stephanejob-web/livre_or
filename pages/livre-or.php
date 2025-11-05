<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

$pageTitle = 'Livre d\'Or - Tous les commentaires';
include '../includes/header.php';

// Récupérer tous les commentaires avec les informations de l'utilisateur
try {
    $pdo = getConnection();
    $stmt = $pdo->query("
        SELECT c.id, c.commentaire, c.date, u.login
        FROM commentaires c
        INNER JOIN utilisateurs u ON c.id_utilisateur = u.id
        ORDER BY c.date DESC
    ");
    $commentaires = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erreur lors de la récupération des commentaires.");
}
?>

<div class="livre-or-container">
    <h1>📖 Livre d'Or</h1>

    <?php displayFlashMessage(); ?>

    <?php if (isLoggedIn()): ?>
        <div class="add-comment-section">
            <a href="commentaire.php" class="btn btn-primary">✍️ Ajouter un commentaire</a>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <p>Vous devez être <a href="connexion.php">connecté</a> pour laisser un commentaire.</p>
        </div>
    <?php endif; ?>

    <div class="comments-section">
        <?php if (empty($commentaires)): ?>
            <div class="no-comments">
                <p>Aucun commentaire pour le moment. Soyez le premier à laisser votre avis !</p>
            </div>
        <?php else: ?>
            <h2>Tous les commentaires (<?php echo count($commentaires); ?>)</h2>

            <div class="comments-list">
                <?php foreach ($commentaires as $comment): ?>
                    <div class="comment-card">
                        <div class="comment-header">
                            <span class="comment-author">👤 <?php echo htmlspecialchars($comment['login']); ?></span>
                            <span class="comment-date">📅 Posté le <?php echo formatDate($comment['date']); ?></span>
                        </div>
                        <div class="comment-body">
                            <p><?php echo nl2br(htmlspecialchars($comment['commentaire'])); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
