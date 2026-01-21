<?php
$host = 'localhost';
$dbname = 'myprogress';
$user = 'root';
$password = '';

// Connexion à la base de données 
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Pour compatibilité avec les anciens fichiers
    $conn = $pdo;
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
