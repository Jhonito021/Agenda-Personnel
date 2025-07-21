/**
 * Script principal pour l'Agenda Personnel et Gestion de Tâches
 */

// Attendre que le DOM soit complètement chargé
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des tooltips Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Correction pour les menus déroulants qui passent sous les tableaux
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(dropdown => {
        // Corriger le positionnement du menu déroulant
        const dropdownToggle = dropdown.querySelector('.dropdown-toggle');
        const dropdownMenu = dropdown.querySelector('.dropdown-menu');
        
        if (dropdownToggle && dropdownMenu) {
            // Initialiser correctement le dropdown de Bootstrap
            new bootstrap.Dropdown(dropdownToggle);
            
            // Positionner correctement le menu déroulant
            dropdownToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Calculer la position
                const rect = dropdownToggle.getBoundingClientRect();
                
                // Appliquer le positionnement
                if (dropdownMenu.classList.contains('dropdown-menu-end')) {
                    dropdownMenu.style.left = 'auto';
                    dropdownMenu.style.right = '0';
                } else {
                    dropdownMenu.style.left = '0';
                    dropdownMenu.style.right = 'auto';
                }
                
                // Ajuster la position verticale
                dropdownMenu.style.top = `${rect.height}px`;
                
                // Afficher/masquer le menu
                if (dropdownMenu.classList.contains('show')) {
                    dropdownMenu.classList.remove('show');
                } else {
                    // Fermer tous les autres menus déroulants
                    document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                        menu.classList.remove('show');
                    });
                    dropdownMenu.classList.add('show');
                }
            });
        }
        
        dropdown.addEventListener('show.bs.dropdown', function() {
            // Augmenter temporairement le z-index de ce dropdown spécifique
            this.style.zIndex = "1050";
        });
        
        dropdown.addEventListener('hide.bs.dropdown', function() {
            // Restaurer le z-index normal
            setTimeout(() => {
                this.style.zIndex = "";
            }, 200);
        });
    });
    
    // Fermer les menus déroulants lors d'un clic en dehors
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });

    // Gestion des templates de tâches
    const templateButtons = document.querySelectorAll('.template-btn');
    templateButtons.forEach(button => {
        button.addEventListener('click', function() {
            try {
                const templateData = JSON.parse(this.dataset.template);
                
                // Remplir le formulaire avec les données du template
                for (const [key, value] of Object.entries(templateData)) {
                    const input = document.querySelector(`[name="${key}"]`);
                    if (input) {
                        input.value = value;
                        // Déclencher un événement change pour les selects
                        if (input.tagName === 'SELECT') {
                            input.dispatchEvent(new Event('change'));
                        }
                    }
                }
                
                // Animation de feedback
                button.classList.add('btn-success');
                button.classList.remove('btn-outline-secondary');
                setTimeout(() => {
                    button.classList.remove('btn-success');
                    button.classList.add('btn-outline-secondary');
                }, 1000);
                
            } catch (e) {
                console.error('Erreur lors du parsing du template:', e);
            }
        });
    });

    /**
     * SECTION: GESTION DE L'EMPLOI DU TEMPS
     * Fonctionnalités JavaScript pour la page emploi_temps.php
     */
    
    // Gestion du changement de statut des tâches dans l'emploi du temps
    const taskStatusCheckboxes = document.querySelectorAll('.task-status');
    if (taskStatusCheckboxes.length > 0) {
        taskStatusCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const taskId = this.dataset.taskId;
                const newStatus = this.checked ? 'terminee' : 'a_faire';
                const taskElement = this.closest('.tache');
                
                // Mise à jour visuelle immédiate
                if (this.checked) {
                    taskElement.classList.add('tache-terminee');
                } else {
                    taskElement.classList.remove('tache-terminee');
                }
                
                // Envoyer la mise à jour via AJAX
                fetch('ajax/update_task_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `task_id=${taskId}&status=${newStatus}`
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        // Restaurer l'état précédent en cas d'erreur
                        this.checked = !this.checked;
                        if (this.checked) {
                            taskElement.classList.add('tache-terminee');
                        } else {
                            taskElement.classList.remove('tache-terminee');
                        }
                        console.error('Erreur lors de la mise à jour du statut:', data.error);
                    }
                })
                .catch(error => {
                    // Restaurer l'état précédent en cas d'erreur
                    this.checked = !this.checked;
                    if (this.checked) {
                        taskElement.classList.add('tache-terminee');
                    } else {
                        taskElement.classList.remove('tache-terminee');
                    }
                    console.error('Erreur:', error);
                });
            });
        });
    }
    
    // Confirmation de suppression pour les événements et tâches
    const deleteButtons = document.querySelectorAll('a[onclick*="confirmDelete"]');
    if (deleteButtons.length > 0) {
        deleteButtons.forEach(button => {
            // Le gestionnaire d'événements est déjà configuré via l'attribut onclick
            // Cette section est juste pour documenter la fonctionnalité
        });
    }

    // Gestion du changement de statut des tâches
    const statusSelects = document.querySelectorAll('.task-status-select');
    statusSelects.forEach(select => {
        select.addEventListener('change', function() {
            const taskId = this.dataset.taskId;
            const newStatus = this.value;
            const taskRow = this.closest('tr');
            
            // Afficher un indicateur de chargement
            const originalContent = this.parentElement.innerHTML;
            this.parentElement.innerHTML = '<div class="spinner"></div>';
            
            // Envoyer la mise à jour via AJAX
            fetch('index.php?page=taches', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update_status&task_id=${taskId}&status=${newStatus}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mettre à jour l'interface utilisateur
                    this.parentElement.innerHTML = originalContent;
                    
                    // Mettre à jour la classe de la ligne en fonction du nouveau statut
                    taskRow.classList.remove('tache-terminee');
                    if (newStatus === 'terminee') {
                        taskRow.classList.add('tache-terminee');
                        // Animation de complétion
                        taskRow.classList.add('fade-in');
                        setTimeout(() => {
                            taskRow.classList.remove('fade-in');
                        }, 500);
                    }
                    
                    // Réinitialiser le sélecteur
                    document.querySelector(`select[data-task-id="${taskId}"]`).value = newStatus;
                } else {
                    // Afficher une erreur
                    this.parentElement.innerHTML = originalContent;
                    alert('Erreur lors de la mise à jour du statut: ' + (data.error || 'Erreur inconnue'));
                }
            })
            .catch(error => {
                this.parentElement.innerHTML = originalContent;
                console.error('Erreur:', error);
                alert('Erreur lors de la mise à jour du statut');
            });
        });
    });

    // Effet de glassmorphisme dynamique sur les cartes
    const cards = document.querySelectorAll('.card');
    if (cards.length > 0) {
        window.addEventListener('mousemove', function(e) {
            cards.forEach(card => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                if (x > 0 && x < rect.width && y > 0 && y < rect.height) {
                    const xPercent = Math.floor((x / rect.width) * 100);
                    const yPercent = Math.floor((y / rect.height) * 100);
                    card.style.background = `
                        radial-gradient(
                            circle at ${xPercent}% ${yPercent}%, 
                            rgba(255, 255, 255, 0.8), 
                            rgba(255, 255, 255, 0.5)
                        )
                    `;
                    if (document.body.classList.contains('sombre')) {
                        card.style.background = `
                            radial-gradient(
                                circle at ${xPercent}% ${yPercent}%, 
                                rgba(60, 65, 70, 0.8), 
                                rgba(40, 45, 50, 0.5)
                            )
                        `;
                    }
                }
            });
        });
    }

    // Animation des boutons
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Initialiser les fonctions spécifiques à l'emploi du temps
    setupScheduleFunctions();
});

