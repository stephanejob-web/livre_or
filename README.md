# 📖 Livre d'Or

Un projet PHP de livre d'or permettant aux utilisateurs de s'inscrire, se connecter et laisser des commentaires.

## 🎯 Fonctionnalités

- ✅ Inscription des utilisateurs
- ✅ Connexion/Déconnexion sécurisée
- ✅ Gestion du profil utilisateur
- ✅ Ajout de commentaires (réservé aux utilisateurs connectés)
- ✅ Affichage des commentaires par ordre chronologique
- ✅ Design responsive et moderne
- ✅ Validation des formulaires côté client et serveur
- ✅ Sécurité : mots de passe hashés, protection contre les injections SQL

## 📁 Structure du projet

```
livre_or/
├── assets/
│   ├── css/
│   │   └── style.css          # Feuille de style principale
│   ├── js/
│   │   └── script.js           # Scripts JavaScript
│   └── images/                 # Dossier pour les images
├── config/
│   └── database.php            # Configuration de la base de données
├── includes/
│   ├── header.php              # En-tête commun
│   ├── footer.php              # Pied de page commun
│   └── functions.php           # Fonctions utilitaires
├── pages/
│   ├── inscription.php         # Formulaire d'inscription
│   ├── connexion.php           # Formulaire de connexion
│   ├── profil.php              # Page de profil
│   ├── livre-or.php            # Affichage du livre d'or
│   ├── commentaire.php         # Formulaire d'ajout de commentaire
│   └── deconnexion.php         # Déconnexion
├── index.php                   # Page d'accueil
├── livreor.sql                 # Script SQL de la base de données
└── README.md                   # Documentation
```

## 🚀 Installation

### Prérequis

- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Serveur web (Apache, Nginx)
- phpMyAdmin (optionnel)

### Étapes d'installation

1. **Cloner ou télécharger le projet**
   ```bash
   cd /var/www/html/
   ```

2. **Créer la base de données**
   - Ouvrez phpMyAdmin
   - Importez le fichier `livreor.sql`
   - Ou exécutez les commandes SQL directement :
     ```bash
     mysql -u root -p < livreor.sql
     ```

3. **Configurer la connexion à la base de données**
   - Ouvrez le fichier `config/database.php`
   - Modifiez les paramètres si nécessaire :
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'livreor');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     ```

4. **Lancer le serveur**
   - Avec Apache : http://localhost/livre_or/
   - Avec PHP built-in server :
     ```bash
     php -S localhost:8000
     ```

5. **Accéder à l'application**
   - Ouvrez votre navigateur : http://localhost/livre_or/

## 👥 Comptes de test

Des comptes de test sont inclus dans le fichier SQL :

- Login: `admin` | Mot de passe: `password123`
- Login: `jean.dupont` | Mot de passe: `password123`
- Login: `marie.martin` | Mot de passe: `password123`

## 🗄️ Base de données

### Table `utilisateurs`
- `id` : INT (clé primaire, auto-incrémentée)
- `login` : VARCHAR(255) (unique)
- `password` : VARCHAR(255) (hashé avec bcrypt)

### Table `commentaires`
- `id` : INT (clé primaire, auto-incrémentée)
- `commentaire` : TEXT
- `id_utilisateur` : INT (clé étrangère vers utilisateurs)
- `date` : DATETIME

## 🔒 Sécurité

- Mots de passe hashés avec `password_hash()` (bcrypt)
- Protection contre les injections SQL avec PDO et requêtes préparées
- Protection XSS avec `htmlspecialchars()`
- Validation des données côté serveur
- Sessions PHP sécurisées

## 🎨 Technologies utilisées

- **Backend** : PHP 7+
- **Base de données** : MySQL
- **Frontend** : HTML5, CSS3, JavaScript
- **Librairies** : PDO pour la base de données

## 📝 Pages disponibles

- `/index.php` : Page d'accueil
- `/pages/inscription.php` : Inscription
- `/pages/connexion.php` : Connexion
- `/pages/livre-or.php` : Consulter le livre d'or
- `/pages/commentaire.php` : Ajouter un commentaire (nécessite connexion)
- `/pages/profil.php` : Gérer son profil (nécessite connexion)
- `/pages/deconnexion.php` : Se déconnecter

## 🐛 Dépannage

### Erreur de connexion à la base de données
- Vérifiez que MySQL est démarré
- Vérifiez les identifiants dans `config/database.php`
- Assurez-vous que la base de données `livreor` existe

### Pages blanches
- Activez l'affichage des erreurs PHP :
  ```php
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  ```

### CSS/JS ne se chargent pas
- Vérifiez les chemins dans `includes/header.php`
- Vérifiez les permissions des dossiers

## 🤝 Contribution

Ce projet est un exercice pédagogique. N'hésitez pas à l'améliorer et à ajouter de nouvelles fonctionnalités !

## 📄 Licence

Ce projet est libre de droits et peut être utilisé à des fins éducatives.

---

Développé avec ❤️ pour l'apprentissage du PHP
