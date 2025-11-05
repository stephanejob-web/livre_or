<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Si déjà connecté, rediriger vers l'accueil
if (isLoggedIn()) {
    redirect('/livre_or/index.php');
}

$errors = [];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = cleanInput($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($login)) {
        $errors[] = "Le login est requis.";
    }

    if (empty($password)) {
        $errors[] = "Le mot de passe est requis.";
    }

    // Vérification des identifiants
    if (empty($errors)) {
        try {
            $pdo = getConnection();
            $stmt = $pdo->prepare("SELECT id, login, password FROM utilisateurs WHERE login = :login");
            $stmt->execute(['login' => $login]);
            $user = $stmt->fetch();

            if ($user && verifyPassword($password, $user['password'])) {
                // Connexion réussie
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_login'] = $user['login'];

                setFlashMessage('success', 'Connexion réussie ! Bienvenue ' . htmlspecialchars($user['login']) . ' !');
                redirect('/livre_or/index.php');
            } else {
                $errors[] = "Login ou mot de passe incorrect.";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la connexion. Veuillez réessayer.";
        }
    }
}

$pageTitle = 'Connexion - Livre d\'Or';
include '../includes/header.php';
?>

<div class="form-container">
    <h1>Connexion</h1>

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
            <input type="text" id="login" name="login" value="<?php echo isset($login) ? htmlspecialchars($login) : ''; ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit" class="btn btn-primary">Se connecter</button>
    </form>

    <p class="form-footer">
        Vous n'avez pas de compte ? <a href="inscription.php">S'inscrire</a>
    </p>
</div>

<?php include '../includes/footer.php'; ?>
