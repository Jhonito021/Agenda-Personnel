<?php
/**
 * Fichier: pages/emploi_temps.php
 * Description: Gestion de l'emploi du temps de l'utilisateur
 * 
 * Ce fichier permet de :
 * - Afficher l'emploi du temps hebdomadaire
 * - Ajouter, modifier et supprimer des événements
 * - Associer des tâches aux événements
 * - Naviguer entre les différentes semaines
 */

// Inclure les fonctions utilitaires
require_once 'includes/emploi_temps_utils.php';

/**
 * SECTION 1: INITIALISATION ET SÉCURITÉ
 * Vérification de la connexion utilisateur et initialisation des variables
 */
if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: index.php?page=connexion');
    exit;
}

$db = connectDB();
$id_utilisateur = $_SESSION['utilisateur_id'];
$message = '';
$type_message = '';
$evenement_a_modifier = null;
$action = isset($_GET['action']) ? $_GET['action'] : '';

/**
 * SECTION 2: RÉCUPÉRATION DES DONNÉES DE BASE
 * Chargement des catégories, de l'emploi du temps et des tâches
 */

// Récupération des catégories pour les formulaires
$query_categories = "SELECT * FROM categories WHERE id_utilisateur = :id_utilisateur OR id_utilisateur IS NULL ORDER BY nom ASC";
$stmt_categories = $db->prepare($query_categories);
$stmt_categories->bindParam(':id_utilisateur', $id_utilisateur);
$stmt_categories->execute();
$categories = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);

// Récupération de l'emploi du temps de l'utilisateur
$query_emploi = "SELECT * FROM emplois_du_temps WHERE id_utilisateur = :id_utilisateur LIMIT 1";
$stmt_emploi = $db->prepare($query_emploi);
$stmt_emploi->bindParam(':id_utilisateur', $id_utilisateur);
$stmt_emploi->execute();
$emploi_du_temps = $stmt_emploi->fetch(PDO::FETCH_ASSOC);

// Création d'un emploi du temps par défaut si l'utilisateur n'en a pas
if (!$emploi_du_temps) {
    $query_create = "INSERT INTO emplois_du_temps (titre, date_debut, date_fin, id_utilisateur) 
                    VALUES ('Mon emploi du temps', CURRENT_DATE(), DATE_ADD(CURRENT_DATE(), INTERVAL 1 YEAR), :id_utilisateur)";
    $stmt_create = $db->prepare($query_create);
    $stmt_create->bindParam(':id_utilisateur', $id_utilisateur);
    
    if ($stmt_create->execute()) {
        $id_emploi_du_temps = $db->lastInsertId();
        $query_emploi = "SELECT * FROM emplois_du_temps WHERE id = :id";
        $stmt_emploi = $db->prepare($query_emploi);
        $stmt_emploi->bindParam(':id', $id_emploi_du_temps);
        $stmt_emploi->execute();
        $emploi_du_temps = $stmt_emploi->fetch(PDO::FETCH_ASSOC);
    }
}

// Récupération des tâches actives pour le formulaire d'association
$query_taches_select = "SELECT id, titre FROM taches WHERE id_utilisateur = :id_utilisateur AND statut != 'terminee' ORDER BY date_echeance ASC, titre ASC";
$stmt_taches_select = $db->prepare($query_taches_select);
$stmt_taches_select->bindParam(':id_utilisateur', $id_utilisateur);
$stmt_taches_select->execute();
$taches_select = $stmt_taches_select->fetchAll(PDO::FETCH_ASSOC);

