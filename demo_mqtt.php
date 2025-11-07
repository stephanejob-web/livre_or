<?php
/**
 * Script de démonstration MQTT
 *
 * Ce script montre toutes les fonctionnalités MQTT disponibles
 */

require_once 'config.php';
require_once 'MqttPublisher.php';

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║        DÉMONSTRATION MQTT - LIVRE D'OR                    ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

$mqtt = new MqttPublisher();

// ===================================================================
// DÉMONSTRATION 1 : Envoyer un message utilisateur simple
// ===================================================================
echo "📝 DEMO 1: Envoi d'un message utilisateur\n";
echo "─────────────────────────────────────────\n";

$result1 = $mqtt->publishUserMessage(
    42,
    'demo_user',
    'Ceci est un message de démonstration envoyé via MQTT !'
);

if ($result1) {
    echo "✓ Message envoyé avec succès\n";
    echo "  Topic: " . MQTT_TOPIC_MESSAGES . "\n";
    echo "  Payload: {\n";
    echo "    \"user_id\": 42,\n";
    echo "    \"user_login\": \"demo_user\",\n";
    echo "    \"message\": \"Ceci est un message de démonstration...\",\n";
    echo "    \"timestamp\": \"" . date('Y-m-d H:i:s') . "\",\n";
    echo "    \"source\": \"livre_or\"\n";
    echo "  }\n\n";
} else {
    echo "✗ Échec de l'envoi\n\n";
}

// ===================================================================
// DÉMONSTRATION 2 : Envoyer un événement utilisateur
// ===================================================================
echo "🔔 DEMO 2: Envoi d'un événement utilisateur\n";
echo "─────────────────────────────────────────\n";

$result2 = $mqtt->publishUserEvent(
    'login',
    42,
    'demo_user',
    ['ip_address' => '192.168.1.100', 'user_agent' => 'Mozilla/5.0']
);

if ($result2) {
    echo "✓ Événement envoyé avec succès\n";
    echo "  Topic: " . MQTT_TOPIC_USERS . "\n";
    echo "  Event Type: login\n\n";
} else {
    echo "✗ Échec de l'envoi\n\n";
}

// ===================================================================
// DÉMONSTRATION 3 : Récupérer et envoyer le dernier message d'un utilisateur réel
// ===================================================================
echo "🔍 DEMO 3: Dernier message d'un utilisateur réel\n";
echo "─────────────────────────────────────────\n";

try {
    // Récupérer tous les utilisateurs ayant au moins un message
    $stmt = $pdo->query(
        "SELECT DISTINCT u.id, u.login, COUNT(c.id) as nb_messages
         FROM utilisateurs u
         INNER JOIN commentaires c ON u.id = c.id_utilisateur
         GROUP BY u.id, u.login
         ORDER BY nb_messages DESC
         LIMIT 3"
    );
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($users) > 0) {
        echo "Utilisateurs avec messages trouvés: " . count($users) . "\n\n";

        foreach ($users as $user) {
            echo "  • " . $user['login'] . " (ID: " . $user['id'] . ") - " . $user['nb_messages'] . " message(s)\n";

            // Envoyer le dernier message de cet utilisateur
            $result = $mqtt->publishLastUserMessage($pdo, $user['id']);

            if ($result) {
                echo "    ✓ Dernier message envoyé sur MQTT\n";
            } else {
                echo "    ✗ Échec de l'envoi\n";
            }
        }
        echo "\n";
    } else {
        echo "Aucun utilisateur avec messages trouvé\n\n";
    }

} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n\n";
}

// ===================================================================
// DÉMONSTRATION 4 : Statistiques
// ===================================================================
echo "📊 DEMO 4: Statistiques du livre d'or\n";
echo "─────────────────────────────────────────\n";

try {
    // Compter le nombre total de messages
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM commentaires");
    $totalMessages = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Compter le nombre d'utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM utilisateurs");
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Récupérer le dernier message global
    $stmt = $pdo->query(
        "SELECT c.commentaire, c.date, u.login
         FROM commentaires c
         JOIN utilisateurs u ON c.id_utilisateur = u.id
         ORDER BY c.date DESC
         LIMIT 1"
    );
    $lastMessage = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "Total de messages: $totalMessages\n";
    echo "Total d'utilisateurs: $totalUsers\n";

    if ($lastMessage) {
        echo "Dernier message posté par: " . $lastMessage['login'] . "\n";
        echo "Date: " . $lastMessage['date'] . "\n";
        echo "Extrait: " . substr($lastMessage['commentaire'], 0, 50) . "...\n";
    }

} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
}

echo "\n╔═══════════════════════════════════════════════════════════╗\n";
echo "║                    FIN DE LA DÉMONSTRATION                ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

echo "💡 Pour écouter les messages MQTT en temps réel :\n";
echo "   ./listen_mqtt.sh\n\n";
echo "   ou :\n\n";
echo "   mosquitto_sub -h " . MQTT_HOST . " -t \"livreor/#\" -v\n\n";
