<nav class="navbar navbar-expand-lg navbar-main glass-effect shadow rounded-4 my-3 position-relative">
    <div class="container-fluid px-3">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
            <i class="bi bi-calendar3 text-primary me-2 brand-icon"></i> <span>Agenda Étudiant</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-2">
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 py-2 <?php echo $page === 'accueil' ? 'active fw-bold bg-primary text-white' : ''; ?>" href="index.php">
                        <i class="bi bi-house-door-fill me-1 nav-icon"></i> Accueil
                    </a>
                </li>
                <?php if (isset($_SESSION['utilisateur_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-3 py-2 <?php echo $page === 'tableau_bord' ? 'active fw-bold bg-primary text-white' : ''; ?>" href="index.php?page=tableau_bord">
                            <i class="bi bi-speedometer2 me-1 nav-icon"></i> Tableau de bord
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle rounded-pill px-3 py-2 <?php echo $page === 'taches' ? 'active fw-bold bg-primary text-white' : ''; ?>" href="#" id="taskDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-list-task me-1 nav-icon"></i> Tâches
                        </a>
                        <ul class="dropdown-menu glass-effect border-0 shadow-sm">
                            <li><a class="dropdown-item py-2" href="index.php?page=taches"><i class="bi bi-list-ul me-2 dropdown-icon"></i>Liste des tâches</a></li>
                            <li><a class="dropdown-item py-2" href="index.php?page=taches&action=ajouter"><i class="bi bi-plus-lg me-2 dropdown-icon"></i>Ajouter une tâche</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-3 py-2 <?php echo $page === 'categories' ? 'active fw-bold bg-primary text-white' : ''; ?>" href="index.php?page=categories">
                            <i class="bi bi-tags-fill me-1 nav-icon"></i> Catégories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-3 py-2 <?php echo $page === 'emploi_temps' ? 'active fw-bold bg-primary text-white' : ''; ?>" href="index.php?page=emploi_temps">
                            <i class="bi bi-calendar-week me-1 nav-icon"></i> Emploi du temps
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav align-items-center gap-2">
                <?php if (isset($_SESSION['utilisateur_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center user-avatar" style="width:40px; height:40px; font-size:1.1em;">
                                <?php echo strtoupper(substr($_SESSION['utilisateur_prenom'], 0, 1)); ?>
                            </span>
                            <span class="fw-semibold d-none d-md-inline user-name"> <?php echo htmlspecialchars($_SESSION['utilisateur_prenom']); ?> </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end glass-effect border-0 shadow-sm">
                            <li><a class="dropdown-item py-2" href="index.php?page=profil"><i class="bi bi-person-gear me-2 dropdown-icon"></i> Profil</a></li>
                            <li><hr class="dropdown-divider opacity-25"></li>
                            <li><a class="dropdown-item py-2" href="index.php?page=deconnexion"><i class="bi bi-box-arrow-right me-2 dropdown-icon"></i> Déconnexion</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-3 py-2 <?php echo $page === 'connexion' ? 'active fw-bold bg-primary text-white' : ''; ?>" href="index.php?page=connexion">
                            <i class="bi bi-box-arrow-in-right me-1 nav-icon"></i> Connexion
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill px-3 py-2 <?php echo $page === 'inscription' ? 'active fw-bold bg-primary text-white' : ''; ?>" href="index.php?page=inscription">
                            <i class="bi bi-person-plus me-1 nav-icon"></i> Inscription
                        </a>
                    </li>
                <?php endif; ?>
                
                <!-- Bouton de changement de thème -->
                <li class="nav-item ms-2">
                    <a class="nav-link theme-toggle p-2 rounded-circle glass-effect" href="index.php?page=<?php echo $page; ?>&action=changer_theme" title="Changer de thème">
                        <?php if (isset($_SESSION['theme']) && $_SESSION['theme'] === 'sombre'): ?>
                            <i class="bi bi-sun theme-icon"></i>
                        <?php else: ?>
                            <i class="bi bi-moon theme-icon"></i>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
/* Styles spécifiques pour les animations de la navigation */
.brand-icon {
    transition: transform 0.3s ease;
}

.navbar-brand:hover .brand-icon {
    transform: rotate(15deg);
}

.nav-icon {
    transition: transform 0.3s ease;
}

.nav-link:hover .nav-icon {
    transform: scale(1.2);
}

.dropdown-icon {
    transition: transform 0.2s ease;
}

.dropdown-item:hover .dropdown-icon {
    transform: translateX(3px);
}

.user-avatar {
    transition: all 0.3s ease;
}

.nav-item.dropdown:hover .user-avatar {
    box-shadow: 0 0 10px rgba(13, 110, 253, 0.5);
}

.theme-icon {
    transition: all 0.3s ease;
}

.theme-toggle:hover .theme-icon {
    transform: rotate(30deg);
}

/* Styles spécifiques pour le mode sombre */
body.sombre .navbar-brand span {
    background: linear-gradient(to right, #f8f9fa, #adb5bd);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    text-shadow: 0 0 5px rgba(255, 255, 255, 0.1);
}

body.sombre .brand-icon {
    color: #0d6efd;
    text-shadow: 0 0 10px rgba(13, 110, 253, 0.5);
}

body.sombre .nav-icon {
    color: #0d6efd;
}

body.sombre .user-name {
    color: rgba(248, 249, 250, 0.9);
}
</style> 