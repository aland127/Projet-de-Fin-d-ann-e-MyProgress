<?php
$host = "localhost";
$dbname = "myprogress";
$username = "root";
$password = "";

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Erreur de connexion : " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $prenom = $_POST["prenom"];
  $nom = $_POST["nom"];
  $age = $_POST["age"];
  $poids = $_POST["poids"];
  $email = $_POST["email"];
  $motdepasse = password_hash($_POST["motdepasse"], PASSWORD_DEFAULT);

  $sql = "INSERT INTO utilisateurs (prenom, nom, age, poids, email, motdepasse)
          VALUES (:prenom, :nom, :age, :poids, :email, :motdepasse)";
  $stmt = $pdo->prepare($sql);

  $stmt->execute([
    ':prenom' => $prenom,
    ':nom' => $nom,
    ':age' => $age,
    ':poids' => $poids,
    ':email' => $email,
    ':motdepasse' => $motdepasse
  ]);

  echo "Inscription réussie !";
}
?>
