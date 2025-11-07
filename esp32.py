"""
Script ESP32 pour afficher les messages du livre d'or sur un écran LCD I2C
via MQTT

Matériel requis:
- ESP32
- Écran LCD I2C (16x2)
- Connexion WiFi

Bibliothèques MicroPython requises:
- umqtt.simple (ou umqtt.robust)
- lcd_api.py
- i2c_lcd.py
"""

import machine
from machine import Pin, SoftI2C
from lcd_api import LcdApi
from i2c_lcd import I2cLcd
import network
import time
import ujson
from umqtt.simple import MQTTClient

# ============================================
# CONFIGURATION
# ============================================

# Configuration WiFi
WIFI_SSID = "VOTRE_SSID"        # À modifier
WIFI_PASSWORD = "VOTRE_PASSWORD" # À modifier

# Configuration MQTT
MQTT_BROKER = "192.168.10.166"
MQTT_PORT = 1883
MQTT_CLIENT_ID = "esp32_lcd_" + str(machine.unique_id())
MQTT_TOPIC = b"livreor/messages"
MQTT_USERNAME = None  # Mettre votre username si nécessaire
MQTT_PASSWORD = None  # Mettre votre password si nécessaire

# Configuration LCD I2C
I2C_ADDR = 0x27
LCD_ROWS = 2
LCD_COLS = 16

# ============================================
# INITIALISATION LCD
# ============================================

i2c = SoftI2C(scl=Pin(22), sda=Pin(21), freq=10000)
lcd = I2cLcd(i2c, I2C_ADDR, LCD_ROWS, LCD_COLS)

# ============================================
# FONCTIONS UTILITAIRES LCD
# ============================================

def clear_lcd():
    """Efface l'écran LCD"""
    lcd.clear()

def display_text(text, row=0, center=False):
    """
    Affiche du texte sur une ligne spécifique du LCD

    Args:
        text: Texte à afficher
        row: Numéro de ligne (0 ou 1)
        center: Centrer le texte
    """
    if center:
        padding = (LCD_COLS - len(text)) // 2
        text = " " * padding + text

    # Tronquer si trop long
    text = text[:LCD_COLS]

    # Compléter avec des espaces pour effacer les anciens caractères
    text = text.ljust(LCD_COLS)

    lcd.move_to(0, row)
    lcd.putstr(text)

def scroll_text(text, row=0, delay=0.3):
    """
    Fait défiler un texte long sur une ligne

    Args:
        text: Texte à faire défiler
        row: Numéro de ligne
        delay: Délai entre chaque étape du défilement
    """
    if len(text) <= LCD_COLS:
        display_text(text, row)
        return

    # Ajouter des espaces au début et à la fin
    text = " " * LCD_COLS + text + " " * LCD_COLS

    for i in range(len(text) - LCD_COLS + 1):
        lcd.move_to(0, row)
        lcd.putstr(text[i:i+LCD_COLS])
        time.sleep(delay)

def display_message(user, message):
    """
    Affiche un message du livre d'or sur le LCD

    Args:
        user: Nom de l'utilisateur
        message: Contenu du message
    """
    # Ligne 1: Afficher l'utilisateur
    display_text(user[:LCD_COLS], row=0, center=False)

    # Ligne 2: Afficher le message (avec défilement si nécessaire)
    if len(message) <= LCD_COLS:
        display_text(message, row=1)
        time.sleep(3)
    else:
        scroll_text(message, row=1, delay=0.3)

    time.sleep(2)

# ============================================
# CONNEXION WIFI
# ============================================

def connect_wifi():
    """Se connecter au WiFi"""
    print("Connexion WiFi...")
    display_text("WiFi...", row=0, center=True)

    wlan = network.WLAN(network.STA_IF)
    wlan.active(True)

    if not wlan.isconnected():
        wlan.connect(WIFI_SSID, WIFI_PASSWORD)

        # Attendre la connexion (timeout 30s)
        timeout = 30
        while not wlan.isconnected() and timeout > 0:
            time.sleep(1)
            timeout -= 1
            display_text(f"WiFi... {timeout}s", row=0, center=True)

    if wlan.isconnected():
        print("WiFi connecté!")
        print("IP:", wlan.ifconfig()[0])
        display_text("WiFi OK!", row=0, center=True)
        display_text(wlan.ifconfig()[0], row=1, center=True)
        time.sleep(2)
        return True
    else:
        print("Échec connexion WiFi")
        display_text("WiFi ERREUR!", row=0, center=True)
        return False

