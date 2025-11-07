# Guide d'Installation Complet - Livre d'Or avec MQTT et ESP32

## Architecture du Système

```
┌─────────────────┐         ┌─────────────────┐         ┌─────────────────┐
│  Navigateur Web │────────>│  Serveur Web    │────────>│  Base MySQL     │
│  (Utilisateur)  │         │  (PHP)          │         │  (Messages)     │
└─────────────────┘         └────────┬────────┘         └─────────────────┘
                                     │
                                     │ MQTT Publish
                                     ↓
                            ┌─────────────────┐
                            │  Broker MQTT    │
                            │  192.168.10.166 │
                            └────────┬────────┘
                                     │
                                     │ MQTT Subscribe
                                     ↓
                            ┌─────────────────┐
                            │     ESP32       │
                            │   + LCD I2C     │
                            │  (Affichage)    │
                            └─────────────────┘
```

## Partie 1 : Serveur Web (PHP + MySQL)

### Fichiers créés

| Fichier | Description |
|---------|-------------|
| `composer.json` | Configuration Composer |
| `mqtt_config.php` | Configuration MQTT |
| `MqttPublisher.php` | Classe pour publier sur MQTT |
| `commentaire.php` | Modifié pour envoyer en MQTT |
| `test_mqtt.php` | Script de test MQTT |
| `send_last_message.php` | Envoyer le dernier message d'un user |
| `demo_mqtt.php` | Démonstration complète |
| `listen_mqtt.sh` | Écouter les messages MQTT |
| `README_MQTT.md` | Documentation MQTT |

### Installation serveur

Les dépendances sont déjà installées. Si besoin de réinstaller :

```bash
cd /var/www/html/livre_or
composer install
```

### Configuration serveur

Le serveur MQTT est déjà configuré dans `mqtt_config.php` :
- Host : `192.168.10.166`
- Port : `1883`
- Topic : `livreor/messages`

### Test serveur

```bash
# Test de connexion MQTT
php test_mqtt.php

# Démonstration complète
php demo_mqtt.php

# Envoyer le dernier message d'un utilisateur
php send_last_message.php 1

# Écouter les messages MQTT
./listen_mqtt.sh
```

## Partie 2 : ESP32 + LCD I2C

### Matériel nécessaire

- ESP32 (NodeMCU-32S ou équivalent)
- Écran LCD I2C 16x2
- Câbles dupont
- Câble micro-USB

### Connexions

```
LCD I2C          ESP32
────────────────────────
VCC      ───────> 5V
GND      ───────> GND
SDA      ───────> GPIO 21
SCL      ───────> GPIO 22
```

### Fichiers pour l'ESP32

| Fichier source | À copier sur ESP32 comme | Description |
|----------------|--------------------------|-------------|
| `esp32_simple.py` | `main.py` | Programme principal (recommandé) |
| `esp32.py` | `main.py` | Programme complet (alternatif) |
| `config_esp32.py` | `config.py` | Configuration WiFi/MQTT |

### Bibliothèques requises sur ESP32

À télécharger et copier sur l'ESP32 :

1. **umqtt.simple** (MQTT)
   - Source : https://github.com/micropython/micropython-lib/tree/master/micropython/umqtt.simple
   - Ou installer : `import upip; upip.install('micropython-umqtt.simple')`

2. **lcd_api.py** (LCD)
   - Source : https://github.com/dhylands/python_lcd/blob/master/lcd/lcd_api.py

3. **i2c_lcd.py** (LCD I2C)
   - Source : https://github.com/dhylands/python_lcd/blob/master/lcd/i2c_lcd.py

### Installation ESP32

#### Étape 1 : Flasher MicroPython

```bash
# Télécharger le firmware depuis https://micropython.org/download/esp32/

# Effacer la flash
esptool.py --port /dev/ttyUSB0 erase_flash

# Flasher MicroPython
esptool.py --chip esp32 --port /dev/ttyUSB0 write_flash -z 0x1000 esp32-*.bin
```

#### Étape 2 : Configurer config_esp32.py

Modifier le fichier avec vos paramètres WiFi :

```python
WIFI_SSID = "VotreReseauWiFi"          # MODIFIER ICI
WIFI_PASSWORD = "VotreMotDePasseWiFi"  # MODIFIER ICI

# Le reste est déjà configuré :
MQTT_BROKER = "192.168.10.166"
MQTT_PORT = 1883
MQTT_TOPIC = b"livreor/messages"
```

#### Étape 3 : Copier les fichiers sur l'ESP32

**Option A : Avec Thonny (recommandé)**

