<?php
session_start();
require_once("config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "ouvrier") {
    header("Location: login.php");
    exit;
}

$id_ouv = $_SESSION['user_id'];

// 1. RÉCUPÉRER LES OFFRES DISPONIBLES (Non clôturées et non postulées)
// Une offre est disponible si : date_limite >= aujourd'hui ET quota non atteint ET non postulé
$sql_dispo = "SELECT o.*, f.libelle as fruit, g.libelle as gouv,
             (SELECT COUNT(*) FROM candidature WHERE id_offre = o.id_offre AND decision = 'Accepte') as admis
             FROM offre o 
             JOIN type_fruit f ON o.id_type_fruit = f.id_type_fruit
             JOIN gouvernorat g ON o.id_gouvernorat = g.id_gouvernorat
             WHERE o.date_limite >= CURDATE() 
             AND o.id_offre NOT IN (SELECT id_offre FROM candidature WHERE id_ouvrier = ?)
             HAVING admis < o.nombre_ouvriers";

$stmt_dispo = $conn->prepare($sql_dispo);
$stmt_dispo->execute([$id_ouv]);
$offres_disponibles = $stmt_dispo->fetchAll(PDO::FETCH_ASSOC);

// 2. RÉCUPÉRER LES OFFRES POSTULÉES (En attente, Acceptées ou Refusées)
$sql_postule = "SELECT c.*, o.prix_journee, f.libelle as fruit, g.libelle as gouv
                FROM candidature c
                JOIN offre o ON c.id_offre = o.id_offre
                JOIN type_fruit f ON o.id_type_fruit = f.id_type_fruit
                JOIN gouvernorat g ON o.id_gouvernorat = g.id_gouvernorat
                WHERE c.id_ouvrier = ? AND (c.notr IS NULL OR c.notr = 0)";

$stmt_postule = $conn->prepare($sql_postule);
$stmt_postule->execute([$id_ouv]);
$mes_candidatures = $stmt_postule->fetchAll(PDO::FETCH_ASSOC);

// 3. RÉCUPÉRER LES CHANTIERS CLÔTURÉS (Participations terminées avec note/gain)
$sql_cloture = "SELECT c.*, f.libelle as fruit, o.adresse
                FROM candidature c
                JOIN offre o ON c.id_offre = o.id_offre
                JOIN type_fruit f ON o.id_type_fruit = f.id_type_fruit
                WHERE c.id_ouvrier = ? AND c.decision = 'Accepte' AND c.notr > 0";

$stmt_cloture = $conn->prepare($sql_cloture);
$stmt_cloture->execute([$id_ouv]);
$mes_chantiers = $stmt_cloture->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Espace Ouvrier</title>
    <link rel="stylesheet" href="dashboard_ouvrier.css">
    <style>
        .section-box { background: white; padding: 20px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
        .En-cours { background: #fff3e0; color: #ef6c00; }
        .Accepte { background: #fff3e0; color: #e65100; }
        .Refuse { background: #ffebee; color: #c62828; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .card { border: 1px solid #eee; padding: 15px; border-radius: 8px; }
    </style>
</head>
<body>

<header>
    <h1>🌿 Uber-Cueillette</h1>
    <nav>
        <a href="#offres">Offres Disponibles</a>
        <a href="#postule">Mes Postulations</a>
        <a href="#cloture">Historique/Gains</a>
        <a href="modifier_profil_ouvrier.php">Mon Profil</a>
        <a href="logout.php">Déconnexion</a>
    </nav>
</header>

<div class="container" style="max-width: 1200px; margin: 20px auto; padding: 20px;">

    <section id="offres" class="section-box">
        <h2>🚜 Offres de récolte disponibles</h2>
        <div class="grid">
            <?php foreach($offres_disponibles as $o): ?>
            <div class="card">
                <h3><?= htmlspecialchars($o['fruit']) ?></h3>
                <p>📍 <?= htmlspecialchars($o['gouv']) ?> (<?= htmlspecialchars($o['adresse']) ?>)</p>
                <p>💰 <b><?= $o['prix_journee'] ?> DT / jour</b></p>
                <p>⌛ Jusqu'au : <?= date('d/m', strtotime($o['date_limite'])) ?></p>
                <a href="postuler.php?id=<?= $o['id_offre'] ?>">
                    <button style="width:100%; padding:8px; background:#e65100; color:white; border:none; border-radius:5px; cursor:pointer; margin-top:10px;">Postuler en 1 clic</button>
                </a>
            </div>
            <?php endforeach; ?>
            <?php if(empty($offres_disponibles)) echo "<p>Aucune nouvelle offre disponible pour le moment.</p>"; ?>
        </div>
    </section>

    <section id="postule" class="section-box">
        <h2>📩 Mes candidatures envoyées</h2>
        <table style="width:100%; border-collapse: collapse;">
            <tr style="text-align:left; border-bottom: 2px solid #e65100;">
                <th style="padding:10px;">Récolte</th>
                <th>Région</th>
                <th>Prix</th>
                <th>Statut</th>
            </tr>
            <?php foreach($mes_candidatures as $c): ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding:10px;"><?= htmlspecialchars($c['fruit']) ?></td>
                <td><?= htmlspecialchars($c['gouv']) ?></td>
                <td><?= $c['prix_journee'] ?> DT</td>
                <td><span class="status-badge <?= str_replace(' ', '-', $c['decision']) ?>"><?= $c['decision'] ?></span></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </section>

    <section id="cloture" class="section-box">
        <h2>✅ Chantiers terminés & Gains</h2>
        <table style="width:100%; border-collapse: collapse;">
            <tr style="text-align:left; border-bottom: 2px solid #bf360c;">
                <th style="padding:10px;">Chantier</th>
                <th>Note reçue</th>
                <th>Rémunération</th>
                <th>Commentaire Agriculteur</th>
            </tr>
            <?php foreach($mes_chantiers as $m): ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding:10px;"><?= htmlspecialchars($m['fruit']) ?></td>
                <td><b style="color:#fbc02d;">⭐ <?= $m['notr'] ?>/10</b></td>
                <td style="color:#e65100; font-weight:bold;"><?= $m['remuneration'] ?> DT</td>
                <td style="font-style:italic;">"<?= htmlspecialchars($m['commentaire']) ?>"</td>
            </tr>
            <?php endforeach; ?>
        </table>
    </section>

</div>

</body>
</html>