<?php
require_once 'connect.php';
session_start();

if (!isset($_SESSION['id'])) {
    http_response_code(403);
    echo json_encode(["error" => "Non connecté"]);
    exit;
}

$id_utilisateur = $_SESSION['id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM objectifs WHERE utilisateur_id = ?");
    $stmt->execute([$id_utilisateur]);
    $objectifs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Normaliser les noms
    function normaliser($texte) {
        $texte = iconv('UTF-8', 'ASCII//TRANSLIT', $texte);
        $texte = strtolower(trim($texte));
        return preg_replace('/[^a-z0-9]/', '', $texte);
    }

    $resultats = [];

    foreach ($objectifs as $obj) {
        $progression = 0;
        $unite = "";
        $typeAffiche = "";

        if ($obj['type'] === "nombre-seances") {
            $unite = "séances";
            $typeAffiche = "Nombre de séances";
            $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM seances WHERE utilisateur_id = ?");
            $stmt2->execute([$id_utilisateur]);
            $progression = $stmt2->fetchColumn();
        }

        elseif ($obj['type'] === "poids-muscu") {
            $unite = "kg";
            $typeAffiche = "Poids";

            // Récupère tous les exercices de muscu pour l'utilisateur
            $stmt2 = $pdo->prepare("SELECT exercice, MAX(poids) AS max_poids
                                    FROM exercices_seance 
                                    WHERE id_seance IN (
                                        SELECT id FROM seances WHERE utilisateur_id = ?
                                    ) 
                                    GROUP BY exercice");
            $stmt2->execute([$id_utilisateur]);
            $tous = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            $exerciceObjectif = normaliser($obj['exercice']);
            foreach ($tous as $row) {
                if (normaliser($row['exercice']) === $exerciceObjectif) {
                    $progression = $row['max_poids'];
                    break;
                }
            }
        }

        elseif ($obj['type'] === "temps-cardio") {
            $unite = "min";
            $typeAffiche = "Temps Cardio";
            $stmt2 = $pdo->prepare("SELECT SUM(duree) FROM seances 
                                    WHERE utilisateur_id = ? AND type = 'cardio'");
            $stmt2->execute([$id_utilisateur]);
            $progression = $stmt2->fetchColumn();
        }

        elseif ($obj['type'] === "distance-velo") {
            $unite = "km";
            $typeAffiche = "Distance Vélo";

            $stmt2 = $pdo->prepare("SELECT exercice, SUM(CAST(vitesse AS DECIMAL)) AS total_vitesse
                                    FROM exercices_seance 
                                    WHERE id_seance IN (
                                        SELECT id FROM seances WHERE utilisateur_id = ?
                                    )
                                    GROUP BY exercice");
            $stmt2->execute([$id_utilisateur]);
            $tous = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            $exerciceObjectif = normaliser($obj['exercice']);
            foreach ($tous as $row) {
                if (normaliser($row['exercice']) === $exerciceObjectif) {
                    $progression = $row['total_vitesse'];
                    break;
                }
            }
        }

        $resultats[] = [
            "id" => $obj['id'],
            "type" => $obj['type'],
            "type_affiche" => $typeAffiche,
            "valeur_objectif" => $obj['valeur_objectif'],
            "unite" => $unite,
            "exercice" => $obj['exercice'],
            "valeur_actuelle" => is_null($progression) ? 0 : round($progression)
        ];
    }

    echo json_encode($resultats);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
