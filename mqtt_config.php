<?php
// Configuration MQTT
define('MQTT_HOST', '192.168.10.166');
define('MQTT_PORT', 1883);
define('MQTT_CLIENT_ID', 'livre_or_' . uniqid());
define('MQTT_USERNAME', ''); // Remplir si authentification requise
define('MQTT_PASSWORD', ''); // Remplir si authentification requise

// Topics MQTT
define('MQTT_TOPIC_MESSAGES', 'livreor/messages');
define('MQTT_TOPIC_USERS', 'livreor/users');