# ============================================
# CALLBACK MQTT
# ============================================

def mqtt_callback(topic, msg):
    """
    Fonction appelée lors de la réception d'un message MQTT

    Args:
        topic: Topic MQTT
        msg: Message reçu (JSON)
    """
    print(f"Message reçu sur {topic}: {msg}")

    try:
        # Parser le JSON
        data = ujson.loads(msg)

        user = data.get('user_login', 'Anonyme')
        message = data.get('message', '')
        timestamp = data.get('timestamp', '')

        print(f"De: {user}")
        print(f"Message: {message}")
        print(f"Date: {timestamp}")

        # Afficher sur le LCD
        clear_lcd()
        display_message(user, message)

    except Exception as e:
        print(f"Erreur parsing JSON: {e}")
        display_text("Erreur format", row=0, center=True)
        time.sleep(2)

# ============================================
# CONNEXION MQTT
# ============================================

def connect_mqtt():
    """Se connecter au broker MQTT et s'abonner au topic"""
    print("Connexion MQTT...")
    display_text("MQTT...", row=0, center=True)

    try:
        client = MQTTClient(
            client_id=MQTT_CLIENT_ID,
            server=MQTT_BROKER,
            port=MQTT_PORT,
            user=MQTT_USERNAME,
            password=MQTT_PASSWORD,
            keepalive=60
        )

        client.set_callback(mqtt_callback)
        client.connect()
        client.subscribe(MQTT_TOPIC)

        print(f"MQTT connecté au broker {MQTT_BROKER}")
        print(f"Abonné au topic: {MQTT_TOPIC}")

        display_text("MQTT OK!", row=0, center=True)
        display_text("En attente...", row=1, center=True)
        time.sleep(2)

        return client

    except Exception as e:
        print(f"Erreur connexion MQTT: {e}")
        display_text("MQTT ERREUR!", row=0, center=True)
        display_text(str(e)[:LCD_COLS], row=1)
        time.sleep(3)
        return None

# ============================================
# ÉCRAN D'ACCUEIL
# ============================================

def show_welcome():
    """Affiche l'écran d'accueil"""
    clear_lcd()
    display_text("LIVRE D'OR", row=0, center=True)
    display_text("Initialisation", row=1, center=True)
    time.sleep(2)

# ============================================
# BOUCLE PRINCIPALE
# ============================================

def main():
    """Fonction principale"""
    # Afficher l'écran d'accueil
    show_welcome()

    # Connexion WiFi
    if not connect_wifi():
        display_text("REDEMARRAGE", row=0, center=True)
        display_text("dans 10s...", row=1, center=True)
        time.sleep(10)
        machine.reset()

    # Connexion MQTT
    client = connect_mqtt()
    if not client:
        display_text("REDEMARRAGE", row=0, center=True)
        display_text("dans 10s...", row=1, center=True)
        time.sleep(10)
        machine.reset()

    # Afficher le message d'attente
    clear_lcd()
    display_text("LIVRE D'OR", row=0, center=True)
    display_text("En ecoute...", row=1, center=True)

    # Boucle principale
    last_ping = time.time()
    ping_interval = 30  # Ping toutes les 30 secondes

    print("En attente de messages...")

    while True:
        try:
            # Vérifier les messages MQTT
            client.check_msg()

            # Ping périodique pour maintenir la connexion
            if time.time() - last_ping > ping_interval:
                try:
                    client.ping()
                    last_ping = time.time()
                except:
                    print("Erreur ping, reconnexion...")
                    client = connect_mqtt()
                    if not client:
                        machine.reset()

            time.sleep(0.1)

        except KeyboardInterrupt:
            print("\nArrêt demandé")
            display_text("ARRET", row=0, center=True)
            client.disconnect()
            break

        except Exception as e:
            print(f"Erreur: {e}")
            display_text("ERREUR!", row=0, center=True)
            display_text("Reconnexion...", row=1, center=True)
            time.sleep(5)

            # Tentative de reconnexion
            try:
                client = connect_mqtt()
                if not client:
                    machine.reset()
            except:
                machine.reset()

# ============================================
# DÉMARRAGE
# ============================================

if __name__ == "__main__":
    try:
        main()
    except Exception as e:
        print(f"Erreur fatale: {e}")
        display_text("ERREUR FATALE", row=0, center=True)
        time.sleep(5)
        machine.reset()
