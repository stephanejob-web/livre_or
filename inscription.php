<?php
// ============================================
// PAGE D'INSCRIPTION
// ============================================
// Cette page permet à un nouvel utilisateur de créer un compte

// --------------------------------------------
// 1. INCLURE LA CONFIGURATION
// --------------------------------------------
require_once 'config.php';

// --------------------------------------------
// 2. INITIALISATION DES VARIABLES
// --------------------------------------------
// On crée des variables vides pour stocker les messages d'erreur ou de succès
$message = '';      // Pour les messages de succès (en vert)
$error = '';        // Pour les messages d'erreur (en rouge)

// --------------------------------------------
// 3. TRAITEMENT DU FORMULAIRE
// --------------------------------------------
// $_SERVER['REQUEST_METHOD'] contient la méthode HTTP utilisée (GET ou POST)
// Quand on soumet un formulaire, la méthode est POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // --------------------------------------------
    // 3.1. RÉCUPÉRATION DES DONNÉES DU FORMULAIRE
    // --------------------------------------------
    // $_POST est un tableau qui contient toutes les données du formulaire
    // trim() = enlève les espaces au début et à la fin
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // --------------------------------------------
    // 3.2. VÉRIFICATIONS DES DONNÉES
    // --------------------------------------------

    // Vérification 1 : Les champs ne doivent pas être vides
    // empty() = vérifie si une variable est vide
    // || = OU logique
    if (empty($login) || empty($password)) {
        $error = "Tous les champs sont obligatoires";
    }
    // Vérification 2 : Les deux mots de passe doivent être identiques
    // !== = différent de
    elseif ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas";
    }
    // Vérification 3 : Le mot de passe doit avoir au moins 4 caractères
    // strlen() = retourne la longueur d'une chaîne de caractères
    elseif (strlen($password) < 4) {
        $error = "Le mot de passe doit contenir au moins 4 caractères";
    }
    // Si toutes les vérifications sont OK
    else {

        // --------------------------------------------
        // 3.3. VÉRIFIER SI LE LOGIN EXISTE DÉJÀ
        // --------------------------------------------
        // prepare() = prépare une requête SQL (protection contre injection SQL)
        // ? = placeholder qui sera remplacé par une valeur sécurisée
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE login = ?");

        // execute() = exécute la requête en remplaçant le ? par la valeur dans le tableau
        $stmt->execute([$login]);

        // fetch() = récupère le résultat de la requête
        // Si un résultat existe, c'est que le login est déjà utilisé
        if ($stmt->fetch()) {
            $error = "Ce login existe déjà";
        }
        // Si le login n'existe pas encore, on peut créer le compte
        else {

            // --------------------------------------------
            // 3.4. HASHER LE MOT DE PASSE
            // --------------------------------------------
            // password_hash() = crypte le mot de passe pour le sécuriser
            // On ne stocke JAMAIS un mot de passe en clair dans la base de données !
            // PASSWORD_DEFAULT = utilise l'algorithme de cryptage par défaut (bcrypt)
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // --------------------------------------------
            // 3.5. INSÉRER L'UTILISATEUR DANS LA BASE
            // --------------------------------------------
            // INSERT INTO = requête SQL pour ajouter une ligne dans une table
            $stmt = $pdo->prepare("INSERT INTO utilisateurs (login, password) VALUES (?, ?)");

            // On exécute la requête en passant le login et le mot de passe hashé
            if ($stmt->execute([$login, $password_hash])) {

                // --------------------------------------------
                // 3.6. REDIRECTION VERS LA PAGE DE CONNEXION
                // --------------------------------------------
                $message = "Inscription réussie ! Vous pouvez vous connecter.";

                // header() = envoie un en-tête HTTP pour rediriger l'utilisateur
                // Location: = l'URL vers laquelle rediriger
                // ?message= = on passe un message via l'URL (GET)
                header("Location: connexion.php?message=Inscription réussie");

                // exit() = arrête l'exécution du script après la redirection
                exit();
            }
            else {
                $error = "Erreur lors de l'inscription";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Livre d'Or</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="centered">
    <div class="container form-container">
        <h1>Inscription</h1>

        <!-- ========================================== -->
        <!-- AFFICHAGE DES MESSAGES D'ERREUR -->
        <!-- ========================================== -->
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- ========================================== -->
        <!-- AFFICHAGE DES MESSAGES DE SUCCÈS -->
        <!-- ========================================== -->
        <?php if ($message): ?>
            <div class="success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <!-- ========================================== -->
        <!-- FORMULAIRE D'INSCRIPTION -->
        <!-- ========================================== -->
        <!-- method="POST" = les données seront envoyées via la méthode POST -->
        <!-- Si on ne met pas d'attribut action, le formulaire s'envoie sur la même page -->
        <form method="POST">

            <!-- Champ 1 : Login -->
            <div class="form-group">
                <label for="login">Login :</label>
                <!-- name="login" = le nom de la variable dans $_POST -->
                <!-- required = le champ est obligatoire (vérification HTML5) -->
                <input type="text" id="login" name="login" required>
            </div>

            <!-- Champ 2 : Mot de passe -->
            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <!-- type="password" = les caractères sont masqués -->
                <input type="password" id="password" name="password" required>
            </div>

            <!-- Champ 3 : Confirmation du mot de passe -->
            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe :</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <!-- Bouton de soumission -->
            <!-- type="submit" = quand on clique, le formulaire est envoyé -->
            <button type="submit" class="btn">S'inscrire</button>
        </form>

        <!-- ========================================== -->
        <!-- LIENS UTILES -->
        <!-- ========================================== -->
        <div class="links">
            <p>Déjà inscrit ? <a href="connexion.php">Se connecter</a></p>
            <p><a href="index.php">Retour à l'accueil</a></p>
        </div>
    </div>
</body>
</html>
