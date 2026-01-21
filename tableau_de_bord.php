<?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['id'])) {
    header("Location: connexion.html");
    exit;
}

$jours_fr = [
  'Monday' => 'Lundi',
  'Tuesday' => 'Mardi',
  'Wednesday' => 'Mercredi',
  'Thursday' => 'Jeudi',
  'Friday' => 'Vendredi',
  'Saturday' => 'Samedi',
  'Sunday' => 'Dimanche'
];

$id_utilisateur = $_SESSION['id'];

// Récupération du prénom
$stmt = $pdo->prepare("SELECT prenom FROM utilisateurs WHERE id = ?");
$stmt->execute([$id_utilisateur]);
$user = $stmt->fetch();
$prenom = $user ? htmlspecialchars($user['prenom']) : 'Utilisateur';

// Objectif + progression 
function getObjectifEtProgression($pdo, $id_utilisateur, $frequence) {
    $stmt = $pdo->prepare("SELECT valeur_objectif FROM objectifs WHERE utilisateur_id = ? AND type = 'nombre-seances' AND frequence = ?");
    $stmt->execute([$id_utilisateur, $frequence]);
    $objectif = $stmt->fetchColumn();

    if (!$objectif) return null;

    if ($frequence === 'hebdo') {
        $periodeSQL = "YEARWEEK(date, 1) = YEARWEEK(NOW(), 1)";
    } elseif ($frequence === 'mensuel') {
        $periodeSQL = "MONTH(date) = MONTH(NOW()) AND YEAR(date) = YEAR(NOW())";
    } elseif ($frequence === 'annuel') {
        $periodeSQL = "YEAR(date) = YEAR(NOW())";
    } else {
        return null;
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM seances WHERE utilisateur_id = ? AND $periodeSQL");
    $stmt->execute([$id_utilisateur]);
    $fait = $stmt->fetchColumn();

    return [
        'objectif' => $objectif,
        'fait' => $fait,
        'reste' => max(0, $objectif - $fait)
    ];
}

$hebdo = getObjectifEtProgression($pdo, $id_utilisateur, 'hebdo');
$mensuel = getObjectifEtProgression($pdo, $id_utilisateur, 'mensuel');
$annuel = getObjectifEtProgression($pdo, $id_utilisateur, 'annuel');

$stmt = $pdo->prepare("SELECT * FROM seances WHERE utilisateur_id = ?");
$stmt->execute([$id_utilisateur]);
$seances = $stmt->fetchAll();

$nb_semaine = 0;
$nb_mois = 0;
$nb_annee = 0;
$duree_totale = 0;
$jours = [];
$types = [];
$semaine_count = [];
$derniere_seance = null;

foreach ($seances as $s) {
    $date = new DateTime($s['date']);
    $duree = (int) $s['duree'];
    $type = $s['type'];
    $jour = $date->format('l');
    $annee = $date->format('Y');
    $mois = $date->format('m');
    $semaine = $date->format('W');
    $full_semaine = $annee . '-S' . $semaine;

    $duree_totale += $duree;

    if ($date->format('oW') === date('oW')) $nb_semaine++;
    if ($mois === date('m') && $annee === date('Y')) $nb_mois++;
    if ($annee === date('Y')) $nb_annee++;

    $types[$type] = ($types[$type] ?? 0) + 1;
    $jours[$jour] = ($jours[$jour] ?? 0) + 1;
    $semaine_count[$full_semaine] = ($semaine_count[$full_semaine] ?? 0) + 1;

    if (!$derniere_seance || $s['date'] > $derniere_seance['date']) {
        $derniere_seance = $s;
    }
}

$nb_total = count($seances);
$duree_moy = $nb_total ? round($duree_totale / $nb_total) : 0;
$semaine_actuelle = date('oW');
$semaine_derniere = date('oW', strtotime('-7 days'));
$nb_semaine_prec = $semaine_count[$semaine_derniere] ?? 0;
$evolution = $nb_semaine - $nb_semaine_prec;

$max_semaine = max($semaine_count ?: [0]);
$semaines_pleines = array_keys($semaine_count, $max_semaine);
$semaine_top = $semaines_pleines[0] ?? '-';

$type_frequent = $types ? array_search(max($types), $types) : '-';
arsort($jours);
$jours_favoris = array_slice(array_keys($jours), 0, 2);

// Répartition des types
$total_types = array_sum($types);
$repartition_types = [];

if ($total_types > 0) {
    foreach ($types as $type => $nb) {
        $pourcentage = round(($nb / $total_types) * 100);
        $repartition_types[ucfirst($type)] = $pourcentage;
    }
}

// Données pour le graphique
ksort($semaine_count);
$graph_labels = array_keys($semaine_count);
$graph_values = array_values($semaine_count);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Tableau de bord – MyProgress</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <nav>
    <div style="font-weight: bold; font-size: 1.2rem;">MyProgress</div>
    <div>
      <a href="tableau_de_bord.php">Accueil</a>
      <a href="ajouter_seance.html">Ajouter une séance</a>
      <a href="historique.html">Historique</a>
      <a href="objectifs.html">Objectifs</a>
      <a href="logout.php">Déconnexion</a>
    </div>
  </nav>

  <div class="contenu">

    <div class="carte">
      <h3>Bienvenue <?= $prenom ?> 👋</h3>
      <p>Tu es connecté. Prêt à suivre tes progrès ?</p>
    </div>

    <div class="carte">
      <h3>Objectifs actuels</h3>
      <?php if ($hebdo): ?>
        <div class="objectif-bloc">
          <p>Objectif hebdomadaire : <strong><?= $hebdo['objectif'] ?> séances</strong></p>
          <p class="ligne-stats">Réalisées cette semaine : <strong><?= $hebdo['fait'] ?></strong></p>
          <p class="<?= $hebdo['reste'] > 0 ? 'moyen' : 'ok' ?>">
            <?= $hebdo['reste'] > 0
                ? "Encore {$hebdo['reste']} à faire cette semaine"
                : "Objectif hebdo atteint 💪" ?>
          </p>
        </div>
      <?php endif; ?>

      <?php if ($mensuel): ?>
        <div class="objectif-bloc">
          <p>Objectif mensuel : <strong><?= $mensuel['objectif'] ?> séances</strong></p>
          <p class="ligne-stats">Réalisées ce mois-ci : <strong><?= $mensuel['fait'] ?></strong></p>
          <p class="<?= $mensuel['reste'] > 0 ? 'moyen' : 'ok' ?>">
            <?= $mensuel['reste'] > 0
                ? "Encore {$mensuel['reste']} à faire ce mois-ci"
                : "Objectif mensuel atteint 🔥" ?>
          </p>
        </div>
      <?php endif; ?>

      <?php if ($annuel): ?>
        <div class="objectif-bloc">
          <p>Objectif annuel : <strong><?= $annuel['objectif'] ?> séances</strong></p>
          <p class="ligne-stats">Réalisées cette année : <strong><?= $annuel['fait'] ?></strong></p>
          <p class="<?= $annuel['reste'] > 0 ? 'moyen' : 'ok' ?>">
            <?= $annuel['reste'] > 0
                ? "Encore {$annuel['reste']} à faire cette année"
                : "Objectif annuel atteint 🏆" ?>
          </p>
        </div>
      <?php endif; ?>
    </div>

    <div class="carte">
      <h3>Statistiques</h3>
      <p class="ligne-stats">Séances cette semaine : <strong><?= $nb_semaine ?></strong></p>
      <p class="ligne-stats">Séances ce mois-ci : <strong><?= $nb_mois ?></strong></p>
      <p class="ligne-stats">Séances cette année : <strong><?= $nb_annee ?></strong></p>
      <hr style="margin: 1rem 0;">
      <p class="ligne-stats">Durée totale : <strong><?= floor($duree_totale / 60) ?>h<?= $duree_totale % 60 ?></strong></p>
      <p class="ligne-stats">Durée moyenne par séance : <strong><?= $duree_moy ?> minutes</strong></p>
      <p class="ligne-stats">Semaine la plus active : <strong><?= $semaine_top ?> (<?= $max_semaine ?> séances)</strong></p>
      <p class="ligne-stats">Activité la plus fréquente : <strong><?= ucfirst($type_frequent) ?></strong></p>
      <p class="ligne-stats">
        Dernière séance :
        <?php if ($derniere_seance): ?>
          <strong><?= date('d/m', strtotime($derniere_seance['date'])) ?> – <?= ucfirst($derniere_seance['type']) ?> – <?= $derniere_seance['duree'] ?> min – <?= ucfirst($derniere_seance['intensite']) ?></strong>
        <?php else: ?>
          <strong>Aucune</strong>
        <?php endif; ?>
      </p>
      <p class="ligne-stats">
        Jours préférés :
        <strong><?= implode(', ', array_map(fn($j) => $jours_fr[$j] ?? $j, $jours_favoris)) ?></strong>
      </p>
      <p class="ligne-stats">
        Évolution vs semaine dernière :
        <span class="<?= $evolution >= 0 ? 'ok' : 'moyen' ?>">
          <?= ($evolution >= 0 ? '+' : '') . $evolution ?> séance<?= abs($evolution) > 1 ? 's' : '' ?>
        </span>
      </p>
    </div>

    <div class="carte">
      <h3>Répartition des types de séances</h3>
      <?php if (!empty($repartition_types)): ?>
        <ul>
          <?php foreach ($repartition_types as $type => $pourcent): ?>
            <li><?= $type ?> : <?= $pourcent ?>%</li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p>Aucune séance enregistrée.</p>
      <?php endif; ?>
    </div>

    <div class="carte">
      <h3>Progression visuelle</h3>
      <canvas id="graphiqueProgression" height="100"></canvas>
    </div>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const ctx = document.getElementById('graphiqueProgression').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($graph_labels) ?>,
            datasets: [{
                label: 'Séances par semaine',
                data: <?= json_encode($graph_values) ?>,
                backgroundColor: 'rgba(0, 123, 255, 0.6)',
                borderColor: 'rgba(0, 123, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            }
        }
    });
  </script>

</body>
</html>