/**
 * Configuration des formulaires de tâches
 */
function setupTaskForms() {
    // Sélection de la date d'échéance
    var dateInputs = document.querySelectorAll('.date-input');
    dateInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            validateDateInput(this);
        });
    });
    
    // Validation de formulaire
    var taskForms = document.querySelectorAll('.task-form');
    taskForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            var titleInput = form.querySelector('[name="titre"]');
            if (!titleInput.value.trim()) {
                e.preventDefault();
                titleInput.classList.add('is-invalid');
                alert('Le titre de la tâche est obligatoire');
            }
        });
    });
}

/**
 * Configuration des fonctionnalités spécifiques à l'emploi du temps
 * Cette fonction initialise toutes les interactions JavaScript
 * nécessaires pour la page emploi_temps.php
 */
function setupScheduleFunctions() {
    // Validation des dates dans le formulaire d'événement
    const dateDebut = document.getElementById('date_debut');
    const dateFin = document.getElementById('date_fin');
    
    if (dateDebut && dateFin) {
        // S'assurer que la date de fin n'est pas antérieure à la date de début
        dateFin.addEventListener('change', function() {
            if (dateDebut.value && this.value && this.value < dateDebut.value) {
                alert('La date de fin ne peut pas être antérieure à la date de début');
                this.value = dateDebut.value;
            }
        });
        
        dateDebut.addEventListener('change', function() {
            if (dateFin.value && this.value && dateFin.value < this.value) {
                dateFin.value = this.value;
            }
        });
    }
    
    // Gestion des heures de début et de fin
    const heureDebut = document.getElementById('heure_debut');
    const heureFin = document.getElementById('heure_fin');
    
    if (heureDebut && heureFin) {
        // S'assurer que l'heure de fin est postérieure à l'heure de début si même jour
        heureDebut.addEventListener('change', function() {
            if (dateDebut.value && dateFin.value && dateDebut.value === dateFin.value) {
                if (heureFin.value <= this.value) {
                    // Ajouter au moins une heure à l'heure de début
                    const heureDebutValue = this.value.split(':');
                    let heures = parseInt(heureDebutValue[0]) + 1;
                    if (heures > 23) heures = 23;
                    heureFin.value = `${heures.toString().padStart(2, '0')}:${heureDebutValue[1]}`;
                }
            }
        });
    }
}