/**
 * SECTION 3: TRAITEMENT DES ACTIONS (CRUD)
 * Gestion des formulaires et des actions sur les événements
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ajouter_evenement']) || isset($_POST['modifier_evenement'])) {
        // Récupération et validation des données du formulaire
        $titre = isset($_POST['titre']) ? trim($_POST['titre']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $date_debut = isset($_POST['date_debut']) ? $_POST['date_debut'] : '';
        $heure_debut = isset($_POST['heure_debut']) ? $_POST['heure_debut'] : '00:00';
        $date_fin = isset($_POST['date_fin']) ? $_POST['date_fin'] : '';
        $heure_fin = isset($_POST['heure_fin']) ? $_POST['heure_fin'] : '00:00';
        $lieu = isset($_POST['lieu']) ? trim($_POST['lieu']) : '';
        $id_categorie = isset($_POST['id_categorie']) && !empty($_POST['id_categorie']) ? $_POST['id_categorie'] : null;
        
        // Validation des champs obligatoires
        if (empty($titre) || empty($date_debut) || empty($date_fin)) {
            $message = 'Veuillez remplir tous les champs obligatoires.';
            $type_message = 'danger';
        } else {
            // Formatage des dates pour la base de données
            $datetime_debut = formatDateTimeForDB($date_debut, $heure_debut);
            $datetime_fin = formatDateTimeForDB($date_fin, $heure_fin);
            
            // Traitement de l'ajout d'un événement
            if (isset($_POST['ajouter_evenement'])) {
                $query = "INSERT INTO evenements (titre, description, date_debut, date_fin, lieu, id_emploi_du_temps, id_categorie) 
                          VALUES (:titre, :description, :date_debut, :date_fin, :lieu, :id_emploi_du_temps, :id_categorie)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':titre', $titre);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':date_debut', $datetime_debut);
                $stmt->bindParam(':date_fin', $datetime_fin);
                $stmt->bindParam(':lieu', $lieu);
                $stmt->bindParam(':id_emploi_du_temps', $emploi_du_temps['id']);
                $stmt->bindParam(':id_categorie', $id_categorie);
                
                if ($stmt->execute()) {
                    $message = 'L\'événement a été ajouté avec succès.';
                    $type_message = 'success';

                    // Association des tâches sélectionnées à l'événement
                    if (!empty($_POST['taches_associees']) && is_array($_POST['taches_associees'])) {
                        foreach ($_POST['taches_associees'] as $id_tache_associee) {
                            $query_assoc = "INSERT IGNORE INTO emploi_temps_taches (id_emploi_du_temps, id_tache) VALUES (:id_edt, :id_tache)";
                            $stmt_assoc = $db->prepare($query_assoc);
                            $stmt_assoc->bindParam(':id_edt', $emploi_du_temps['id']);
                            $stmt_assoc->bindParam(':id_tache', $id_tache_associee);
                            $stmt_assoc->execute();
                        }
                    }
                } else {
                    $message = 'Une erreur est survenue lors de l\'ajout de l\'événement.';
                    $type_message = 'danger';
                }
            } 
            // Traitement de la modification d'un événement
            elseif (isset($_POST['modifier_evenement']) && isset($_POST['id'])) {
                $id = $_POST['id'];
                
                // Vérification que l'utilisateur est bien propriétaire de l'événement
                if (verifierProprietaireEvenement($db, $id, $id_utilisateur)) {
                    // Mise à jour des associations de tâches
                    $id_edt = $emploi_du_temps['id'];
                    $query_del = "DELETE FROM emploi_temps_taches WHERE id_emploi_du_temps = :id_edt";
                    $stmt_del = $db->prepare($query_del);
                    $stmt_del->bindParam(':id_edt', $id_edt);
                    $stmt_del->execute();

                    // Ajout des nouvelles associations de tâches
                    if (!empty($_POST['taches_associees']) && is_array($_POST['taches_associees'])) {
                        foreach ($_POST['taches_associees'] as $id_tache_associee) {
                            $query_assoc = "INSERT IGNORE INTO emploi_temps_taches (id_emploi_du_temps, id_tache) VALUES (:id_edt, :id_tache)";
                            $stmt_assoc = $db->prepare($query_assoc);
                            $stmt_assoc->bindParam(':id_edt', $id_edt);
                            $stmt_assoc->bindParam(':id_tache', $id_tache_associee);
                            $stmt_assoc->execute();
                        }
                    }

                    // Mise à jour des informations de l'événement
                    $query = "UPDATE evenements 
                              SET titre = :titre, description = :description, date_debut = :date_debut, 
                                  date_fin = :date_fin, lieu = :lieu, id_categorie = :id_categorie 
                              WHERE id = :id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':titre', $titre);
                    $stmt->bindParam(':description', $description);
                    $stmt->bindParam(':date_debut', $datetime_debut);
                    $stmt->bindParam(':date_fin', $datetime_fin);
                    $stmt->bindParam(':lieu', $lieu);
                    $stmt->bindParam(':id_categorie', $id_categorie);
                    $stmt->bindParam(':id', $id);
                    
                    if ($stmt->execute()) {
                        $message = 'L\'événement a été modifié avec succès.';
                        $type_message = 'success';
                    } else {
                        $message = 'Une erreur est survenue lors de la modification de l\'événement.';
                        $type_message = 'danger';
                    }
                } else {
                    $message = 'Vous n\'êtes pas autorisé à modifier cet événement.';
                    $type_message = 'danger';
                }
            }
        }
    }
}

/**
 * SECTION 3.1: TRAITEMENT DE LA SUPPRESSION D'UN ÉVÉNEMENT
 * Gestion de la suppression via l'action GET
 */
