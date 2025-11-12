# ESP32 - Affichage LCD des messages du Livre d'Or

## Description

Ce script permet d'afficher en temps réel les messages du livre d'or sur un écran LCD I2C connecté à un ESP32, via MQTT.

## Matériel requis

- **ESP32** (NodeMCU-32S ou équivalent)
- **Écran LCD I2C 16x2** (avec module PCF8574)
- **Câbles de connexion**
- **Alimentation** (USB ou 5V)

## Connexions

### LCD I2C vers ESP32

| LCD I2C | ESP32 |
|---------|-------|
| VCC     | 5V    |
| GND     | GND   |
| SDA     | GPIO 21 |
| SCL     | GPIO 22 |

## Installation

### 1. Flasher MicroPython sur l'ESP32

Si ce n'est pas déjà fait, flashez MicroPython sur votre ESP32 :

```bash
# Télécharger le firmware MicroPython
# https://micropython.org/download/esp32/

# Effacer la flash
esptool.py --port /dev/ttyUSB0 erase_flash

# Flasher MicroPython
esptool.py --chip esp32 --port /dev/ttyUSB0 write_flash -z 0x1000 esp32-*.bin
```

### 2. Installer les bibliothèques requises

Vous devez copier ces fichiers sur l'ESP32 :

#### Bibliothèque umqtt

Téléchargez `umqtt.simple` depuis :
- https://github.com/micropython/micropython-lib/tree/master/micropython/umqtt.simple

Ou installez avec upip :
```python
import upip
upip.install('micropython-umqtt.simple')
```

#### Bibliothèque LCD I2C

Téléchargez ces deux fichiers :
- `lcd_api.py` : https://github.com/dhylands/python_lcd/blob/master/lcd/lcd_api.py
- `i2c_lcd.py` : https://github.com/dhylands/python_lcd/blob/master/lcd/i2c_lcd.py

### 3. Copier les fichiers sur l'ESP32

Utilisez un outil comme **Thonny**, **ampy**, ou **rshell** pour copier les fichiers :

#### Avec Thonny (recommandé pour débutants)

1. Ouvrir Thonny
2. Aller dans *Run > Select interpreter*
3. Choisir "MicroPython (ESP32)"
4. Sélectionner le port COM de l'ESP32
5. Glisser-déposer les fichiers dans la vue "Files"

#### Avec ampy (ligne de commande)

```bash
# Installer ampy
pip install adafruit-ampy

# Copier les fichiers
ampy --port /dev/ttyUSB0 put lcd_api.py
ampy --port /dev/ttyUSB0 put i2c_lcd.py
ampy --port /dev/ttyUSB0 put config_esp32.py config.py
ampy --port /dev/ttyUSB0 put esp32.py main.py
```

### 4. Configuration

Modifiez le fichier `config_esp32.py` avec vos paramètres :

```python
# Configuration WiFi
WIFI_SSID = "VotreReseauWiFi"
WIFI_PASSWORD = "VotreMotDePasse"

# Configuration MQTT (déjà configuré pour votre serveur)
MQTT_BROKER = "192.168.10.166"
MQTT_PORT = 1883
MQTT_TOPIC = b"livreor/messages"

# Authentification MQTT (si nécessaire)
MQTT_USERNAME = None  # ou "votre_username"
MQTT_PASSWORD = None  # ou "votre_password"
```

Puis copiez-le sur l'ESP32 avec le nom `config.py`.

### 5. Fichiers à copier sur l'ESP32

Assurez-vous d'avoir ces fichiers sur l'ESP32 :

```
/
├── boot.py           (fichier par défaut MicroPython)
├── main.py           (votre esp32.py renommé)
├── config.py         (votre config_esp32.py renommé)
├── lcd_api.py        (bibliothèque LCD)
├── i2c_lcd.py        (bibliothèque LCD I2C)
└── umqtt/            (dossier de la bibliothèque MQTT)
    └── simple.py
```

## Utilisation

### Démarrage automatique

Le script `main.py` se lance automatiquement au démarrage de l'ESP32.

### Séquence de démarrage

1. **Écran d'accueil** : "LIVRE D'OR / Initialisation"
2. **Connexion WiFi** : "WiFi..." puis "WiFi OK!" avec l'adresse IP
3. **Connexion MQTT** : "MQTT..." puis "MQTT OK!"
4. **Attente** : "LIVRE D'OR / En ecoute..."

### Affichage des messages

Lorsqu'un message est reçu :

