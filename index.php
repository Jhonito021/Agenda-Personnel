<?php
/**
 * Agenda Personnel et Gestion de Tâches pour Étudiants
 * Version simple pour débutants
 */

// Démarrer la session
session_start();

// Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'bdd_agenda_perso');
define('DB_USER', 'root');
define('DB_PASS', '');

// Connexion à la base de données
require_once __DIR__ . '/includes/db.php';


// Gérer le changement de thème
if (isset($_GET['action']) && $_GET['action'] === 'changer_theme') {
    if (isset($_SESSION['theme']) && $_SESSION['theme'] === 'clair') {
        $_SESSION['theme'] = 'sombre';
    } else {
        $_SESSION['theme'] = 'clair';
    }
    
    // Mettre à jour le thème dans la base de données si l'utilisateur est connecté
    if (isset($_SESSION['utilisateur_id'])) {
        $db = connectDB();
        $query = "UPDATE utilisateurs SET theme = :theme WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':theme', $_SESSION['theme']);
        $stmt->bindParam(':id', $_SESSION['utilisateur_id']);
        $stmt->execute();
    }
    
    // Rediriger vers la page d'origine
    $redirect_page = isset($_GET['page']) ? $_GET['page'] : 'accueil';
    header('Location: index.php?page=' . $redirect_page);
    exit;
}

// Définir la page par défaut
$page = isset($_GET['page']) ? $_GET['page'] : 'accueil';
$action = isset($_GET['action']) ? $_GET['action'] : '';

// En-tête HTML
include 'includes/header.php';

// Navigation
include 'includes/navigation.php';

// Contenu principal
switch ($page) {
    case 'accueil':
        include 'pages/accueil.php';
        break;
    
    case 'connexion':
        include 'pages/connexion.php';
        break;
    
    case 'inscription':
        include 'pages/inscription.php';
        break;
    
    case 'deconnexion':
        // On stocke temporairement le thème avant de détruire la session
        $theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'clair';
        
        // Détruire la session
        session_destroy();
        
        // Démarrer une nouvelle session pour conserver le thème
        session_start();
        $_SESSION['theme'] = $theme;
        
        // Message de déconnexion réussie
        $_SESSION['message'] = 'Vous avez été déconnecté avec succès.';
        $_SESSION['message_type'] = 'success';
        
        // Rediriger vers la page de connexion avec message et JS
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Déconnexion...</title>';
        echo '<meta http-equiv="refresh" content="1;url=index.php?page=connexion">';
        echo '<script>window.location.href = "index.php?page=connexion";</script>';
        echo '</head><body style="display:flex;align-items:center;justify-content:center;height:100vh;"><div style="text-align:center"><div class="spinner-border text-primary" role="status"></div><p class="mt-3">Déconnexion réussie ! Redirection...</p></div></body></html>';
        exit;
        break;
    
    case 'tableau_bord':
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['utilisateur_id'])) {
            header('Location: index.php?page=connexion');
            exit;
        }
        include 'pages/tableau_bord.php';
        break;
    
    case 'taches':
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['utilisateur_id'])) {
            header('Location: index.php?page=connexion');
            exit;
        }
        // Pas besoin de redirection spéciale pour l'action ajouter car c'est géré dans taches.php
        include 'pages/taches.php';
        break;
    
    case 'categories':
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['utilisateur_id'])) {
            header('Location: index.php?page=connexion');
            exit;
        }
        include 'pages/categories.php';
        break;
    
    case 'emploi_temps':
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['utilisateur_id'])) {
            header('Location: index.php?page=connexion');
            exit;
        }
        include 'pages/emploi_temps.php';
        break;
    
    case 'profil':
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['utilisateur_id'])) {
            header('Location: index.php?page=connexion');
            exit;
        }
        include 'pages/profil.php';
        break;
    
    default:
        include 'pages/accueil.php';
        break;
}

// Pied de page
include 'includes/footer.php';
?> 