"""
Version minimale pour ESP32 avec LCD 20x4
À intégrer dans votre code existant

Prérequis:
- WiFi connecté
- LCD initialisé dans votre code
"""

import ujson
import time
from umqtt.simple import MQTTClient
import machine

# ============================================
# CONFIGURATION - À ADAPTER
# ============================================

MQTT_BROKER = "192.168.10.166"
MQTT_PORT = 1883
MQTT_TOPIC = b"livreor/messages"

# Supposons que 'lcd' est déjà initialisé dans votre code
# Si non, décommentez et adaptez:
# from machine import Pin, SoftI2C
# from i2c_lcd import I2cLcd
# i2c = SoftI2C(scl=Pin(22), sda=Pin(21), freq=10000)
# lcd = I2cLcd(i2c, 0x27, 4, 20)  # 20x4

# ============================================
# FONCTIONS D'AFFICHAGE
# ============================================

def afficher_message(user, message, timestamp=""):
    """
    Affiche un message du livre d'or sur LCD 20x4

    Format:
    ┌────────────────────┐
    │    LIVRE D'OR      │  Ligne 0: Titre centré
    │ User      15:30    │  Ligne 1: Nom + heure
    │────────────────────│  Ligne 2: Séparateur
    │ Message...         │  Ligne 3: Contenu
    └────────────────────┘
    """
    lcd.clear()

    # Ligne 0: Titre
    titre = "LIVRE D'OR"
    lcd.move_to((20 - len(titre)) // 2, 0)
    lcd.putstr(titre)

    # Ligne 1: User + heure
    heure = timestamp[11:16] if len(timestamp) >= 16 else ""
    ligne1 = f"{user[:12]} {heure}".ljust(20)
    lcd.move_to(0, 1)
    lcd.putstr(ligne1)

    # Ligne 2: Séparateur
    lcd.move_to(0, 2)
    lcd.putstr("-" * 20)

    # Ligne 3: Message
    lcd.move_to(0, 3)
    lcd.putstr(message[:20])

    # Si message long, défiler
    if len(message) > 20:
        time.sleep(2)
        # Découper en mots
        mots = message.split()
        ligne = ""
        for mot in mots[1:]:  # On a déjà affiché le premier mot
            if len(ligne) + len(mot) + 1 <= 20:
                ligne = ligne + " " + mot if ligne else mot
            else:
                lcd.move_to(0, 3)
                lcd.putstr(ligne.ljust(20))
                time.sleep(1.5)
                ligne = mot

        if ligne:
            lcd.move_to(0, 3)
            lcd.putstr(ligne.ljust(20))
            time.sleep(1.5)

def ecran_attente():
    """Écran d'attente de messages"""
    lcd.clear()
    lcd.move_to((20 - 10) // 2, 0)
    lcd.putstr("LIVRE D'OR")
    lcd.move_to(0, 1)
    lcd.putstr("-" * 20)
    lcd.move_to((20 - 14) // 2, 2)
    lcd.putstr("En attente de")
    lcd.move_to((20 - 11) // 2, 3)
    lcd.putstr("messages...")

# ============================================
# CALLBACK MQTT
# ============================================

def callback_mqtt(topic, msg):
    """Appelé quand un message MQTT arrive"""
    try:
        data = ujson.loads(msg)
        user = data.get('user_login', 'Anonyme')
        message = data.get('message', '')
        timestamp = data.get('timestamp', '')

        print(f"{user}: {message}")

        afficher_message(user, message, timestamp)
        time.sleep(3)
        ecran_attente()

    except Exception as e:
        print(f"Erreur: {e}")

# ============================================
# CONNEXION ET BOUCLE
# ============================================

def demarrer_mqtt():
    """Démarre l'écoute MQTT"""

    # Créer le client MQTT
    client = MQTTClient(
        client_id=b"esp32_" + str(machine.unique_id()),
        server=MQTT_BROKER,
        port=MQTT_PORT,
        keepalive=60
    )

    # Définir le callback
    client.set_callback(callback_mqtt)

    # Connexion
    client.connect()
    client.subscribe(MQTT_TOPIC)

    print(f"Connecté à {MQTT_BROKER}")
    print(f"Topic: {MQTT_TOPIC}")

    # Afficher écran d'attente
    ecran_attente()

    # Boucle d'écoute
    dernier_ping = time.time()

    while True:
        try:
            client.check_msg()

            # Ping toutes les 30s
            if time.time() - dernier_ping > 30:
                client.ping()
                dernier_ping = time.time()

            time.sleep(0.1)

        except KeyboardInterrupt:
            client.disconnect()
            break
        except Exception as e:
            print(f"Erreur: {e}")
            time.sleep(5)
            # Reconnexion
            client.connect()
            client.subscribe(MQTT_TOPIC)

# ============================================
# DÉMARRAGE
# ============================================

# À appeler dans votre code principal après la connexion WiFi:
# demarrer_mqtt()

if __name__ == "__main__":
    # Si vous exécutez ce fichier directement
    # Assurez-vous que lcd est bien initialisé
    demarrer_mqtt()
