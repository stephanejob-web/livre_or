"""
Version simplifiée du script ESP32 avec import de config.py
À renommer en main.py sur l'ESP32
"""

import machine
from machine import Pin, SoftI2C
from lcd_api import LcdApi
from i2c_lcd import I2cLcd
import network
import time
import ujson
from umqtt.simple import MQTTClient

# Importer la configuration
try:
    from config import *
except ImportError:
    print("ERREUR: Fichier config.py introuvable!")
    print("Créez config.py avec vos paramètres WiFi et MQTT")
    raise

# ============================================
# INITIALISATION LCD
# ============================================
i2c = SoftI2C(scl=Pin(I2C_SCL_PIN), sda=Pin(I2C_SDA_PIN), freq=I2C_FREQ)
lcd = I2cLcd(i2c, I2C_ADDR, LCD_ROWS, LCD_COLS)

def display_text(text, row=0, center=False):
    """Affiche du texte sur le LCD"""
    if center:
        text = text.center(LCD_COLS)
    text = text[:LCD_COLS].ljust(LCD_COLS)
    lcd.move_to(0, row)
    lcd.putstr(text)

def scroll_text(text, row=0):
    """Fait défiler un texte long"""
    if len(text) <= LCD_COLS:
        display_text(text, row)
        time.sleep(3)
        return

    text = " " * LCD_COLS + text + " " * LCD_COLS
    for i in range(len(text) - LCD_COLS + 1):
        lcd.move_to(0, row)
        lcd.putstr(text[i:i+LCD_COLS])
        time.sleep(0.3)

# ============================================
# CONNEXION WIFI
# ============================================
def connect_wifi():
    """Connexion WiFi"""
    display_text("WiFi...", row=0, center=True)
    wlan = network.WLAN(network.STA_IF)
    wlan.active(True)

    if not wlan.isconnected():
        wlan.connect(WIFI_SSID, WIFI_PASSWORD)
        timeout = 20
        while not wlan.isconnected() and timeout > 0:
            time.sleep(1)
            timeout -= 1

    if wlan.isconnected():
        print("WiFi OK:", wlan.ifconfig()[0])
        display_text("WiFi OK!", row=0, center=True)
        time.sleep(1)
        return True
    return False

# ============================================
# MQTT
# ============================================
def mqtt_callback(topic, msg):
    """Traite les messages MQTT reçus"""
    try:
        data = ujson.loads(msg)
        user = data.get('user_login', 'Anonyme')
        message = data.get('message', '')

        print(f"{user}: {message}")

        lcd.clear()
        display_text(user, row=0)
        scroll_text(message, row=1)
        time.sleep(1)

        lcd.clear()
        display_text("LIVRE D'OR", row=0, center=True)
        display_text("En ecoute...", row=1, center=True)

    except Exception as e:
        print(f"Erreur: {e}")

def connect_mqtt():
    """Connexion MQTT"""
    display_text("MQTT...", row=0, center=True)

    client = MQTTClient(
        client_id=b"esp32_" + str(machine.unique_id()),
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
    display_text("MQTT OK!", row=0, center=True)
    time.sleep(1)

    return client

# ============================================
# BOUCLE PRINCIPALE
# ============================================
def main():
    """Programme principal"""
    lcd.clear()
    display_text("LIVRE D'OR", row=0, center=True)
    display_text("Demarrage...", row=1, center=True)
    time.sleep(1)

    # Connexion WiFi
    if not connect_wifi():
        display_text("WiFi ERREUR!", row=0, center=True)
        time.sleep(5)
        machine.reset()

    # Connexion MQTT
    try:
        client = connect_mqtt()
    except Exception as e:
        print(f"MQTT Erreur: {e}")
        display_text("MQTT ERREUR!", row=0, center=True)
        time.sleep(5)
        machine.reset()

    # Mode écoute
    lcd.clear()
    display_text("LIVRE D'OR", row=0, center=True)
    display_text("En ecoute...", row=1, center=True)

    last_ping = time.time()

    while True:
        try:
            client.check_msg()

            # Ping toutes les 30s
            if time.time() - last_ping > 30:
                client.ping()
                last_ping = time.time()

            time.sleep(0.1)

        except KeyboardInterrupt:
            client.disconnect()
            break
        except Exception as e:
            print(f"Erreur: {e}")
            time.sleep(5)
            machine.reset()

# Démarrage
if __name__ == "__main__":
    main()
