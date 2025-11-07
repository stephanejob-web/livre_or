<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/mqtt_config.php';

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

class MqttPublisher {
    private $mqtt;
    private $connectionSettings;

    public function __construct() {
        $this->connectionSettings = (new ConnectionSettings)
            ->setConnectTimeout(3)
            ->setSocketTimeout(5)
            ->setResendTimeout(10)
            ->setKeepAliveInterval(10);

        // Ajouter l'authentification si configurée
        if (MQTT_USERNAME !== '') {
            $this->connectionSettings
                ->setUsername(MQTT_USERNAME)
                ->setPassword(MQTT_PASSWORD);
        }
    }

    /**
     * Publie un message utilisateur sur le broker MQTT
     *
     * @param int $userId ID de l'utilisateur
     * @param string $userLogin Login de l'utilisateur
     * @param string $message Contenu du message
     * @return bool Succès de la publication
     */
    public function publishUserMessage($userId, $userLogin, $message) {
        try {
            // Créer le client MQTT
            $this->mqtt = new MqttClient(MQTT_HOST, MQTT_PORT, MQTT_CLIENT_ID);

            // Se connecter au broker
            $this->mqtt->connect($this->connectionSettings, true);

            // Préparer le payload JSON
            $payload = json_encode([
                'user_id' => $userId,
                'user_login' => $userLogin,
                'message' => $message,
                'timestamp' => date('Y-m-d H:i:s'),
                'source' => 'livre_or'
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            // Publier le message
            $this->mqtt->publish(
                MQTT_TOPIC_MESSAGES,
                $payload,
                0, // QoS 0
                false // Not retained
            );

            // Déconnexion propre
            $this->mqtt->disconnect();

            return true;

        } catch (Exception $e) {
            // Log l'erreur (vous pouvez adapter selon vos besoins)
            error_log("Erreur MQTT: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Publie un événement utilisateur (connexion, inscription, etc.)
     *
     * @param string $eventType Type d'événement (login, register, logout, etc.)
     * @param int $userId ID de l'utilisateur
     * @param string $userLogin Login de l'utilisateur
     * @param array $additionalData Données supplémentaires
     * @return bool Succès de la publication
     */
    public function publishUserEvent($eventType, $userId, $userLogin, $additionalData = []) {
        try {
            $this->mqtt = new MqttClient(MQTT_HOST, MQTT_PORT, MQTT_CLIENT_ID);
            $this->mqtt->connect($this->connectionSettings, true);

            $payload = json_encode(array_merge([
                'event_type' => $eventType,
                'user_id' => $userId,
                'user_login' => $userLogin,
                'timestamp' => date('Y-m-d H:i:s'),
                'source' => 'livre_or'
            ], $additionalData), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $this->mqtt->publish(
                MQTT_TOPIC_USERS,
                $payload,
                0,
                false
            );

            $this->mqtt->disconnect();

            return true;

        } catch (Exception $e) {
            error_log("Erreur MQTT: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère le dernier message d'un utilisateur depuis la BD et l'envoie en MQTT
     *
     * @param PDO $pdo Connexion à la base de données
     * @param int $userId ID de l'utilisateur
     * @return bool Succès de l'opération
     */
    public function publishLastUserMessage($pdo, $userId) {
        try {
            // Récupérer le dernier message de l'utilisateur
            $stmt = $pdo->prepare(
                "SELECT c.commentaire, c.date, u.login, u.id
                 FROM commentaires c
                 JOIN utilisateurs u ON c.id_utilisateur = u.id
                 WHERE c.id_utilisateur = ?
                 ORDER BY c.date DESC
                 LIMIT 1"
            );
            $stmt->execute([$userId]);
            $lastMessage = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($lastMessage) {
                return $this->publishUserMessage(
                    $lastMessage['id'],
                    $lastMessage['login'],
                    $lastMessage['commentaire']
                );
            }

            return false;

        } catch (Exception $e) {
            error_log("Erreur récupération dernier message: " . $e->getMessage());
            return false;
        }
    }
}