if ($action === 'supprimer' && isset($_GET['id'])) {
    $id = $_GET['id'];
    // Vérification que l'utilisateur est bien propriétaire de l'événement
    if (verifierProprietaireEvenement($db, $id, $id_utilisateur)) {
        $query = "DELETE FROM evenements WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            $message = 'L\'événement a été supprimé avec succès.';
            $type_message = 'success';
        } else {
            $message = 'Une erreur est survenue lors de la suppression de l\'événement.';
            $type_message = 'danger';
        }
    } else {
        $message = 'Vous n\'êtes pas autorisé à supprimer cet événement.';
        $type_message = 'danger';
    }
}

/**
 * SECTION 3.2: CHARGEMENT D'UN ÉVÉNEMENT POUR MODIFICATION
 * Récupération des données de l'événement à modifier
 */
if ($action === 'modifier' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM evenements e 
              JOIN emplois_du_temps edt ON e.id_emploi_du_temps = edt.id 
              WHERE e.id = :id AND edt.id_utilisateur = :id_utilisateur";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':id_utilisateur', $id_utilisateur);
    $stmt->execute();
    $evenement_a_modifier = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$evenement_a_modifier) {
        $message = 'Événement introuvable ou vous n\'êtes pas autorisé à le modifier.';
        $type_message = 'danger';
    }
}

/**
 * SECTION 4: RÉCUPÉRATION DES DONNÉES POUR L'AFFICHAGE
 * Calcul des dates et récupération des événements/tâches pour la semaine
 */
// Détermination de la semaine à afficher (0 = semaine actuelle, -1 = semaine précédente, etc.)
$semaine = isset($_GET['semaine']) ? intval($_GET['semaine']) : 0;
$date_debut_semaine = date('Y-m-d', strtotime("monday this week $semaine week"));
$date_fin_semaine = date('Y-m-d', strtotime("sunday this week $semaine week"));

// Récupération des événements et tâches pour la semaine sélectionnée
$evenements = getEvenements($db, $id_utilisateur, $date_debut_semaine, $date_fin_semaine);
$taches = getTaches($db, $id_utilisateur, $date_debut_semaine, $date_fin_semaine);
$evenements_par_jour = organiserParJour($evenements, $taches);

/**
 * SECTION 5: AFFICHAGE DE L'INTERFACE UTILISATEUR
 * Début du code HTML pour l'interface
 */
?>

