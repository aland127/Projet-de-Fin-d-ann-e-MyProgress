<?php
// connexion.php
$host = 'localhost';
$dbname = 'myprogress';
$user = 'root';
$pass = '';

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];
    $motdepasse = $_POST["motdepasse"];

    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    $utilisateur = $stmt->fetch();

    if ($utilisateur && password_verify($motdepasse, $utilisateur["motdepasse"])) {
        // Connexion réussie
        session_start();
        $_SESSION["id"] = $utilisateur["id"];
        $_SESSION["id_utilisateur"] = $utilisateur["id"]; 
        $_SESSION["prenom"] = $utilisateur["prenom"];
        header("Location: tableau_de_bord.php");
        exit();
    } else {
        // Échec de connexion
        header("Location: connexion.html?erreur=1");
        exit();
    }
}
?>
