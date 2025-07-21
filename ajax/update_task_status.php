<?php
/**
 * Fichier: ajax/update_task_status.php
 * Description: Traitement AJAX pour mettre à jour le statut d'une tâche
 * 
 * Ce script reçoit une requête AJAX de la page emploi_temps.php
 * pour mettre à jour le statut d'une tâche (terminée ou à faire)
 * sans avoir à recharger la page.
 * 
 * @param int $_POST['task_id'] - ID de la tâche à mettre à jour
 * @param string $_POST['status'] - Nouveau statut ('terminee' ou 'a_faire')
 * @return json - Résultat de l'opération (succès ou erreur)
 */

// Initialiser la session si ce n'est pas déjà fait
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit;
}

// Vérifier si la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

// Vérifier si les paramètres requis sont présents
if (!isset($_POST['task_id']) || !isset($_POST['status'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Paramètres manquants']);
    exit;
}

// Récupérer les paramètres
$task_id = intval($_POST['task_id']);
$status = $_POST['status'];
$id_utilisateur = $_SESSION['utilisateur_id'];

// Valider le statut
if (!in_array($status, ['terminee', 'a_faire', 'en_cours', 'en_retard'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Statut invalide']);
    exit;
}

// Connexion à la base de données
require_once '../config/database.php';
$db = connectDB();

// Vérifier que la tâche appartient bien à l'utilisateur
$query_verify = "SELECT COUNT(*) FROM taches WHERE id = :id AND id_utilisateur = :id_utilisateur";
$stmt_verify = $db->prepare($query_verify);
$stmt_verify->bindParam(':id', $task_id);
$stmt_verify->bindParam(':id_utilisateur', $id_utilisateur);
$stmt_verify->execute();

if ($stmt_verify->fetchColumn() == 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Tâche non trouvée ou non autorisée']);
    exit;
}

// Mettre à jour le statut de la tâche
$query = "UPDATE taches SET statut = :statut WHERE id = :id AND id_utilisateur = :id_utilisateur";
$stmt = $db->prepare($query);
$stmt->bindParam(':statut', $status);
$stmt->bindParam(':id', $task_id);
$stmt->bindParam(':id_utilisateur', $id_utilisateur);

try {
    $result = $stmt->execute();
    
    if ($result) {
        // Si la tâche est marquée comme terminée, mettre à jour la date de complétion
        if ($status === 'terminee') {
            $query_completion = "UPDATE taches SET date_completion = NOW() WHERE id = :id";
            $stmt_completion = $db->prepare($query_completion);
            $stmt_completion->bindParam(':id', $task_id);
            $stmt_completion->execute();
        } else {
            // Si la tâche est remise à faire, effacer la date de complétion
            $query_completion = "UPDATE taches SET date_completion = NULL WHERE id = :id";
            $stmt_completion = $db->prepare($query_completion);
            $stmt_completion->bindParam(':id', $task_id);
            $stmt_completion->execute();
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Erreur lors de la mise à jour']);
    }
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Erreur de base de données: ' . $e->getMessage()]);
}
