<?php
session_start();
require_once '../includes/functions.php';

// Détruire la session
session_destroy();

// Rediriger vers la page de connexion avec un message
session_start();
setFlashMessage('success', 'Vous avez été déconnecté avec succès.');
redirect('/livre_or/pages/connexion.php');
?>
