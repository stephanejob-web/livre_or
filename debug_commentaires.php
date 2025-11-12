<?php
// Script de debug pour vérifier les commentaires
require_once 'config.php';

echo "<h2>DEBUG - Commentaires dans la base de données</h2>";

try {
    // Test 1 : Compter les commentaires
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM commentaires");
    $total = $stmt->fetch()['total'];
    echo "<p><strong>Total de commentaires :</strong> $total</p>";

    // Test 2 : Lister tous les commentaires (même requête que livre-or.php)
    $stmt = $pdo->query("
        SELECT c.id, c.commentaire, c.date, c.id_utilisateur, u.login
        FROM commentaires c
        JOIN utilisateurs u ON c.id_utilisateur = u.id
        ORDER BY c.date DESC
    ");

    $commentaires = $stmt->fetchAll();

    echo "<p><strong>Nombre de commentaires récupérés :</strong> " . count($commentaires) . "</p>";

    if (count($commentaires) > 0) {
        echo "<h3>Liste des commentaires :</h3>";
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Date</th><th>Auteur</th><th>Commentaire</th></tr>";

        foreach($commentaires as $comm) {
            echo "<tr>";
            echo "<td>" . $comm['id'] . "</td>";
            echo "<td>" . $comm['date'] . "</td>";
            echo "<td>" . htmlspecialchars($comm['login']) . "</td>";
            echo "<td>" . htmlspecialchars($comm['commentaire']) . "</td>";
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "<p style='color: red;'>AUCUN commentaire trouvé !</p>";
    }

    // Test 3 : Vérifier s'il y a des commentaires sans utilisateur
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM commentaires WHERE id_utilisateur NOT IN (SELECT id FROM utilisateurs)");
    $orphelins = $stmt->fetch()['total'];

    if ($orphelins > 0) {
        echo "<p style='color: orange;'><strong>ATTENTION :</strong> $orphelins commentaire(s) ont un id_utilisateur qui n'existe pas dans la table utilisateurs !</p>";
    }

} catch(PDOException $e) {
    echo "<p style='color: red;'><strong>Erreur SQL :</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='livre-or.php'>Retour au livre d'or</a></p>";
?>
