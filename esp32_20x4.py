"""
Script ESP32 pour LCD 20x4 - Version sans gestion WiFi
À utiliser si vous avez déjà un fichier de connexion WiFi séparé

Prérequis:
- WiFi déjà connecté
- LCD I2C 20x4
- umqtt.simple installé
"""

from machine import Pin, SoftI2C
from lcd_api import LcdApi
from i2c_lcd import I2cLcd
import time
import ujson
from umqtt.simple import MQTTClient
import machine

# ============================================
# CONFIGURATION
# ============================================

# Configuration MQTT
MQTT_BROKER = "192.168.10.166"
MQTT_PORT = 1883
MQTT_CLIENT_ID = "esp32_lcd_" + str(machine.unique_id())
MQTT_TOPIC = b"livreor/messages"
MQTT_USERNAME = None
MQTT_PASSWORD = None

# Configuration LCD I2C 20x4
I2C_ADDR = 0x27
LCD_ROWS = 4
LCD_COLS = 20

# Pins I2C
I2C_SCL_PIN = 22
I2C_SDA_PIN = 21
I2C_FREQ = 10000

# ============================================
# INITIALISATION LCD
# ============================================

i2c = SoftI2C(scl=Pin(I2C_SCL_PIN), sda=Pin(I2C_SDA_PIN), freq=I2C_FREQ)
lcd = I2cLcd(i2c, I2C_ADDR, LCD_ROWS, LCD_COLS)

# ============================================
# FONCTIONS LCD
# ============================================

def clear_lcd():
    """Efface l'écran LCD"""
    lcd.clear()

def display_text(text, row=0, center=False):
    """
    Affiche du texte sur une ligne du LCD

    Args:
        text: Texte à afficher
        row: Numéro de ligne (0 à 3)
        center: Centrer le texte
    """
    if center:
        padding = (LCD_COLS - len(text)) // 2
        text = " " * padding + text

    text = text[:LCD_COLS].ljust(LCD_COLS)
    lcd.move_to(0, row)
    lcd.putstr(text)

def display_separator(row):
    """Affiche une ligne de séparation"""
    lcd.move_to(0, row)
    lcd.putstr("-" * LCD_COLS)

def wrap_text(text, max_width):
    """
    Découpe un texte en plusieurs lignes

    Args:
        text: Texte à découper
        max_width: Largeur maximale par ligne

    Returns:
        Liste de lignes
    """
    words = text.split()
    lines = []
    current_line = ""

    for word in words:
        if len(current_line) + len(word) + 1 <= max_width:
            if current_line:
                current_line += " " + word
            else:
                current_line = word
        else:
            if current_line:
                lines.append(current_line)
            current_line = word

    if current_line:
        lines.append(current_line)

    return lines

def display_message(user, message, timestamp=""):
    """
    Affiche un message du livre d'or sur le LCD 20x4

    Format:
    Ligne 0: "LIVRE D'OR" (centré)
    Ligne 1: Nom de l'utilisateur + date/heure
    Ligne 2: --------------------
    Ligne 3: Message (ou début du message)

    Args:
        user: Nom de l'utilisateur
        message: Contenu du message
        timestamp: Date et heure (optionnel)
    """
    clear_lcd()

    # Ligne 0: Titre
    display_text("LIVRE D'OR", row=0, center=True)

    # Ligne 1: Utilisateur + timestamp
    if timestamp:
        # Extraire juste l'heure (HH:MM)
        time_part = timestamp[11:16] if len(timestamp) >= 16 else ""
        header = f"{user[:12]} {time_part}"
    else:
        header = user[:LCD_COLS]

    display_text(header, row=1, center=False)

    # Ligne 2: Séparateur
    display_separator(row=2)

    # Ligne 3: Message
    # Pour un LCD 20x4, on peut afficher le message sur 1 ligne
    # Si le message est trop long, on le découpe
    lines = wrap_text(message, LCD_COLS)

    if len(lines) > 0:
        display_text(lines[0], row=3, center=False)

    # Si le message est long, on fait défiler après un délai
    if len(lines) > 1:
        time.sleep(2)
        for i, line in enumerate(lines[1:], start=1):
            if i < 2:  # On ne peut afficher qu'une ligne supplémentaire
                time.sleep(1.5)
                display_text(line, row=3, center=False)

