<?php
// ============================================
// PAGE DE CONNEXION
// ============================================
// Cette page permet à un utilisateur existant de se connecter

// --------------------------------------------
// 1. INCLURE LA CONFIGURATION
// --------------------------------------------
require_once 'config.php';

// --------------------------------------------
// 2. INITIALISATION DES VARIABLES
// --------------------------------------------
$message = '';
$error = '';

// --------------------------------------------
// 3. RÉCUPÉRER LE MESSAGE DE SUCCÈS (si vient de l'inscription)
// --------------------------------------------
// $_GET contient les données passées dans l'URL (après le ?)
// Exemple : connexion.php?message=Inscription réussie
// isset() vérifie si la variable existe dans $_GET
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}

// --------------------------------------------
// 4. TRAITEMENT DU FORMULAIRE DE CONNEXION
// --------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // --------------------------------------------
    // 4.1. RÉCUPÉRATION DES DONNÉES
    // --------------------------------------------
    $login = trim($_POST['login']);
    $password = $_POST['password'];

    // --------------------------------------------
    // 4.2. VÉRIFICATION DES CHAMPS
    // --------------------------------------------
    if (empty($login) || empty($password)) {
        $error = "Tous les champs sont obligatoires";
    }
    else {

        // --------------------------------------------
        // 4.3. CHERCHER L'UTILISATEUR DANS LA BASE
        // --------------------------------------------
        // On récupère toutes les infos de l'utilisateur (id, login, password)
        $stmt = $pdo->prepare("SELECT id, login, password FROM utilisateurs WHERE login = ?");
        $stmt->execute([$login]);

        // fetch() récupère UNE ligne de résultat sous forme de tableau associatif
        // Exemple : ['id' => 1, 'login' => 'admin', 'password' => '$2y$10$...']
        $user = $stmt->fetch();

        // --------------------------------------------
        // 4.4. VÉRIFIER LE MOT DE PASSE
        // --------------------------------------------
        // password_verify() compare le mot de passe entré avec le hash stocké en BDD
        // Cette fonction déchiffre automatiquement le hash pour comparer
        // $user && ... = on vérifie d'abord que $user existe (utilisateur trouvé)
        if ($user && password_verify($password, $user['password'])) {

            // --------------------------------------------
            // 4.5. CONNEXION RÉUSSIE : CRÉER LA SESSION
            // --------------------------------------------
            // On stocke les informations de l'utilisateur dans $_SESSION
            // Ces informations seront disponibles sur toutes les pages du site
            $_SESSION['user_id'] = $user['id'];           // On stocke l'ID
            $_SESSION['user_login'] = $user['login'];     // On stocke le login

            // --------------------------------------------
            // 4.6. REDIRECTION VERS L'ACCUEIL
            // --------------------------------------------
            header("Location: index.php");
            exit();
        }
        else {
            // Si l'utilisateur n'existe pas OU si le mot de passe est incorrect
            $error = "Login ou mot de passe incorrect";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Livre d'Or</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="centered">
    <div class="container form-container">
        <h1>Connexion</h1>

        <!-- ========================================== -->
        <!-- AFFICHAGE DES MESSAGES -->
        <!-- ========================================== -->
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <!-- ========================================== -->
        <!-- FORMULAIRE DE CONNEXION -->
        <!-- ========================================== -->
        <form method="POST">
            <!-- Champ login -->
            <div class="form-group">
                <label for="login">Login :</label>
                <input type="text" id="login" name="login" required>
            </div>

            <!-- Champ mot de passe -->
            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
            </div>

            <!-- Bouton de connexion -->
            <button type="submit" class="btn">Se connecter</button>
        </form>

        <!-- ========================================== -->
        <!-- LIENS UTILES -->
        <!-- ========================================== -->
        <div class="links">
            <p>Pas encore inscrit ? <a href="inscription.php">S'inscrire</a></p>
            <p><a href="index.php">Retour à l'accueil</a></p>
        </div>
    </div>
</body>
</html>
