#!/bin/bash
# Script pour écouter les messages MQTT du livre d'or

echo "=== Écoute des messages MQTT du Livre d'Or ==="
echo "Serveur: 192.168.10.166:1883"
echo "Topics: livreor/#"
echo ""
echo "Appuyez sur Ctrl+C pour arrêter"
echo "================================================"
echo ""

mosquitto_sub -h 192.168.10.166 -t "livreor/#" -v
