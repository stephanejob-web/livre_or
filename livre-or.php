<?php
// ============================================
// PAGE DU LIVRE D'OR
// ============================================
// Cette page affiche tous les commentaires du livre d'or

// --------------------------------------------
// 1. INCLURE LA CONFIGURATION
// --------------------------------------------
require_once 'config.php';

// --------------------------------------------
// 2. RÉCUPÉRER TOUS LES COMMENTAIRES
// --------------------------------------------
// query() = exécute une requête SQL directement (sans préparation)
// On peut utiliser query() car il n'y a pas de variables dans la requête

// SELECT = récupérer des données
// c.commentaire = le texte du commentaire (c = alias pour la table commentaires)
// c.date = la date du commentaire
// u.login = le login de l'auteur (u = alias pour la table utilisateurs)
// FROM commentaires c = depuis la table commentaires, qu'on appelle "c"
// JOIN utilisateurs u = on joint la table utilisateurs, qu'on appelle "u"
// ON c.id_utilisateur = u.id = condition de jointure (relier les tables)
// ORDER BY c.date DESC = trier par date décroissante (le plus récent en premier)

$stmt = $pdo->query("
    SELECT c.commentaire, c.date, u.login
    FROM commentaires c
    JOIN utilisateurs u ON c.id_utilisateur = u.id
    ORDER BY c.date DESC
");

// fetchAll() = récupérer TOUS les résultats sous forme de tableau
// Cela va créer un tableau avec tous les commentaires
// Exemple : [
//   ['commentaire' => 'Super site', 'date' => '2025-11-05', 'login' => 'admin'],
//   ['commentaire' => 'Très bien', 'date' => '2025-11-04', 'login' => 'jean']
// ]
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

        <!-- ========================================== -->
        <!-- MENU DE NAVIGATION -->
        <!-- ========================================== -->
        <div class="menu">
            <a href="index.php" class="btn">Accueil</a>

            <?php if(isset($_SESSION['user_id'])): ?>
                <!-- Si l'utilisateur est connecté -->
                <a href="commentaire.php" class="btn">Ajouter un commentaire</a>
                <a href="deconnexion.php" class="btn">Déconnexion</a>
            <?php else: ?>
                <!-- Si l'utilisateur n'est pas connecté -->
                <a href="connexion.php" class="btn">Se connecter pour commenter</a>
            <?php endif; ?>
        </div>

        <!-- ========================================== -->
        <!-- AFFICHAGE DES COMMENTAIRES -->
        <!-- ========================================== -->

        <?php
        // count() = compte le nombre d'éléments dans un tableau
        // Si il y a au moins 1 commentaire
        if (count($commentaires) > 0):
        ?>

            <?php
            // --------------------------------------------
            // BOUCLE SUR TOUS LES COMMENTAIRES
            // --------------------------------------------
            // foreach = pour chaque élément du tableau
            // $commentaires as $comm = pour chaque ligne de $commentaires, on l'appelle $comm
            // À chaque tour de boucle, $comm contiendra un commentaire différent

            foreach($commentaires as $comm):
            ?>
                <!-- Bloc pour UN commentaire -->
                <div class="commentaire">

                    <!-- En-tête du commentaire (date et auteur) -->
                    <div class="commentaire-header">
                        Posté le <strong>
                            <?php
                            // date() = formate une date
                            // 'd/m/Y' = jour/mois/année (exemple : 05/11/2025)
                            // strtotime() = convertit la date texte en timestamp
                            echo date('d/m/Y', strtotime($comm['date']));
                            ?>
                        </strong>
                        par <strong><?php echo htmlspecialchars($comm['login']); ?></strong>
                    </div>

                    <!-- Texte du commentaire -->
                    <div class="commentaire-text">
                        <?php
                        // nl2br() = transforme les retours à la ligne (\n) en <br>
                        // Cela permet d'afficher les sauts de ligne dans le HTML
                        // htmlspecialchars() = protège contre les attaques XSS
                        echo nl2br(htmlspecialchars($comm['commentaire']));
                        ?>
                    </div>
                </div>

            <?php endforeach; ?>

        <?php else: ?>
            <!-- Si il n'y a aucun commentaire -->
            <div class="no-comments">
                Aucun commentaire pour le moment. Soyez le premier à laisser un message !
            </div>

        <?php endif; ?>

    </div>
</body>
</html>