def scroll_long_message(user, message, timestamp=""):
    """
    Affiche un message long avec défilement vertical

    Format pour messages longs:
    Ligne 0: LIVRE D'OR
    Ligne 1: Utilisateur + heure
    Ligne 2: --------------------
    Ligne 3: Message ligne 1
    (puis défile les autres lignes)
    """
    clear_lcd()

    # En-tête fixe
    display_text("LIVRE D'OR", row=0, center=True)

    time_part = timestamp[11:16] if len(timestamp) >= 16 else ""
    header = f"{user[:12]} {time_part}"
    display_text(header, row=1, center=False)
    display_separator(row=2)

    # Découper le message en lignes
    lines = wrap_text(message, LCD_COLS)

    # Afficher chaque ligne avec défilement
    for line in lines:
        display_text(line, row=3, center=False)
        time.sleep(1.5)

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
    print(f"Message reçu: {msg}")

    try:
        # Parser le JSON
        data = ujson.loads(msg)

        user = data.get('user_login', 'Anonyme')
        message = data.get('message', '')
        timestamp = data.get('timestamp', '')

        print(f"De: {user}")
        print(f"Message: {message}")
        print(f"Heure: {timestamp}")

        # Afficher sur le LCD
        if len(message) > LCD_COLS:
            scroll_long_message(user, message, timestamp)
        else:
            display_message(user, message, timestamp)

        # Pause avant de revenir à l'écran d'attente
        time.sleep(3)

        # Retour à l'écran d'attente
        show_waiting_screen()

    except Exception as e:
        print(f"Erreur: {e}")
        clear_lcd()
        display_text("ERREUR FORMAT", row=1, center=True)
        time.sleep(2)
        show_waiting_screen()

# ============================================
# CONNEXION MQTT
# ============================================

def connect_mqtt():
    """Se connecter au broker MQTT"""
    print("Connexion MQTT...")

    display_text("Connexion MQTT...", row=1, center=True)
    display_text(MQTT_BROKER, row=2, center=True)

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

        print(f"MQTT OK: {MQTT_BROKER}")
        print(f"Topic: {MQTT_TOPIC}")

        clear_lcd()
        display_text("MQTT CONNECTE!", row=1, center=True)
        time.sleep(2)

        return client

    except Exception as e:
        print(f"Erreur MQTT: {e}")
        clear_lcd()
        display_text("ERREUR MQTT!", row=1, center=True)
        display_text(str(e)[:LCD_COLS], row=2, center=True)
        time.sleep(3)
        return None

# ============================================
# ÉCRANS
# ============================================

def show_startup_screen():
    """Écran de démarrage"""
    clear_lcd()
    display_text("====================", row=0)
    display_text("LIVRE D'OR", row=1, center=True)
    display_text("Affichage MQTT", row=2, center=True)
    display_text("====================", row=3)
    time.sleep(2)

def show_waiting_screen():
    """Écran en attente de messages"""
    clear_lcd()
    display_text("LIVRE D'OR", row=0, center=True)
    display_separator(row=1)
    display_text("En attente de", row=2, center=True)
    display_text("messages...", row=3, center=True)

# ============================================
# BOUCLE PRINCIPALE
# ============================================

def main():
    """Programme principal"""

    # Écran de démarrage
    show_startup_screen()

    # Note: On suppose que le WiFi est déjà connecté
    # par un autre fichier (ex: boot.py ou connexion.py)

    # Connexion MQTT
    client = connect_mqtt()

    if not client:
        clear_lcd()
        display_text("IMPOSSIBLE DE", row=1, center=True)
        display_text("SE CONNECTER!", row=2, center=True)
        return

    # Écran d'attente
    show_waiting_screen()

    # Variables pour le ping
    last_ping = time.time()
    ping_interval = 30

    print("En attente de messages MQTT...")

    # Boucle principale
    while True:
        try:
            # Vérifier les nouveaux messages
            client.check_msg()

            # Ping périodique pour maintenir la connexion
            if time.time() - last_ping > ping_interval:
                try:
                    client.ping()
                    last_ping = time.time()
                    print("Ping MQTT OK")
                except:
                    print("Erreur ping, reconnexion...")
                    client = connect_mqtt()
                    if not client:
                        break

            time.sleep(0.1)

        except KeyboardInterrupt:
            print("\nArrêt demandé")
            clear_lcd()
            display_text("ARRET", row=1, center=True)
            client.disconnect()
            break

        except Exception as e:
            print(f"Erreur: {e}")
            clear_lcd()
            display_text("ERREUR!", row=1, center=True)
            display_text("Reconnexion...", row=2, center=True)
            time.sleep(3)

            # Tentative de reconnexion
            client = connect_mqtt()
            if client:
                show_waiting_screen()
            else:
                break

# ============================================
# DÉMARRAGE
# ============================================

if __name__ == "__main__":
    try:
        main()
    except Exception as e:
        print(f"Erreur fatale: {e}")
        clear_lcd()
        display_text("ERREUR FATALE", row=1, center=True)
        display_text(str(e)[:LCD_COLS], row=2, center=True)
        time.sleep(5)
