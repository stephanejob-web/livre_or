"""
Fichier de configuration pour l'ESP32
À copier sur l'ESP32 avec le nom: config.py
"""

# ============================================
# CONFIGURATION WIFI
# ============================================
WIFI_SSID = "VOTRE_SSID"
WIFI_PASSWORD = "VOTRE_PASSWORD"

# ============================================
# CONFIGURATION MQTT
# ============================================
MQTT_BROKER = "192.168.10.166"
MQTT_PORT = 1883
MQTT_TOPIC = b"livreor/messages"

# Authentification MQTT (optionnel)
# Laisser à None si pas d'authentification
MQTT_USERNAME = None
MQTT_PASSWORD = None

# ============================================
# CONFIGURATION LCD I2C
# ============================================
I2C_ADDR = 0x27
LCD_ROWS = 2
LCD_COLS = 16

# Pins I2C pour ESP32
I2C_SCL_PIN = 22
I2C_SDA_PIN = 21
I2C_FREQ = 10000
