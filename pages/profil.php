<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Vérifier si l'utilisateur est connecté
requireLogin();

$errors = [];
$success = false;

// Récupérer les informations de l'utilisateur
try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT login FROM utilisateurs WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        session_destroy();
        redirect('/livre_or/pages/connexion.php');
    }
} catch (PDOException $e) {
    die("Erreur lors de la récupération du profil.");
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_login = cleanInput($_POST['login'] ?? '');
    $new_password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($new_login)) {
        $errors[] = "Le login est requis.";
    } elseif (strlen($new_login) < 3) {
        $errors[] = "Le login doit contenir au moins 3 caractères.";
    }

    // Vérifier si le nouveau login existe déjà (sauf si c'est le même)
    if ($new_login !== $user['login']) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE login = :login AND id != :id");
            $stmt->execute(['login' => $new_login, 'id' => $_SESSION['user_id']]);

            if ($stmt->fetch()) {
                $errors[] = "Ce login est déjà utilisé.";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la vérification du login.";
        }
    }

    // Validation du mot de passe (si fourni)
    if (!empty($new_password)) {
        if (strlen($new_password) < 6) {
            $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
        }

        if ($new_password !== $confirm_password) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }
    }

    // Mise à jour dans la base de données
    if (empty($errors)) {
        try {
            if (!empty($new_password)) {
                // Mise à jour avec nouveau mot de passe
                $hashedPassword = hashPassword($new_password);
                $stmt = $pdo->prepare("UPDATE utilisateurs SET login = :login, password = :password WHERE id = :id");
                $stmt->execute([
                    'login' => $new_login,
                    'password' => $hashedPassword,
                    'id' => $_SESSION['user_id']
                ]);
            } else {
                // Mise à jour sans modifier le mot de passe
                $stmt = $pdo->prepare("UPDATE utilisateurs SET login = :login WHERE id = :id");
                $stmt->execute([
                    'login' => $new_login,
                    'id' => $_SESSION['user_id']
                ]);
            }

            // Mettre à jour la session
            $_SESSION['user_login'] = $new_login;
            $user['login'] = $new_login;

            setFlashMessage('success', 'Profil mis à jour avec succès !');
            $success = true;
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la mise à jour du profil.";
        }
    }
}

$pageTitle = 'Mon Profil - Livre d\'Or';
include '../includes/header.php';
?>

<div class="form-container">
    <h1>Mon Profil</h1>

    <?php displayFlashMessage(); ?>

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
            <label for="login">Login</label>
            <input type="text" id="login" name="login" value="<?php echo htmlspecialchars($user['login']); ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Nouveau mot de passe</label>
            <input type="password" id="password" name="password">
            <small>Laissez vide si vous ne souhaitez pas changer votre mot de passe</small>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirmer le nouveau mot de passe</label>
            <input type="password" id="confirm_password" name="confirm_password">
        </div>

        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </form>

    <div class="profile-info">
        <h2>Mes statistiques</h2>
        <?php
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM commentaires WHERE id_utilisateur = :id");
            $stmt->execute(['id' => $_SESSION['user_id']]);
            $stats = $stmt->fetch();
        ?>
        <p>Nombre de commentaires postés : <strong><?php echo $stats['total']; ?></strong></p>
        <?php } catch (PDOException $e) { } ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