```
┌────────────────┐
│ darksh3ll      │  ← Ligne 1: Nom de l'utilisateur
│ Mon message... │  ← Ligne 2: Message (défile si > 16 caractères)
└────────────────┘
```

Le message reste affiché 3-5 secondes puis revient en mode attente.

## Fonctionnalités

### Défilement automatique

Si le message dépasse 16 caractères, il défile automatiquement de droite à gauche.

### Reconnexion automatique

En cas de perte de connexion WiFi ou MQTT, l'ESP32 tente automatiquement de se reconnecter.

### Ping MQTT

Un ping est envoyé toutes les 30 secondes pour maintenir la connexion active.

## Débogage

### Voir les logs via le port série

```bash
# Avec screen
screen /dev/ttyUSB0 115200

# Avec minicom
minicom -D /dev/ttyUSB0 -b 115200

# Avec Thonny (le plus simple)
# Ouvrir Thonny > View > Shell
```

### Messages de débogage

Le script affiche ces informations sur le port série :

```
Connexion WiFi...
WiFi connecté!
IP: 192.168.1.100
Connexion MQTT...
MQTT connecté au broker 192.168.10.166
Abonné au topic: livreor/messages
En attente de messages...
Message reçu sur b'livreor/messages': {...}
De: darksh3ll
Message: Super site !
Date: 2025-11-06 15:30:00
```

### Problèmes courants

#### LCD ne s'affiche pas

1. Vérifier l'adresse I2C du LCD :
   ```python
   from machine import Pin, SoftI2C
   i2c = SoftI2C(scl=Pin(22), sda=Pin(21), freq=10000)
   print(i2c.scan())  # Affiche [39] si l'adresse est 0x27
   ```

2. Ajuster le contraste du LCD (potentiomètre au dos du module I2C)

3. Vérifier les connexions

#### Erreur de connexion WiFi

- Vérifier SSID et mot de passe
- Vérifier que l'ESP32 est à portée du WiFi
- S'assurer que le réseau est en 2.4 GHz (l'ESP32 ne supporte pas le 5 GHz)

#### Erreur de connexion MQTT

- Vérifier que le broker MQTT est accessible : `ping 192.168.10.166`
- Vérifier que le port 1883 est ouvert
- Tester avec mosquitto_sub sur un autre appareil

#### Redémarrage en boucle

- Vérifier l'alimentation (l'ESP32 a besoin de suffisamment de courant)
- Regarder les logs sur le port série pour identifier l'erreur

## Personnalisation

### Modifier le délai de défilement

Dans `esp32.py`, ligne ~131 :
```python
scroll_text(message, row=1, delay=0.3)  # 0.3 = vitesse de défilement
```

### Modifier le temps d'affichage

Dans `esp32.py`, ligne ~135 :
```python
time.sleep(3)  # 3 secondes d'affichage
```

### Changer les pins I2C

Dans `config_esp32.py` :
```python
I2C_SCL_PIN = 22  # Changer le pin SCL
I2C_SDA_PIN = 21  # Changer le pin SDA
```

### S'abonner à d'autres topics

Dans `config_esp32.py` :
```python
MQTT_TOPIC = b"livreor/users"  # Pour les événements utilisateurs
```

## Test avec le serveur

Pour tester l'affichage, envoyez un message depuis le serveur :

```bash
# Depuis le serveur web
php send_last_message.php 1

# Ou publiez directement avec mosquitto
mosquitto_pub -h 192.168.10.166 -t "livreor/messages" -m '{"user_login":"Test","message":"Message de test"}'
```

## Améliorations possibles

- Afficher l'heure du message
- Ajouter un bouton pour forcer le rafraîchissement
- Ajouter des LEDs pour indiquer l'état de connexion
- Utiliser un écran LCD 20x4 pour plus d'informations
- Ajouter un buzzer pour signaler l'arrivée de nouveaux messages
- Créer un mode économie d'énergie (deep sleep)

## Liens utiles

- MicroPython : https://micropython.org/
- ESP32 Pinout : https://randomnerdtutorials.com/esp32-pinout-reference-gpios/
- LCD I2C Library : https://github.com/dhylands/python_lcd
- umqtt : https://github.com/micropython/micropython-lib/tree/master/micropython/umqtt.simple

## Support

En cas de problème, vérifiez :
1. Les logs sur le port série
2. La connexion réseau (WiFi et MQTT)
3. Les connexions physiques (LCD I2C)
4. La configuration dans `config.py`
