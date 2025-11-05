<?php
require_once 'config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

$message = '';
$error = '';

// Récupérer les infos actuelles
$stmt = $pdo->prepare("SELECT login FROM utilisateurs WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_login = trim($_POST['login']);
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($new_login)) {
        $error = "Le login ne peut pas être vide";
    } elseif (!empty($new_password) && $new_password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas";
    } elseif (!empty($new_password) && strlen($new_password) < 4) {
        $error = "Le mot de passe doit contenir au moins 4 caractères";
    } else {
        // Vérifier si le nouveau login n'est pas déjà utilisé par quelqu'un d'autre
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE login = ? AND id != ?");
        $stmt->execute([$new_login, $_SESSION['user_id']]);

        if ($stmt->fetch()) {
            $error = "Ce login est déjà utilisé";
        } else {
            // Mettre à jour le profil
            if (!empty($new_password)) {
                // Avec nouveau mot de passe
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE utilisateurs SET login = ?, password = ? WHERE id = ?");
                $stmt->execute([$new_login, $password_hash, $_SESSION['user_id']]);
            } else {
                // Sans changer le mot de passe
                $stmt = $pdo->prepare("UPDATE utilisateurs SET login = ? WHERE id = ?");
                $stmt->execute([$new_login, $_SESSION['user_id']]);
            }

            $_SESSION['user_login'] = $new_login;
            $message = "Profil mis à jour avec succès";
            $user['login'] = $new_login;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="centered">
    <div class="container form-container">
        <h1>👤 Mon Profil</h1>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="info">
            Laissez le mot de passe vide si vous ne souhaitez pas le changer.
        </div>

        <form method="POST">
            <div class="form-group">
                <label for="login">Login :</label>
                <input type="text" id="login" name="login" value="<?php echo htmlspecialchars($user['login']); ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Nouveau mot de passe (optionnel) :</label>
                <input type="password" id="password" name="password">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmer le nouveau mot de passe :</label>
                <input type="password" id="confirm_password" name="confirm_password">
            </div>

            <button type="submit" class="btn">Mettre à jour</button>
        </form>

        <div class="links">
            <p><a href="index.php">Retour à l'accueil</a></p>
        </div>
    </div>
</body>
</html>
