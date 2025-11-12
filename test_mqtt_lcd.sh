#!/bin/bash

# Script pour tester l'affichage LCD ESP32 en envoyant des messages MQTT

MQTT_HOST="192.168.10.166"
MQTT_TOPIC="livreor/messages"

echo "======================================"
echo "TEST D'ENVOI DE MESSAGES MQTT"
echo "pour l'affichage LCD ESP32"
echo "======================================"
echo ""
echo "Serveur MQTT: $MQTT_HOST"
echo "Topic: $MQTT_TOPIC"
echo ""

# Message 1 - Court
echo "Envoi du message 1 (court)..."
mosquitto_pub -h $MQTT_HOST -t "$MQTT_TOPIC" -m '{
  "user_id": 1,
  "user_login": "Stephane",
  "message": "Bonjour!",
  "timestamp": "2025-11-06 16:30:00",
  "source": "test"
}'
sleep 5

# Message 2 - Moyen
echo "Envoi du message 2 (moyen)..."
mosquitto_pub -h $MQTT_HOST -t "$MQTT_TOPIC" -m '{
  "user_id": 2,
  "user_login": "Marie",
  "message": "Super projet!",
  "timestamp": "2025-11-06 16:30:15",
  "source": "test"
}'
sleep 5

# Message 3 - Long (défilement)
echo "Envoi du message 3 (long avec défilement)..."
mosquitto_pub -h $MQTT_HOST -t "$MQTT_TOPIC" -m '{
  "user_id": 3,
  "user_login": "Jean",
  "message": "Ceci est un message beaucoup plus long qui va defiler sur l ecran LCD",
  "timestamp": "2025-11-06 16:30:30",
  "source": "test"
}'
sleep 8

# Message 4 - Avec caractères spéciaux
echo "Envoi du message 4 (caractères spéciaux)..."
mosquitto_pub -h $MQTT_HOST -t "$MQTT_TOPIC" -m '{
  "user_id": 4,
  "user_login": "Laetitia",
  "message": "Bravo pour ce projet! :)",
  "timestamp": "2025-11-06 16:30:45",
  "source": "test"
}'
sleep 5

# Message 5 - Dernier message de la BD
echo "Envoi du dernier message réel de la base de données..."
php send_last_message.php 1
sleep 5

echo ""
echo "======================================"
echo "Tests terminés!"
echo "======================================"