<!-- Interface utilisateur -->
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <h2 class="mb-4 display-6 d-flex align-items-center">
                <i class="bi bi-calendar-week me-3 text-primary"></i> Emploi du temps
            </h2>

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $type_message; ?> alert-dismissible fade show shadow-sm" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Formulaire d'ajout/modification d'événement -->
            <div class="card form-event-card mb-5">
                <div class="card-header">
                    <h5 class="mb-0">
                        <?php if ($action === 'modifier' && $evenement_a_modifier): ?>
                            <i class="bi bi-pencil-square me-2"></i> Modifier un événement
                        <?php else: ?>
                            <i class="bi bi-plus-circle me-2"></i> Ajouter un événement
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="post" action="index.php?page=emploi_temps">
                        <?php if ($action === 'modifier' && $evenement_a_modifier): ?>
                            <input type="hidden" name="id" value="<?php echo $evenement_a_modifier['id']; ?>">
                        <?php endif; ?>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="titre" class="form-label">Titre*</label>
                                <input type="text" class="form-control" id="titre" name="titre" required
                                       value="<?php echo $evenement_a_modifier ? htmlspecialchars($evenement_a_modifier['titre']) : ''; ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="lieu" class="form-label">Lieu</label>
                                <input type="text" class="form-control" id="lieu" name="lieu"
                                       value="<?php echo $evenement_a_modifier ? htmlspecialchars($evenement_a_modifier['lieu']) : ''; ?>">
                            </div>
                        </div>

                        <div class="mb-3 mt-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2"><?php echo $evenement_a_modifier ? htmlspecialchars($evenement_a_modifier['description']) : ''; ?></textarea>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="date_debut" class="form-label">Date de début*</label>
                                <input type="date" class="form-control" id="date_debut" name="date_debut" required
                                       value="<?php echo $evenement_a_modifier ? date('Y-m-d', strtotime($evenement_a_modifier['date_debut'])) : date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="heure_debut" class="form-label">Heure de début*</label>
                                <input type="time" class="form-control" id="heure_debut" name="heure_debut" required
                                       value="<?php echo $evenement_a_modifier ? date('H:i', strtotime($evenement_a_modifier['date_debut'])) : '08:00'; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="date_fin" class="form-label">Date de fin*</label>
                                <input type="date" class="form-control" id="date_fin" name="date_fin" required
                                       value="<?php echo $evenement_a_modifier ? date('Y-m-d', strtotime($evenement_a_modifier['date_fin'])) : date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="heure_fin" class="form-label">Heure de fin*</label>
                                <input type="time" class="form-control" id="heure_fin" name="heure_fin" required
                                       value="<?php echo $evenement_a_modifier ? date('H:i', strtotime($evenement_a_modifier['date_fin'])) : '09:00'; ?>">
                            </div>
                        </div>

                        <div class="mb-3 mt-3">
                            <label for="id_categorie" class="form-label">Catégorie</label>
                            <select class="form-select" id="id_categorie" name="id_categorie">
                                <option value="">Aucune catégorie</option>
                                <?php foreach ($categories as $categorie): ?>
                                    <option value="<?php echo $categorie['id']; ?>" 
                                            <?php echo $evenement_a_modifier && $evenement_a_modifier['id_categorie'] == $categorie['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($categorie['nom']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="taches_associees" class="form-label">Tâches associées</label>
                            <select class="form-select" id="taches_associees" name="taches_associees[]" multiple>
                                <?php foreach ($taches_select as $tache): ?>
                                    <option value="<?php echo $tache['id']; ?>">
                                        <?php echo htmlspecialchars($tache['titre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Maintenez Ctrl (Windows) ou Cmd (Mac) pour sélectionner plusieurs tâches.</div>
                        </div>

                        <div class="d-flex gap-3 justify-content-end mt-4">
                            <?php if ($action === 'modifier' && $evenement_a_modifier): ?>
                                <button type="submit" name="modifier_evenement" class="btn btn-primary px-4">
                                    <i class="bi bi-save"></i> Enregistrer les modifications
                                </button>
                                <a href="index.php?page=emploi_temps" class="btn btn-secondary px-4">
                                    <i class="bi bi-x-lg"></i> Annuler
                                </a>
                            <?php else: ?>
                                <button type="submit" name="ajouter_evenement" class="btn btn-primary px-4">
                                    <i class="bi bi-plus-circle"></i> Ajouter l'événement
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        
            <!-- Affichage de l'emploi du temps -->
        <div class="card">
                <div class="card-header week-navigation">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar3 text-primary me-2"></i> 
                            <span class="d-none d-sm-inline">Semaine du</span> 
                            <?php echo date('d/m/Y', strtotime($date_debut_semaine)); ?> 
                            <span class="d-none d-sm-inline">au</span>
                            <?php echo date('d/m/Y', strtotime($date_fin_semaine)); ?>
                        </h5>
                        <div class="d-flex gap-2">
                            <a href="index.php?page=emploi_temps&semaine=<?php echo $semaine - 1; ?>" 
                               class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-chevron-left"></i>
                                <span class="d-none d-sm-inline ms-1">Semaine précédente</span>
                            </a>
                            <a href="index.php?page=emploi_temps&semaine=0" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-calendar-day"></i>
                                <span class="d-none d-sm-inline ms-1">Semaine actuelle</span>
                            </a>
                            <a href="index.php?page=emploi_temps&semaine=<?php echo $semaine + 1; ?>" 
                               class="btn btn-outline-secondary btn-sm">
                                <span class="d-none d-sm-inline me-1">Semaine suivante</span>
                                <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>
                </div>
                <div class="card-body p-0">
                <div class="emploi-temps-container">
                        <table class="table table-bordered emploi-temps-table mb-0">
                        <thead>
                            <tr>
                                    <th style="width: 80px;">Horaire</th>
                                <?php
                                // Génération des en-têtes pour chaque jour de la semaine
                                for ($i = 0; $i < 7; $i++) {
                                    $jour = date('Y-m-d', strtotime("$date_debut_semaine +$i days"));
                                    $nom_jour = date('l', strtotime($jour));
                                    $numero_jour = date('d/m', strtotime($jour));
                                        $nom_jour = traduireJour($nom_jour);
                                        $classe = date('Y-m-d') === $jour ? 'table-active' : '';
                                        echo "<th class=\"$classe\">$nom_jour<br><small class='text-muted'>$numero_jour</small></th>";
                                }
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                                <?php
                                // Génération des lignes pour chaque heure de la journée
                                for ($heure = 8; $heure <= 20; $heure++) {
                                    echo "<tr>";
                                    echo "<td class=\"text-center text-muted\">" . sprintf("%02d:00", $heure) . "</td>";
                                    
                                    // Génération des cellules pour chaque jour de la semaine
                                    for ($i = 0; $i < 7; $i++) {
                                        $jour = date('Y-m-d', strtotime("$date_debut_semaine +$i days"));
                                        $classe = date('Y-m-d') === $jour ? 'table-active' : '';
                                        echo "<td class=\"$classe\">";
                                        
                                        // Affichage des événements et tâches pour ce jour et cette heure
                                        if (isset($evenements_par_jour[$jour])) {
                                            foreach ($evenements_par_jour[$jour] as $item) {
                                                $heure_debut = intval(date('H', strtotime($item['date_debut'])));
                                                $heure_fin = intval(date('H', strtotime($item['date_fin'])));
                                                
                                                // Vérifier si l'événement/tâche est à cette heure
                                                if ($heure >= $heure_debut && $heure < $heure_fin) {
                                                    $couleur_bg = $item['categorie_couleur'] ?? '#6c757d';
                                                    $couleur_texte = '#ffffff';
                                                    $est_tache = isset($item['type']) && $item['type'] === 'tache';
                                                    
                                                    $classe_item = $est_tache ? 'tache' : 'evenement';
                                                    $icone = $est_tache ? '<i class="bi bi-list-task me-1"></i>' : '<i class="bi bi-calendar-day me-1"></i>';
                                                    
                                                    if ($est_tache && isset($item['statut']) && $item['statut'] === 'terminee') {
                                                        $classe_item .= ' tache-terminee';
                                                    }
                                                    
                                                        echo "<div class=\"$classe_item\" data-task-id=\"{$item['id']}\" style=\"background-color: $couleur_bg; color: $couleur_texte;\">";
                                                    echo "<div class=\"fw-bold\">$icone" . htmlspecialchars($item['titre']) . "</div>";
                                                    echo "<div class=\"small\">" . date('H:i', strtotime($item['date_debut'])) . " - " . date('H:i', strtotime($item['date_fin'])) . "</div>";
                                                    
                                                    if (!empty($item['lieu'])) {
                                                            echo "<div class=\"small\"><i class=\"bi bi-geo-alt me-1\"></i>" . htmlspecialchars($item['lieu']) . "</div>";
                                                    }
                                                    
                                                        echo "<div class=\"mt-2 d-flex align-items-center gap-1\">";
                                                    if ($est_tache) {
                                                        $statut = $item['statut'] ?? 'a_faire';
                                                        $checked = $statut === 'terminee' ? 'checked' : '';
                                                        echo "<div class=\"form-check d-inline-block me-2\">";
                                                        echo "<input class=\"form-check-input task-status\" type=\"checkbox\" data-task-id=\"{$item['id']}\" $checked>";
                                                        echo "<label class=\"form-check-label small\">Terminée</label>";
                                                        echo "</div>";
                                                            echo "<a href=\"index.php?page=taches&action=modifier&id=" . $item['id'] . "\" class=\"btn btn-light btn-sm\"><i class=\"bi bi-pencil\"></i></a>";
                                                    } else {
                                                            echo "<a href=\"index.php?page=emploi_temps&action=modifier&id=" . $item['id'] . "\" class=\"btn btn-light btn-sm\"><i class=\"bi bi-pencil\"></i></a>";
                                                    }
                                                    
                                                    $page = $est_tache ? 'taches' : 'emploi_temps';
                                                    $message = $est_tache ? 'cette tâche' : 'cet événement';
                                                        echo "<a href=\"index.php?page=$page&action=supprimer&id=" . $item['id'] . "\" class=\"btn btn-light btn-sm\" onclick=\"return confirmDelete('Êtes-vous sûr de vouloir supprimer $message ?');\"><i class=\"bi bi-trash\"></i></a>";
                                                    echo "</div>";
                                                    echo "</div>";
                                                }
                                            }
                                        }
                                        
                                        echo "</td>";
                                    }
                                    
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (empty($evenements)): ?>
                        <div class="alert alert-info m-3">
                            <i class="bi bi-info-circle me-2"></i> Aucun événement prévu pour cette semaine. 
                            Utilisez le formulaire ci-dessus pour ajouter des événements à votre emploi du temps.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>