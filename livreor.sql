-- Base de données pour le projet Livre d'Or
-- Auteur: Projet PHP Livre d'Or
-- Date: 2025-11-05

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS `livreor` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `livreor`;

-- ============================================================
-- Table: utilisateurs
-- Description: Stocke les informations des utilisateurs inscrits
-- ============================================================
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `login` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_login` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: commentaires
-- Description: Stocke les commentaires du livre d'or
-- ============================================================
CREATE TABLE IF NOT EXISTS `commentaires` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `commentaire` TEXT NOT NULL,
  `id_utilisateur` INT NOT NULL,
  `date` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_commentaires_utilisateurs` (`id_utilisateur`),
  CONSTRAINT `fk_commentaires_utilisateurs` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Insertion de données de test (optionnel)
-- ============================================================

-- Utilisateurs de test (mot de passe: "password123" pour tous)
INSERT INTO `utilisateurs` (`login`, `password`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('jean.dupont', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('marie.martin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Commentaires de test
INSERT INTO `commentaires` (`commentaire`, `id_utilisateur`, `date`) VALUES
('Excellent site ! Très bien conçu et facile à utiliser. J\'apprécie particulièrement le design moderne et épuré.', 1, '2025-11-05 10:30:00'),
('Merci pour ce livre d\'or ! C\'est une super initiative. L\'interface est intuitive et agréable.', 2, '2025-11-05 11:15:00'),
('Bravo pour ce projet ! Le système d\'inscription et de connexion fonctionne parfaitement. Continue comme ça !', 3, '2025-11-05 12:45:00'),
('J\'aime beaucoup pouvoir laisser mes commentaires en toute simplicité. Le site est rapide et réactif.', 1, '2025-11-05 14:20:00'),
('Une très belle réalisation ! J\'ai hâte de voir les prochaines fonctionnalités.', 2, '2025-11-05 15:10:00');

-- ============================================================
-- Index supplémentaires pour optimisation
-- ============================================================

-- Index sur la date pour l'affichage chronologique
CREATE INDEX idx_date ON `commentaires` (`date` DESC);

-- ============================================================
-- Affichage des statistiques
-- ============================================================

SELECT
    (SELECT COUNT(*) FROM utilisateurs) as nombre_utilisateurs,
    (SELECT COUNT(*) FROM commentaires) as nombre_commentaires;

-- ============================================================
-- Fin du script SQL
-- ============================================================
