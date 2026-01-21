<?php 
require_once 'connect.php';
session_start();

if (!isset($_SESSION['id'])) {
    http_response_code(403);
    echo "Non connecté.";
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    http_response_code(400);
    echo "ID manquant.";
    exit;
}

// debug temporaire

try {
    $stmt = $pdo->prepare("DELETE FROM objectifs WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        echo "Objectif supprimé avec succès.";
    } else {
        echo "Aucun objectif trouvé. ID reçu : " . htmlspecialchars($id);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo "Erreur : " . $e->getMessage();
}
?>
