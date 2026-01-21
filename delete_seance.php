<?php
require_once 'connect.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo "ID manquant";
    exit;
}

$id = intval($_GET['id']);

try {
    // Supprimer d'abord les exercices liés à cette séance
    $stmt = $pdo->prepare("DELETE FROM exercices_seance WHERE id_seance = ?");
    $stmt->execute([$id]);

    // Suppression de la seance
    $stmt = $pdo->prepare("DELETE FROM seances WHERE id = ?");
    $stmt->execute([$id]);

    echo "Séance supprimée avec succès";
} catch (PDOException $e) {
    http_response_code(500);
    echo "Erreur lors de la suppression : " . $e->getMessage();
}
?>