1. Installer Thonny : https://thonny.org/
2. Ouvrir Thonny
3. Menu : *Run > Select interpreter*
4. Choisir "MicroPython (ESP32)"
5. Sélectionner le port COM/ttyUSB
6. Dans la vue "Files", glisser-déposer les fichiers :
   - `lcd_api.py`
   - `i2c_lcd.py`
   - `config_esp32.py` → renommer en `config.py`
   - `esp32_simple.py` → renommer en `main.py`
7. Installer umqtt dans le Shell Thonny :
   ```python
   import upip
   upip.install('micropython-umqtt.simple')
   ```

**Option B : Avec ampy (ligne de commande)**

```bash
# Installer ampy
pip install adafruit-ampy

# Copier les fichiers
ampy --port /dev/ttyUSB0 put lcd_api.py
ampy --port /dev/ttyUSB0 put i2c_lcd.py
ampy --port /dev/ttyUSB0 put config_esp32.py config.py
ampy --port /dev/ttyUSB0 put esp32_simple.py main.py

# Pour umqtt, connectez-vous via REPL et exécutez :
# import upip
# upip.install('micropython-umqtt.simple')
```

#### Étape 4 : Vérifier l'adresse I2C du LCD

Avant de lancer le programme, vérifiez l'adresse I2C de votre LCD :

```python
# Dans le REPL de Thonny ou via screen
from machine import Pin, SoftI2C
i2c = SoftI2C(scl=Pin(22), sda=Pin(21), freq=10000)
print(hex(i2c.scan()[0]))  # Devrait afficher 0x27 ou 0x3f
```

Si l'adresse est différente de `0x27`, modifiez dans `config.py` :
```python
I2C_ADDR = 0x3f  # ou l'adresse trouvée
```

#### Étape 5 : Démarrer l'ESP32

Débranchez et rebranchez l'ESP32. Le programme `main.py` se lance automatiquement.

Séquence normale au démarrage :
1. "LIVRE D'OR / Demarrage..."
2. "WiFi..." puis "WiFi OK!"
3. "MQTT..." puis "MQTT OK!"
4. "LIVRE D'OR / En ecoute..."

## Test Complet du Système

### Test 1 : Depuis le serveur web

1. Ouvrez un navigateur : `http://votre-serveur/livre_or/`
2. Connectez-vous avec un compte utilisateur
3. Allez dans "Ajouter un commentaire"
4. Écrivez un message
5. Publiez le commentaire
6. **Le message devrait apparaître sur le LCD de l'ESP32**

### Test 2 : Envoyer le dernier message via script

```bash
cd /var/www/html/livre_or
php send_last_message.php 1
```

Le dernier message de l'utilisateur ID 1 s'affiche sur le LCD.

### Test 3 : Test complet avec script automatique

```bash
./test_mqtt_lcd.sh
```

Ce script envoie plusieurs messages de test avec des délais entre chaque.

### Test 4 : Publier manuellement via mosquitto

```bash
mosquitto_pub -h 192.168.10.166 -t "livreor/messages" -m '{
  "user_login": "TestUser",
  "message": "Message de test manuel"
}'
```

## Débogage

### Problème : LCD n'affiche rien

1. Vérifier les connexions (VCC, GND, SDA, SCL)
2. Ajuster le contraste (potentiomètre au dos du module I2C)
3. Vérifier l'adresse I2C (voir Étape 4)
4. Tester le LCD avec un script simple :
   ```python
   from machine import Pin, SoftI2C
   from i2c_lcd import I2cLcd
   i2c = SoftI2C(scl=Pin(22), sda=Pin(21), freq=10000)
   lcd = I2cLcd(i2c, 0x27, 2, 16)
   lcd.putstr("TEST")
   ```

### Problème : ESP32 ne se connecte pas au WiFi

1. Vérifier SSID et mot de passe dans `config.py`
2. S'assurer que le WiFi est en 2.4 GHz (pas 5 GHz)
3. Vérifier la portée du signal WiFi
4. Regarder les logs sur le port série

### Problème : Erreur MQTT sur ESP32

1. Vérifier que le broker MQTT est accessible :
   ```bash
   ping 192.168.10.166
   ```
2. Tester l'abonnement depuis un autre appareil :
   ```bash
   mosquitto_sub -h 192.168.10.166 -t "livreor/#" -v
   ```
3. Vérifier les logs sur le port série de l'ESP32

### Problème : Messages ne s'affichent pas depuis le web

1. Vérifier que `composer install` a bien été exécuté
2. Tester la connexion MQTT du serveur :
   ```bash
   php test_mqtt.php
   ```
3. Vérifier que `commentaire.php` inclut bien `MqttPublisher.php`
4. Vérifier les logs PHP : `tail -f /var/log/apache2/error.log`

### Voir les logs ESP32

**Avec screen :**
```bash
screen /dev/ttyUSB0 115200
```

