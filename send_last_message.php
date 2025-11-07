<?php
/**
 * Script pour envoyer le dernier message d'un utilisateur en MQTT
 *
 * Usage: php send_last_message.php <user_id>
 * Exemple: php send_last_message.php 1
 */

require_once 'config.php';
require_once 'MqttPublisher.php';

// Vérifier les arguments
if ($argc < 2) {
    echo "Usage: php send_last_message.php <user_id>\n";
    echo "Exemple: php send_last_message.php 1\n";
    exit(1);
}

$userId = (int)$argv[1];

if ($userId <= 0) {
    echo "Erreur: L'ID utilisateur doit être un nombre positif\n";
    exit(1);
}

echo "Récupération du dernier message de l'utilisateur ID: $userId\n";

try {
    // Vérifier si l'utilisateur existe
    $stmt = $pdo->prepare("SELECT login FROM utilisateurs WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "Erreur: Utilisateur ID $userId introuvable\n";
        exit(1);
    }

    echo "Utilisateur trouvé: " . $user['login'] . "\n";

    // Envoyer le dernier message en MQTT
    $mqtt = new MqttPublisher();
    $result = $mqtt->publishLastUserMessage($pdo, $userId);

    if ($result) {
        echo "✓ Dernier message envoyé avec succès sur le broker MQTT!\n";
        echo "Topic: " . MQTT_TOPIC_MESSAGES . "\n";
        echo "Serveur: " . MQTT_HOST . ":" . MQTT_PORT . "\n";
    } else {
        echo "✗ Échec de l'envoi ou aucun message trouvé pour cet utilisateur\n";
        exit(1);
    }

} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
