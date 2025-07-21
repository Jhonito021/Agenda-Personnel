<!DOCTYPE html>
<html lang="fr" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda Personnel Étudiant</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg">
    <!-- Intégration de Bootstrap pour un style rapide (version locale) -->
    <link href="assets/vendor/bootstrap/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome pour les icônes (version locale) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="assets/vendor/bootstrap-icons/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Animate.css pour les animations (version locale) -->
    <link rel="stylesheet" href="assets/vendor/animate/animate.min.css">
    <!-- Styles personnalisés -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Styles additionnels pour le débogage -->
    <style>
        .debug-info {
            background-color: rgba(248, 215, 218, 0.8);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            padding: 10px;
            margin: 10px 0;
            border: 1px solid rgba(245, 198, 203, 0.5);
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        /* Ajout de la police Poppins pour tout le site */
        body {
            font-family: 'Poppins', sans-serif;
        }
        
        /* Animation pour le changement de thème */
        .theme-transition {
            animation: theme-fade 0.5s ease;
        }
        
        @keyframes theme-fade {
            0% { opacity: 0.7; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body class="<?php echo isset($_SESSION['theme']) ? $_SESSION['theme'] : 'clair'; ?> d-flex flex-column h-100">
    <!-- Overlay pour l'animation de changement de thème -->
    <div id="theme-overlay" class="position-fixed top-0 start-0 w-100 h-100" style="z-index: 9999; pointer-events: none; display: none;"></div>
    
    <div class="container flex-grow-1">
        <!-- Le header est supprimé car il est redondant avec le nouveau design de la page d'accueil -->

<script>
// Script pour animer le changement de thème
document.addEventListener('DOMContentLoaded', function() {
    // Vérifier si on vient de changer de thème
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('action') && urlParams.get('action') === 'changer_theme') {
        const overlay = document.getElementById('theme-overlay');
        overlay.style.display = 'block';
        overlay.style.backgroundColor = '<?php echo isset($_SESSION["theme"]) && $_SESSION["theme"] === "sombre" ? "#212529" : "#ffffff"; ?>';
        overlay.style.opacity = '0.8';
        
        setTimeout(() => {
            overlay.style.opacity = '0';
            setTimeout(() => {
                overlay.style.display = 'none';
            }, 500);
        }, 100);
        
        // Ajouter une classe d'animation au body
        document.body.classList.add('theme-transition');
        setTimeout(() => {
            document.body.classList.remove('theme-transition');
        }, 500);
    }
});
</script> 