/**
 * Configuration du changement de statut des tâches
 */
function setupTaskStatusChange() {
    var statusSelects = document.querySelectorAll('.status-select');
    statusSelects.forEach(function(select) {
        select.addEventListener('change', function() {
            var taskId = this.getAttribute('data-task-id');
            var status = this.value;
            
            // Animation de chargement
            var row = this.closest('tr') || this.closest('.card');
            row.classList.add('opacity-50');
            
            // Envoyer la mise à jour via AJAX
            var formData = new FormData();
            formData.append('task_id', taskId);
            formData.append('status', status);
            formData.append('action', 'update_status');
            
            fetch('index.php?page=taches', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                row.classList.remove('opacity-50');
                if (data.success) {
                    // Mise à jour réussie
                    if (status === 'terminee') {
                        row.classList.add('tache-terminee');
                    } else {
                        row.classList.remove('tache-terminee');
                    }
                } else {
                    // Erreur
                    alert('Erreur lors de la mise à jour du statut');
                    // Réinitialiser la sélection
                    this.value = this.getAttribute('data-original-value');
                }
            })
            .catch(error => {
                row.classList.remove('opacity-50');
                console.error('Erreur:', error);
            });
        });
    });
}

/**
 * Configuration de la gestion des catégories
 */
function setupCategoryManagement() {
    // Prévisualisation de la couleur de la catégorie
    var colorPickers = document.querySelectorAll('.color-picker');
    colorPickers.forEach(function(picker) {
        picker.addEventListener('input', function() {
            var preview = document.querySelector('.color-preview');
            if (preview) {
                preview.style.backgroundColor = this.value;
            }
        });
    });
}

/**
 * Validation des champs de date
 */
function validateDateInput(input) {
    var today = new Date();
    today.setHours(0, 0, 0, 0);
    
    var selectedDate = new Date(input.value);
    selectedDate.setHours(0, 0, 0, 0);
    
    if (selectedDate < today) {
        input.classList.add('is-invalid');
        var feedback = input.nextElementSibling;
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.textContent = 'La date ne peut pas être antérieure à aujourd\'hui';
        }
    } else {
        input.classList.remove('is-invalid');
    }
}
/**
 * Confirmation de suppression
 */
function confirmDelete(message) {
    return confirm(message || 'Êtes-vous sûr de vouloir supprimer cet élément ?');
} 

/**
 * Remplacer les images manquantes par des placeholders
 */
function replaceMissingImages() {
    // Vérifier les images du calendrier
    var calendarImages = document.querySelectorAll('img[src*="calendar.png"]');
    calendarImages.forEach(function(img) {
        img.onerror = function() {
            // Image de calendrier en base64 (bleu simple)
            this.src = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA0NDggNTEyIj48cGF0aCBmaWxsPSIjMDA3YmZmIiBkPSJNMTUyIDY0SDI5NlYyNGEyNCAyNCAwIDAgMSA0OCAwVjY0aDQ4YzI2LjUgMCA0OCAyMS41IDQ4IDQ4djM1MmMwIDI2LjUtMjEuNSA0OC00OCA0OEg1NmMtMjYuNSAwLTQ4LTIxLjUtNDgtNDhWMTEyYzAtMjYuNSAyMS41LTQ4IDQ4LTQ4aDQ4VjI0YTI0IDI0IDAgMCAxIDQ4IDBWNjR6TTQ4IDQwMlYyNTZIMzk5Ljk2djE0NmMwIDMuMzA4LTIuNjg4IDYtNiA2SDU0Yy0zLjMwOCAwLTYtMi42OTItNi02VjQwMnpNNTQgMjA4VjExMmMwLTMuMzA4IDIuNjkyLTYgNi02aDMyOGMzLjMxMiAwIDYgMi42OTIgNiA2djk2SDU0eiIvPjwvc3ZnPg==';
            this.style.width = '200px';
            this.style.height = '200px';
        };
        
        // Déclencher manuellement si l'image est déjà en erreur
        if (img.complete && img.naturalHeight === 0) {
            img.onerror();
        }
    });
} 