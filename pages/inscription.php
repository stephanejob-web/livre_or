<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Si déjà connecté, rediriger vers l'accueil
if (isLoggedIn()) {
    redirect('/livre_or/index.php');
}

$errors = [];
$success = false;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = cleanInput($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($login)) {
        $errors[] = "Le login est requis.";
    } elseif (strlen($login) < 3) {
        $errors[] = "Le login doit contenir au moins 3 caractères.";
    }

    if (empty($password)) {
        $errors[] = "Le mot de passe est requis.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }

    // Vérifier si le login existe déjà
    if (empty($errors)) {
        try {
            $pdo = getConnection();
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE login = :login");
            $stmt->execute(['login' => $login]);

            if ($stmt->fetch()) {
                $errors[] = "Ce login est déjà utilisé.";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la vérification du login.";
        }
    }

    // Insertion dans la base de données
    if (empty($errors)) {
        try {
            $pdo = getConnection();
            $hashedPassword = hashPassword($password);

            $stmt = $pdo->prepare("INSERT INTO utilisateurs (login, password) VALUES (:login, :password)");
            $stmt->execute([
                'login' => $login,
                'password' => $hashedPassword
            ]);

            setFlashMessage('success', 'Inscription réussie ! Vous pouvez maintenant vous connecter.');
            redirect('/livre_or/pages/connexion.php');
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de l'inscription. Veuillez réessayer.";
        }
    }
}

$pageTitle = 'Inscription - Livre d\'Or';
include '../includes/header.php';
?>

<div class="form-container">
    <h1>Inscription</h1>

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
            <input type="text" id="login" name="login" value="<?php echo isset($login) ? htmlspecialchars($login) : ''; ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required>
            <small>Minimum 6 caractères</small>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirmer le mot de passe</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>

        <button type="submit" class="btn btn-primary">S'inscrire</button>
    </form>

    <p class="form-footer">
        Vous avez déjà un compte ? <a href="connexion.php">Se connecter</a>
    </p>
</div>

<?php include '../includes/footer.php'; ?>
