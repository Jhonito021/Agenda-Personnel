<?php

/**
 * Fichier: includes/emploi_temps_utils.php
 * Description: Fonctions utilitaires pour la gestion de l'emploi du temps
 * Ce fichier contient toutes les fonctions auxiliaires utilisées par la page emploi_temps.php
 * pour améliorer la modularité et la maintenabilité du code.
 */

/**
 * Fonction: traduireJour
 * 
 * Traduit le nom d'un jour de la semaine de l'anglais vers le français
 * 
 * @param string $nom_jour Le nom du jour en anglais (ex: "Monday")
 * @return string Le nom du jour traduit en français (ex: "Lundi")
 */
function traduireJour($nom_jour) {
    $traductions = [
        'Monday' => 'Lundi',
        'Tuesday' => 'Mardi',
        'Wednesday' => 'Mercredi',
        'Thursday' => 'Jeudi',
        'Friday' => 'Vendredi',
        'Saturday' => 'Samedi',
        'Sunday' => 'Dimanche'
    ];
    return $traductions[$nom_jour] ?? $nom_jour;
}

/**
 * Fonction: formatDateTimeForDB
 * 
 * Formate une date et une heure pour l'insertion dans la base de données
 * Combine la date et l'heure dans un format compatible avec MySQL DATETIME
 * 
 * @param string $date La date au format Y-m-d
 * @param string $heure L'heure au format H:i
 * @return string La date et l'heure combinées au format Y-m-d H:i:00
 */
function formatDateTimeForDB($date, $heure) {
    return $date . ' ' . $heure . ':00';
}

/**
 * Fonction: organiserParJour
 * 
 * Organise les événements et tâches par jour pour faciliter l'affichage dans l'emploi du temps
 * Crée un tableau associatif où les clés sont les dates (Y-m-d) et les valeurs sont les événements/tâches
 * 
 * @param array $evenements Tableau d'événements à organiser
 * @param array $taches Tableau de tâches à organiser
 * @return array Tableau associatif des événements et tâches organisés par jour
 */
function organiserParJour($evenements, $taches) {
    $items_par_jour = [];
    
    // Organiser les événements
    foreach ($evenements as $evenement) {
        $jour = date('Y-m-d', strtotime($evenement['date_debut']));
        if (!isset($items_par_jour[$jour])) {
            $items_par_jour[$jour] = [];
        }
        $items_par_jour[$jour][] = $evenement;
    }
    
    // Organiser les tâches
    foreach ($taches as $tache) {
        $jour = date('Y-m-d', strtotime($tache['date_debut']));
        if (!isset($items_par_jour[$jour])) {
            $items_par_jour[$jour] = [];
        }
        $items_par_jour[$jour][] = $tache;
    }
    
    return $items_par_jour;
}

/**
 * Fonction: verifierProprietaireEvenement
 * 
 * Vérifie si un événement appartient bien à l'utilisateur spécifié
 * Sécurité importante pour éviter qu'un utilisateur puisse modifier ou supprimer
 * des événements qui ne lui appartiennent pas
 * 
 * @param PDO $db Connexion à la base de données
 * @param int $id_evenement ID de l'événement à vérifier
 * @param int $id_utilisateur ID de l'utilisateur
 * @return bool Vrai si l'événement appartient à l'utilisateur, faux sinon
 */
function verifierProprietaireEvenement($db, $id_evenement, $id_utilisateur) {
    $query = "SELECT COUNT(*) FROM evenements e 
              JOIN emplois_du_temps edt ON e.id_emploi_du_temps = edt.id 
              WHERE e.id = :id AND edt.id_utilisateur = :id_utilisateur";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id_evenement);
    $stmt->bindParam(':id_utilisateur', $id_utilisateur);
    $stmt->execute();
    
    return $stmt->fetchColumn() > 0;
}

/**
 * Fonction: getEvenements
 * 
 * Récupère tous les événements d'un utilisateur pour une période donnée
 * Inclut les informations sur les catégories associées
 * 
 * @param PDO $db Connexion à la base de données
 * @param int $id_utilisateur ID de l'utilisateur
 * @param string $date_debut Date de début de la période (format Y-m-d)
 * @param string $date_fin Date de fin de la période (format Y-m-d)
 * @return array Tableau des événements trouvés
 */
function getEvenements($db, $id_utilisateur, $date_debut, $date_fin) {
    $query = "
        SELECT 
            e.id,
            e.titre,
            e.date_debut,
            e.date_fin,
            e.lieu,
            e.description,
            ec.id as id_categorie,
            ec.nom as categorie_nom,
            ec.couleur as categorie_couleur,
            'evenement' as type
        FROM evenements e
        JOIN emplois_du_temps edt ON e.id_emploi_du_temps = edt.id
        LEFT JOIN categories ec ON e.id_categorie = ec.id
        WHERE edt.id_utilisateur = :id_utilisateur
        AND DATE(e.date_debut) >= :date_debut
        AND DATE(e.date_debut) <= :date_fin
        ORDER BY e.date_debut ASC";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_utilisateur', $id_utilisateur);
    $stmt->bindParam(':date_debut', $date_debut);
    $stmt->bindParam(':date_fin', $date_fin);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fonction: getTaches
 * 
 * Récupère toutes les tâches d'un utilisateur pour une période donnée
 * Inclut les informations sur les catégories associées
 * Les tâches sont formatées de manière similaire aux événements pour
 * faciliter leur affichage dans l'emploi du temps
 * 
 * @param PDO $db Connexion à la base de données
 * @param int $id_utilisateur ID de l'utilisateur
 * @param string $date_debut Date de début de la période (format Y-m-d)
 * @param string $date_fin Date de fin de la période (format Y-m-d)
 * @return array Tableau des tâches trouvées
 */
function getTaches($db, $id_utilisateur, $date_debut, $date_fin) {
    $query = "
        SELECT 
            t.id,
            t.titre,
            t.date_echeance as date_debut,
            t.date_echeance as date_fin,
            '' as lieu,
            t.description,
            tc.id as id_categorie,
            tc.nom as categorie_nom,
            tc.couleur as categorie_couleur,
            'tache' as type,
            t.statut
        FROM taches t
        LEFT JOIN categories tc ON t.id_categorie = tc.id
        WHERE t.id_utilisateur = :id_utilisateur
        AND t.date_echeance IS NOT NULL
        AND DATE(t.date_echeance) >= :date_debut
        AND DATE(t.date_echeance) <= :date_fin
        ORDER BY t.date_echeance ASC";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_utilisateur', $id_utilisateur);
    $stmt->bindParam(':date_debut', $date_debut);
    $stmt->bindParam(':date_fin', $date_fin);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
} 