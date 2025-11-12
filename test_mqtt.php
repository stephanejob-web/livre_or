<?php
/**
 * Script de test MQTT
 *
 * Ce script teste l'envoi de messages MQTT au broker
 * Usage: php test_mqtt.php
 */

require_once 'config.php';
require_once 'MqttPublisher.php';

echo "=== TEST DE CONNEXION MQTT ===\n\n";

// Vérifier la configuration
echo "Configuration MQTT:\n";
echo "- Host: " . MQTT_HOST . "\n";
echo "- Port: " . MQTT_PORT . "\n";
echo "- Client ID: " . MQTT_CLIENT_ID . "\n";
echo "- Topic messages: " . MQTT_TOPIC_MESSAGES . "\n\n";

// Test 1: Envoyer un message de test simple
echo "Test 1: Envoi d'un message de test...\n";
$mqtt = new MqttPublisher();

$testResult = $mqtt->publishUserMessage(
    999,
    'test_user',
    'Ceci est un message de test MQTT depuis le livre d\'or'
);

if ($testResult) {
    echo "✓ Message de test envoyé avec succès!\n\n";
} else {
    echo "✗ Échec de l'envoi du message de test\n\n";
}

// Test 2: Récupérer et envoyer le dernier message d'un utilisateur existant
echo "Test 2: Récupération du dernier message d'un utilisateur...\n";

try {
    // Récupérer le premier utilisateur de la base
    $stmt = $pdo->query("SELECT id, login FROM utilisateurs LIMIT 1");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "- Utilisateur trouvé: " . $user['login'] . " (ID: " . $user['id'] . ")\n";

        $lastMessageResult = $mqtt->publishLastUserMessage($pdo, $user['id']);

        if ($lastMessageResult) {
            echo "✓ Dernier message de l'utilisateur envoyé avec succès!\n\n";
        } else {
            echo "✗ Aucun message trouvé ou échec de l'envoi\n\n";
        }
    } else {
        echo "✗ Aucun utilisateur trouvé dans la base de données\n\n";
    }

} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n\n";
}

echo "=== FIN DES TESTS ===\n";
