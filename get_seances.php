<?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['id'])) {
    http_response_code(403);
    echo json_encode(["error" => "Non connecté"]);
    exit;
}

$id_utilisateur = $_SESSION['id'];

try {
    // Récupération des séances
    $stmt = $pdo->prepare("SELECT * FROM seances WHERE utilisateur_id = ? ORDER BY date DESC");
    $stmt->execute([$id_utilisateur]);
    $seances = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $resultats = [];

    foreach ($seances as $seance) {
        $id_seance = $seance['id'];

        // Récupération des exercices liés 
        $stmtExo = $pdo->prepare("SELECT zone, exercice, poids, repetitions, distance, vitesse FROM exercices_seance WHERE id_seance = ?");
        $stmtExo->execute([$id_seance]);
        $exercices = $stmtExo->fetchAll(PDO::FETCH_ASSOC);

        $resultats[] = [
            'id' => $seance['id'],
            'date' => $seance['date'],
            'type' => $seance['type'],
            'duree' => $seance['duree'],
            'intensite' => $seance['intensite'],
            'fatigue' => $seance['fatigue'],
            'commentaire' => $seance['commentaire'],
            'exercices' => $exercices
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($resultats);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erreur serveur", "details" => $e->getMessage()]);
}
