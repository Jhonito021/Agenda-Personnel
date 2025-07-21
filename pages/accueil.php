<div class="container py-5">
    <!-- Affichage des messages de notification -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'info'; ?> alert-dismissible fade show glass-effect" role="alert">
            <?php 
                echo $_SESSION['message']; 
                // Nettoyer les messages après affichage
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Hero Section avec animations -->
    <div class="row align-items-center mb-5">
        <div class="col-lg-6 mb-4 mb-lg-0 animate__animated animate__fadeInLeft" style="animation-duration: 0.6s;">
            <h1 class="display-4 fw-bold">
                <span class="typewriter" id="hero-title">Organisez votre vie étudiante</span>
            </h1>
            <p class="lead mb-4 animate__animated animate__fadeIn animate__delay-05s">Un outil simple et efficace pour gérer vos tâches, cours et deadlines en un seul endroit.</p>
            
            <?php if (!isset($_SESSION['utilisateur_id'])): ?>
                <div class="d-flex gap-3 animate__animated animate__fadeIn animate__delay-05s">
                    <a href="index.php?page=connexion" class="btn btn-primary btn-lg px-4 animate__animated animate__pulse animate__infinite">
                        Connexion
                    </a>
                    <a href="index.php?page=inscription" class="btn btn-outline-primary btn-lg px-4">
                        Inscription
                    </a>
                </div>
            <?php else: ?>
                <a href="index.php?page=tableau_bord" class="btn btn-primary btn-lg px-4 animate__animated animate__pulse animate__infinite">
                    Accéder à mon tableau de bord
                </a>
            <?php endif; ?>
        </div>
        <div class="col-lg-6 text-center animate__animated animate__fadeInRight" style="animation-duration: 0.6s;">
            <!-- <img src="assets/img/calendar.svg" alt="Agenda" class="img-fluid floating" style="max-height: 300px;"> -->
        </div>
    </div>
    
    <!-- Features Section avec animations -->
    <div class="row mb-5">
        <div class="col-12 text-center mb-4">
            <h2 class="fw-bold animate__animated animate__fadeIn animate__delay-05s">Tout ce dont vous avez besoin</h2>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="feature-card p-4 text-center h-100 border rounded animate__animated animate__fadeInUp animate__delay-05s">
                <div class="feature-icon mb-3">
                    <i class="fas fa-tasks fa-2x text-primary"></i>
                </div>
                <h3 class="h5 mb-3">Gestion des tâches</h3>
                <p class="mb-0">Créez, organisez et suivez vos tâches par priorité et catégorie.</p>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="feature-card p-4 text-center h-100 border rounded animate__animated animate__fadeInUp animate__delay-1s">
                <div class="feature-icon mb-3">
                    <i class="fas fa-calendar-week fa-2x text-info"></i>
                </div>
                <h3 class="h5 mb-3">Emploi du temps</h3>
                <p class="mb-0">Visualisez votre semaine et gérez vos cours en un coup d'œil.</p>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="feature-card p-4 text-center h-100 border rounded animate__animated animate__fadeInUp animate__delay-15s">
                <div class="feature-icon mb-3">
                    <i class="fas fa-palette fa-2x text-success"></i>
                </div>
                <h3 class="h5 mb-3">Personnalisation</h3>
                <p class="mb-0">Adaptez l'interface à vos préférences avec le mode sombre/clair.</p>
            </div>
        </div>
    </div>
</div>

<!-- Script pour l'animation d'écriture -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation d'écriture pour le titre principal
    const titleElement = document.getElementById('hero-title');
    const originalText = titleElement.textContent;
    const phrases = [
        "Organisez votre vie étudiante",
        "Gérez vos tâches efficacement",
        "Planifiez votre emploi du temps",
        "Réussissez vos études"
    ];
    titleElement.textContent = '';
    
    // Vitesse de frappe variable pour un effet plus naturel mais plus rapide
    const typeSpeeds = [40, 50, 60, 70];
    const eraseSpeed = 20; // Vitesse d'effacement plus rapide
    const pauseBeforeErasing = 1500; // Pause avant d'effacer
    const pauseBeforeTyping = 500; // Pause avant de taper une nouvelle phrase
    
    let phraseIndex = 0;
    let charIndex = 0;
    let isDeleting = false;
    let isPaused = false;
    
    function typeLoop() {
        const currentPhrase = phrases[phraseIndex];
        
        if (isPaused) {
            setTimeout(typeLoop, isDeleting ? pauseBeforeTyping : pauseBeforeErasing);
            isPaused = false;
            return;
        }
        
        if (isDeleting) {
            // Effacer les caractères
            titleElement.textContent = currentPhrase.substring(0, charIndex - 1);
            charIndex--;
            
            // Si tout est effacé, passer à la phrase suivante
            if (charIndex === 0) {
                isDeleting = false;
                isPaused = true;
                phraseIndex = (phraseIndex + 1) % phrases.length;
            }
            
            setTimeout(typeLoop, eraseSpeed);
        } else {
            // Taper les caractères
            titleElement.textContent = currentPhrase.substring(0, charIndex + 1);
            charIndex++;
            
            // Si la phrase est complète, commencer à effacer après une pause
            if (charIndex === currentPhrase.length) {
                isDeleting = true;
                isPaused = true;
            }
            
            // Vitesse de frappe aléatoire pour un effet plus naturel
            const randomSpeed = typeSpeeds[Math.floor(Math.random() * typeSpeeds.length)];
            setTimeout(typeLoop, randomSpeed);
        }
    }
    
    // Démarrer l'animation immédiatement
    setTimeout(typeLoop, 400);
    
    // Accélérer les animations d'apparition
    document.querySelectorAll('.animate__animated').forEach(element => {
        // Réduire les délais d'animation de moitié
        if (element.classList.contains('animate__delay-1s')) {
            element.classList.remove('animate__delay-1s');
            element.classList.add('animate__delay-05s');
        } else if (element.classList.contains('animate__delay-2s')) {
            element.classList.remove('animate__delay-2s');
            element.classList.add('animate__delay-1s');
        } else if (element.classList.contains('animate__delay-3s')) {
            element.classList.remove('animate__delay-3s');
            element.classList.add('animate__delay-15s');
        } else if (element.classList.contains('animate__delay-4s')) {
            element.classList.remove('animate__delay-4s');
            element.classList.add('animate__delay-2s');
        }
        
        // Accélérer les animations elles-mêmes
        element.style.animationDuration = '0.6s';
    });
    
    // Animation de flottement pour l'image
    const floatingElements = document.querySelectorAll('.floating');
    floatingElements.forEach(element => {
        let amplitude = 8; // Amplitude du mouvement
        let speed = 0.2;   // Vitesse du mouvement (plus rapide)
        
        // Mouvement sinusoïdal pour un effet plus fluide
        let startTime = Date.now();
        
        function animateFloat() {
            const elapsed = Date.now() - startTime;
            const newPosition = amplitude * Math.sin(elapsed * speed / 1000);
            element.style.transform = `translateY(${newPosition}px)`;
            requestAnimationFrame(animateFloat);
        }
        
        animateFloat();
    });
    
    // Animation au survol des cartes de fonctionnalités
    const featureCards = document.querySelectorAll('.feature-card');
    featureCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.querySelector('.feature-icon').classList.add('animate__animated', 'animate__rubberBand');
        });
        
        card.addEventListener('mouseleave', function() {
            const icon = this.querySelector('.feature-icon');
            icon.classList.remove('animate__animated', 'animate__rubberBand');
        });
    });
});
</script>

<!-- Ajout de la bibliothèque Animate.css -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" /> 