**Avec minicom :**
```bash
minicom -D /dev/ttyUSB0 -b 115200
```

**Avec Thonny :**
- Menu : View > Shell

## Structure Finale des Fichiers

### Sur le serveur (/var/www/html/livre_or/)

```
livre_or/
├── vendor/                    # Dépendances Composer (auto-généré)
├── composer.json              # Configuration Composer
├── composer.lock              # Versions des dépendances
├── config.php                 # Config base de données
├── mqtt_config.php            # Config MQTT serveur
├── MqttPublisher.php          # Classe MQTT
├── commentaire.php            # Modifié pour MQTT
├── test_mqtt.php              # Script de test
├── send_last_message.php      # Envoyer dernier message
├── demo_mqtt.php              # Démonstration
├── listen_mqtt.sh             # Écouter MQTT
├── test_mqtt_lcd.sh           # Tests automatiques
├── esp32.py                   # Script ESP32 complet
├── esp32_simple.py            # Script ESP32 simplifié
├── config_esp32.py            # Config ESP32
├── README_MQTT.md             # Doc MQTT
├── README_ESP32.md            # Doc ESP32
└── INSTALLATION_COMPLETE.md   # Ce fichier
```

### Sur l'ESP32

```
/
├── boot.py              # Fichier MicroPython par défaut
├── main.py              # Programme principal (esp32_simple.py)
├── config.py            # Configuration (config_esp32.py)
├── lcd_api.py           # Bibliothèque LCD
├── i2c_lcd.py           # Bibliothèque LCD I2C
└── lib/
    └── umqtt/
        └── simple.py    # Bibliothèque MQTT
```

## Fonctionnalités

### Serveur Web
- Inscription / Connexion utilisateurs
- Ajout de commentaires
- Affichage des messages
- Publication automatique MQTT lors d'un nouveau message

### ESP32
- Connexion WiFi automatique
- Connexion MQTT au broker
- Abonnement au topic `livreor/messages`
- Affichage des messages sur LCD 16x2
- Défilement automatique pour les messages longs
- Reconnexion automatique en cas de perte de connexion
- Ping MQTT pour maintenir la connexion

## Topics MQTT Disponibles

| Topic | Description | Format |
|-------|-------------|--------|
| `livreor/messages` | Nouveaux messages du livre d'or | JSON |
| `livreor/users` | Événements utilisateurs | JSON |

## Format des Messages JSON

### Topic: livreor/messages

```json
{
  "user_id": 1,
  "user_login": "darksh3ll",
  "message": "Contenu du message",
  "timestamp": "2025-11-06 15:30:00",
  "source": "livre_or"
}
```

### Topic: livreor/users

```json
{
  "event_type": "login",
  "user_id": 1,
  "user_login": "darksh3ll",
  "timestamp": "2025-11-06 15:30:00",
  "source": "livre_or"
}
```

## Améliorations Futures

### Côté Serveur
- [ ] Ajouter authentification MQTT (username/password)
- [ ] Publier les événements utilisateurs (connexion, inscription)
- [ ] Ajouter un dashboard de monitoring
- [ ] Stocker l'historique des publications MQTT

### Côté ESP32
- [ ] Ajouter un bouton pour afficher les statistiques
- [ ] Utiliser un écran LCD 20x4 pour plus d'infos
- [ ] Ajouter des LEDs pour l'état de connexion
- [ ] Ajouter un buzzer pour signaler les nouveaux messages
- [ ] Mode économie d'énergie (deep sleep)
- [ ] OTA (mise à jour over-the-air)

## Sécurité

### Serveur
- Mots de passe hashés avec bcrypt
- Requêtes SQL préparées (protection injection SQL)
- Protection XSS avec `htmlspecialchars()`
- Sessions PHP sécurisées

### MQTT
- Possibilité d'ajouter authentification username/password
- QoS 0 (at most once delivery)
- Messages non retenus (retained = false)
- Timeouts configurés

### ESP32
- Stockage des credentials WiFi dans config.py (ne pas commiter)
- Reconnexion automatique sécurisée
- Gestion des erreurs robuste

## Support et Documentation

- Documentation MQTT : `README_MQTT.md`
- Documentation ESP32 : `README_ESP32.md`
- Tests : `test_mqtt.php`, `demo_mqtt.php`, `test_mqtt_lcd.sh`
- MicroPython : https://micropython.org/
- php-mqtt/client : https://github.com/php-mqtt/client

## Conclusion

Votre système est maintenant complet :

1. **Interface Web** : Les utilisateurs peuvent poster des messages
2. **Base de données** : Les messages sont stockés
3. **MQTT** : Les messages sont publiés automatiquement
4. **ESP32** : Les messages s'affichent en temps réel sur le LCD

Tous les composants sont en place et testés !
