# Intégration MQTT - Livre d'Or

## Description

Cette intégration permet d'envoyer automatiquement les messages du livre d'or vers un broker MQTT.

## Configuration

### Serveur MQTT

La configuration se trouve dans `mqtt_config.php` :

```php
MQTT_HOST: 192.168.10.166
MQTT_PORT: 1883
MQTT_CLIENT_ID: livre_or_<unique_id>
```

### Topics MQTT

- `livreor/messages` : Messages des utilisateurs
- `livreor/users` : Événements utilisateurs (connexion, inscription, etc.)

### Authentification (optionnel)

Si votre broker MQTT nécessite une authentification, modifiez dans `mqtt_config.php` :

```php
define('MQTT_USERNAME', 'votre_username');
define('MQTT_PASSWORD', 'votre_password');
```

## Format des Messages

### Message utilisateur (topic: livreor/messages)

```json
{
  "user_id": 1,
  "user_login": "darksh3ll",
  "message": "Contenu du message",
  "timestamp": "2025-11-06 15:30:00",
  "source": "livre_or"
}
```

### Événement utilisateur (topic: livreor/users)

```json
{
  "event_type": "login|register|logout",
  "user_id": 1,
  "user_login": "darksh3ll",
  "timestamp": "2025-11-06 15:30:00",
  "source": "livre_or"
}
```

## Fonctionnement Automatique

Lorsqu'un utilisateur publie un commentaire via l'interface web (`commentaire.php`), le message est :

1. Enregistré dans la base de données MySQL
2. Automatiquement envoyé au broker MQTT
3. Publié sur le topic `livreor/messages`

## Scripts Utilitaires

### 1. Test de connexion MQTT

```bash
php test_mqtt.php
```

Ce script teste :
- La connexion au broker MQTT
- L'envoi d'un message de test
- La récupération et l'envoi du dernier message d'un utilisateur

### 2. Envoyer le dernier message d'un utilisateur

```bash
php send_last_message.php <user_id>
```

Exemple :
```bash
php send_last_message.php 1
```

Ce script récupère le dernier message d'un utilisateur spécifique depuis la base de données et l'envoie au broker MQTT.

## Classe MqttPublisher

La classe `MqttPublisher` fournit les méthodes suivantes :

### publishUserMessage($userId, $userLogin, $message)

Publie un message utilisateur sur le broker MQTT.

```php
$mqtt = new MqttPublisher();
$mqtt->publishUserMessage(1, 'darksh3ll', 'Mon message');
```

### publishUserEvent($eventType, $userId, $userLogin, $additionalData)

Publie un événement utilisateur (connexion, inscription, etc.).

```php
$mqtt = new MqttPublisher();
$mqtt->publishUserEvent('login', 1, 'darksh3ll');
```

### publishLastUserMessage($pdo, $userId)

Récupère et publie le dernier message d'un utilisateur depuis la base de données.

```php
$mqtt = new MqttPublisher();
$mqtt->publishLastUserMessage($pdo, 1);
```

## Dépendances

- PHP 7.4+
- Composer
- php-mqtt/client ^1.0

## Installation

Les dépendances sont déjà installées. Si vous devez les réinstaller :

```bash
composer install
```

## Monitoring et Debugging

Les erreurs MQTT sont loggées via `error_log()`. Vérifiez les logs PHP pour diagnostiquer les problèmes de connexion.

## Sécurité

- Les messages sont envoyés en QoS 0 (at most once)
- Les messages ne sont pas retenus (retained = false)
- Authentification optionnelle supportée
- Timeout de connexion : 3 secondes
- Timeout de socket : 5 secondes

## Abonnement aux Messages

Pour recevoir les messages MQTT, vous pouvez utiliser un client MQTT comme `mosquitto_sub` :

```bash
# Écouter tous les messages du livre d'or
mosquitto_sub -h 192.168.10.166 -t "livreor/#" -v

# Écouter uniquement les messages utilisateurs
mosquitto_sub -h 192.168.10.166 -t "livreor/messages" -v

# Écouter uniquement les événements utilisateurs
mosquitto_sub -h 192.168.10.166 -t "livreor/users" -v
```

## Exemple d'utilisation avec Node-RED

Vous pouvez facilement intégrer ces messages dans Node-RED en utilisant un nœud MQTT In :

1. Ajouter un nœud "mqtt in"
2. Configurer le broker : 192.168.10.166:1883
3. Topic : `livreor/messages`
4. Le payload sera automatiquement parsé en JSON
