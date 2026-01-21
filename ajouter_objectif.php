<?php
require_once 'connect.php';
session_start();

if (!isset($_SESSION['id'])) {
    http_response_code(403);
    echo "Non connecté";
    exit;
}

$id_utilisateur = $_SESSION['id'];
$type = $_POST['type'] ?? '';
$exercice = $_POST['exercice'] ?? '';
$valeur = $_POST['valeur'] ?? '';
$frequence = $_POST['frequence'] ?? null; 


$type = htmlspecialchars(trim($type));
$exercice = htmlspecialchars(trim($exercice));
$valeur = intval($valeur);
$frequence = $frequence ? htmlspecialchars(trim($frequence)) : null;

if ($type === '' || $valeur <= 0) {
    http_response_code(400);
    echo "Champs requis manquants ou invalides.";
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO objectifs (utilisateur_id, type, exercice, valeur_objectif, frequence)
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$id_utilisateur, $type, $exercice, $valeur, $frequence]);
    echo "Objectif ajouté avec succès";
} catch (PDOException $e) {
    http_response_code(500);
    echo "Erreur base de données : " . $e->getMessage();
}
