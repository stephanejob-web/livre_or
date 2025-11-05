<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est connecté
requireLogin();

$errors = [];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commentaire = cleanInput($_POST['commentaire'] ?? '');

    // Validation
    if (empty($commentaire)) {
        $errors[] = "Le commentaire ne peut pas être vide.";
    } elseif (strlen($commentaire) < 10) {
        $errors[] = "Le commentaire doit contenir au moins 10 caractères.";
    }

    // Insertion dans la base de données
    if (empty($errors)) {
        try {
            $pdo = getConnection();
            $stmt = $pdo->prepare("
                INSERT INTO commentaires (commentaire, id_utilisateur, date)
                VALUES (:commentaire, :id_utilisateur, NOW())
            ");
            $stmt->execute([
                'commentaire' => $commentaire,
                'id_utilisateur' => $_SESSION['user_id']
            ]);

            setFlashMessage('success', 'Votre commentaire a été ajouté avec succès !');
            redirect('/livre_or/pages/livre-or.php');
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de l'ajout du commentaire. Veuillez réessayer.";
        }
    }
}

$pageTitle = 'Ajouter un commentaire - Livre d\'Or';
include '../includes/header.php';
?>

<div class="form-container">
    <h1>✍️ Ajouter un commentaire</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="" class="form">
        <div class="form-group">
            <label for="commentaire">Votre commentaire</label>
            <textarea
                id="commentaire"
                name="commentaire"
                rows="8"
                placeholder="Partagez votre expérience, vos impressions..."
                required><?php echo isset($commentaire) ? htmlspecialchars($commentaire) : ''; ?></textarea>
            <small>Minimum 10 caractères</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Publier le commentaire</button>
            <a href="livre-or.php" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
