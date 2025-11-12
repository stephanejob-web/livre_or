<?php
// ============================================
// PAGE DE PROFIL
// ============================================
// Cette page permet à un utilisateur de modifier ses informations

// --------------------------------------------
// 1. INCLURE LA CONFIGURATION
// --------------------------------------------
require_once 'config.php';

// --------------------------------------------
// 2. VÉRIFIER SI L'UTILISATEUR EST CONNECTÉ
// --------------------------------------------
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
// 4. RÉCUPÉRER LES INFORMATIONS ACTUELLES DE L'UTILISATEUR
// --------------------------------------------
// On récupère le login actuel pour le pré-remplir dans le formulaire
$stmt = $pdo->prepare("SELECT login FROM utilisateurs WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// --------------------------------------------
// 5. TRAITEMENT DU FORMULAIRE DE MODIFICATION
// --------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // --------------------------------------------
    // 5.1. RÉCUPÉRATION DES DONNÉES
    // --------------------------------------------
    $new_login = trim($_POST['login']);
    $new_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // --------------------------------------------
    // 5.2. VÉRIFICATIONS
    // --------------------------------------------

    // Vérification 1 : Le login ne peut pas être vide
    if (empty($new_login)) {
        $error = "Le login ne peut pas être vide";
    }
    // Vérification 2 : Si on veut changer le mot de passe, les 2 doivent correspondre
    // !empty($new_password) = si le champ mot de passe n'est pas vide
    // && = ET logique
    elseif (!empty($new_password) && $new_password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas";
    }
    // Vérification 3 : Si on veut changer le mot de passe, il doit avoir au moins 4 caractères
    elseif (!empty($new_password) && strlen($new_password) < 4) {
        $error = "Le mot de passe doit contenir au moins 4 caractères";
    }
    // Si toutes les vérifications sont OK
    else {

        // --------------------------------------------
        // 5.3. VÉRIFIER SI LE NOUVEAU LOGIN N'EST PAS DÉJÀ PRIS
        // --------------------------------------------
        // On vérifie que le login n'existe pas chez un AUTRE utilisateur
        // AND id != ? = sauf pour notre propre ID (on peut garder notre login actuel)
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE login = ? AND id != ?");
        $stmt->execute([$new_login, $_SESSION['user_id']]);

        if ($stmt->fetch()) {
            $error = "Ce login est déjà utilisé";
        }
        else {

            // --------------------------------------------
            // 5.4. MISE À JOUR DES INFORMATIONS
            // --------------------------------------------

            // CAS 1 : L'utilisateur veut AUSSI changer son mot de passe
            if (!empty($new_password)) {

                // On hashe le nouveau mot de passe
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

                // UPDATE = modifier une ligne existante dans la table
                // SET login = ?, password = ? = on modifie login et password
                // WHERE id = ? = uniquement pour cet ID
                $stmt = $pdo->prepare("UPDATE utilisateurs SET login = ?, password = ? WHERE id = ?");
                $stmt->execute([$new_login, $password_hash, $_SESSION['user_id']]);
            }
            // CAS 2 : L'utilisateur veut SEULEMENT changer son login
            else {

                // On modifie seulement le login, pas le mot de passe
                $stmt = $pdo->prepare("UPDATE utilisateurs SET login = ? WHERE id = ?");
                $stmt->execute([$new_login, $_SESSION['user_id']]);
            }

            // --------------------------------------------
            // 5.5. METTRE À JOUR LA SESSION
            // --------------------------------------------
            // On met à jour le login dans la session pour qu'il soit correct partout
            $_SESSION['user_login'] = $new_login;

            // Message de succès
            $message = "Profil mis à jour avec succès";

            // On met à jour aussi la variable $user pour le formulaire
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

        <!-- ========================================== -->
        <!-- AFFICHAGE DES MESSAGES -->
        <!-- ========================================== -->
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <!-- Information pour l'utilisateur -->
        <div class="info">
            Laissez le mot de passe vide si vous ne souhaitez pas le changer.
        </div>

        <!-- ========================================== -->
        <!-- FORMULAIRE DE MODIFICATION -->
        <!-- ========================================== -->
        <form method="POST">

            <!-- Champ login -->
            <div class="form-group">
                <label for="login">Login :</label>
                <!-- value = pré-remplit le champ avec le login actuel -->
                <input type="text" id="login" name="login" value="<?php echo htmlspecialchars($user['login']); ?>" required>
            </div>

            <!-- Champ nouveau mot de passe (optionnel) -->
            <div class="form-group">
                <label for="password">Nouveau mot de passe (optionnel) :</label>
                <!-- Pas de "required" car c'est optionnel -->
                <!-- Pas de value car on ne pré-remplit JAMAIS un champ mot de passe -->
                <input type="password" id="password" name="password">
            </div>

            <!-- Champ confirmation mot de passe -->
            <div class="form-group">
                <label for="confirm_password">Confirmer le nouveau mot de passe :</label>
                <input type="password" id="confirm_password" name="confirm_password">
            </div>

            <!-- Bouton de soumission -->
            <button type="submit" class="btn">Mettre à jour</button>
        </form>

        <!-- ========================================== -->
        <!-- LIENS UTILES -->
        <!-- ========================================== -->
        <div class="links">
            <p><a href="index.php">Retour à l'accueil</a></p>
        </div>
    </div>
</body>
</html>
