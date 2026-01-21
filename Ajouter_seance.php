<?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['id'])) {
    http_response_code(403);
    echo "Non connecté";
    exit;
}

$id_utilisateur = $_SESSION['id'];
$type = $_POST['type'] ?? '';
$date = $_POST['date'] ?? '';
$duree = $_POST['duree'] ?? 0;
$intensite = $_POST['intensite'] ?? '';
$fatigue = $_POST['fatigue'] ?? '';
$commentaire = $_POST['commentaire'] ?? '';

try {
    $pdo->beginTransaction();

    // Insérer la séance
    $stmt = $pdo->prepare("INSERT INTO seances (utilisateur_id, type, date, duree, intensite, fatigue, commentaire) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$id_utilisateur, $type, $date, $duree, $intensite, $fatigue, $commentaire]);
    $id_seance = $pdo->lastInsertId();

    // Insérer les exercices associés
    $insertExercice = $pdo->prepare("INSERT INTO exercices_seance (id_seance, zone, exercice, poids, repetitions, distance, vitesse) VALUES (?, ?, ?, ?, ?, ?, ?)");

    // Musculation
    if ($type === 'musculation') {
        foreach ($_POST as $cle => $valeur) {
            if (preg_match('/^exercices_([a-zA-Z0-9_]+)$/', $cle, $matches)) {
                $zone = $matches[1];
                foreach ($valeur as $exo) {
                    $exoClean = iconv('UTF-8', 'ASCII//TRANSLIT', $exo);
                    $exoClean = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($exoClean));
                    $poidsKey = "poids_{$zone}_{$exoClean}";
                    $repKey = "repetitions_{$zone}_{$exoClean}";

                    $poids = isset($_POST[$poidsKey]) && $_POST[$poidsKey] !== '' ? floatval($_POST[$poidsKey]) : null;
                    $reps = isset($_POST[$repKey]) && $_POST[$repKey] !== '' ? intval($_POST[$repKey]) : null;

                    $insertExercice->execute([$id_seance, $zone, $exo, $poids, $reps, null, null]);
                }
            }
        }
    }

    // Cardio
    if ($type === 'cardio') {
        $exos = $_POST['exercices_cardio'] ?? [];
        foreach ($exos as $exo) {
            $insertExercice->execute([$id_seance, null, $exo, null, null, null, null]);
        }
        $autre = trim($_POST['autre_exercice_cardio'] ?? '');
        if ($autre !== '') {
            $insertExercice->execute([$id_seance, null, $autre, null, null, null, null]);
        }
    }

    // Vélo
    if ($type === 'vélo') {
        $exos = $_POST['exercices_velo'] ?? [];
        $distance = isset($_POST['distance_velo']) ? floatval($_POST['distance_velo']) : null;
        $vitesse = isset($_POST['vitesse_velo']) ? floatval($_POST['vitesse_velo']) : null;

        foreach ($exos as $exo) {
            $insertExercice->execute([$id_seance, null, $exo, null, null, $distance, $vitesse]);
        }

        $autre = trim($_POST['autre_exercice_velo'] ?? '');
        if ($autre !== '') {
            $insertExercice->execute([$id_seance, null, $autre, null, null, $distance, $vitesse]);
        }
    }

    // Natation
    if ($type === 'natation') {
        $exos = $_POST['exercices_natation'] ?? [];
        $distance = isset($_POST['distance_natation']) ? floatval($_POST['distance_natation']) : null;
        $vitesse = isset($_POST['vitesse_natation']) ? floatval($_POST['vitesse_natation']) : null;

        foreach ($exos as $exo) {
            $insertExercice->execute([$id_seance, null, $exo, null, null, $distance, $vitesse]);
        }

        $autre = trim($_POST['autre_exercice_natation'] ?? '');
        if ($autre !== '') {
            $insertExercice->execute([$id_seance, null, $autre, null, null, $distance, $vitesse]);
        }
    }

    $pdo->commit();
    header("Location: historique.html");
exit;
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo "Erreur : " . $e->getMessage();
}
