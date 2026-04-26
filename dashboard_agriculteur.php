<?php
session_start();
require_once("config.php");

// 1. Vérification de l'accès (Rôle agriculteur uniquement)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "agriculteur") {
    header("Location: login.php");
    exit;
}

$id_agri = $_SESSION['user_id'];

// 2. Récupération des informations du profil de l'agriculteur
$stmt_profil = $conn->prepare("SELECT * FROM agriculteur WHERE id_agriculteur = ?");
$stmt_profil->execute([$id_agri]);
$profil = $stmt_profil->fetch(PDO::FETCH_ASSOC);

// 3. Récupération des offres de l'agriculteur
$sql = "SELECT o.*, f.libelle as fruit, g.libelle as gouv 
        FROM offre o 
        JOIN type_fruit f ON o.id_type_fruit = f.id_type_fruit
        JOIN gouvernorat g ON o.id_gouvernorat = g.id_gouvernorat
        WHERE o.id_agriculteur = ?
        ORDER BY o.date_limite DESC";
$stmt = $conn->prepare($sql);
$stmt->execute([$id_agri]);
$offres = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Agriculteur - Uber-Cueillette</title>
    <link rel="stylesheet" href="dashboard_agriculteur.css">
    <style>
        .status-complet { color: #d32f2f; font-weight: bold; }
        .status-ouvert { color: #e65100; font-weight: bold; }
        .badge-cloture { background: #f44336; color: white; padding: 2px 5px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .msg-alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; text-align: center; }
        .msg-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .msg-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<header>
    <h1>🌿 Uber-Cueillette</h1>
    <nav>
        <a href="dashboard_agriculteur.php">Mon Dashboard</a>
        <a href="ajouter_offre.php">Publier une Offre</a>
        <a href="logout.php">Déconnexion</a>
    </nav>
</header>

<div class="container" style="max-width: 1100px; margin: 20px auto; padding: 0 20px;">
    
    <?php if (isset($_GET['msg'])): ?>
        <div class="msg-alert <?= ($_GET['msg'] == 'deleted') ? 'msg-success' : 'msg-error' ?>">
            <?php 
                if($_GET['msg'] == 'deleted') echo "✅ L'offre a été supprimée avec succès.";
                if($_GET['msg'] == 'has_workers') echo "❌ Suppression impossible : des ouvriers ont déjà été acceptés.";
            ?>
        </div>
    <?php endif; ?>

    <section id="profil" style="background:#fff; padding:20px; border-radius:10px; margin-bottom:30px; box-shadow:0 4px 6px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2>Bienvenue, <?= htmlspecialchars($profil['nom'] . ' ' . $profil['prenom']) ?> 👋</h2>
                <p style="margin-top: 10px;"><b>📧 Email :</b> <?= htmlspecialchars($profil['email']) ?></p>
                <p><b>📍 Adresse :</b> <?= htmlspecialchars($profil['adresse']) ?></p>
            </div>
            <a href="modifier_profil_agri.php">
                <button class="btn" style="background: #e65100; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer;">Modifier mon profil</button>
            </a>
        </div>
    </section>

    <section id="mesoffres">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h2>Mes récoltes en cours</h2>
            <a href="ajouter_offre.php" style="text-decoration: none;">
                <button class="btn" style="background: #bf360c; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer;">+ Nouvelle Offre</button>
            </a>
        </div>

        <table class="table" style="width:100%; border-collapse: collapse; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <thead>
                <tr style="background: #fff3e0; text-align: left; border-bottom: 2px solid #e65100;">
                    <th style="padding: 15px;">Fruit</th>
                    <th style="padding: 15px;">Région</th>
                    <th style="padding: 15px;">Recrutement (Admis/Total)</th>
                    <th style="padding: 15px;">Salaire/Jour</th>
                    <th style="padding: 15px;">Date Limite</th>
                    <th style="padding: 15px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($offres as $o): 
                    // Compter les ouvriers déjà acceptés pour cette offre
                    $st = $conn->prepare("SELECT COUNT(*) FROM candidature WHERE id_offre = ? AND decision = 'Accepte'");
                    $st->execute([$o['id_offre']]);
                    $acceptes = $st->fetchColumn();
                    
                    $expiree = (strtotime($o['date_limite']) < time());
                    $complet = ($acceptes >= $o['nombre_ouvriers']);
                ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 15px; font-weight: bold;"><?= htmlspecialchars($o['fruit']) ?></td>
                    <td style="padding: 15px;"><?= htmlspecialchars($o['gouv']) ?></td>
                    <td style="padding: 15px;">
                        <span class="<?= $complet ? 'status-complet' : 'status-ouvert' ?>">
                            <?= $acceptes ?> / <?= $o['nombre_ouvriers'] ?>
                        </span>
                        <?php if ($complet || $expiree): ?>
                            <br><span class="badge-cloture">OFFRE CLÔTURÉE</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 15px;"><?= number_format($o['prix_journee'], 2) ?> DT</td>
                    <td style="padding: 15px;"><?= date('d/m/Y', strtotime($o['date_limite'])) ?></td>
                    <td style="padding: 15px; display: flex; gap: 10px;">
                        <a href="voir_postulants.php?id=<?= $o['id_offre'] ?>">
                            <button class="btn" style="padding: 5px 12px; cursor: pointer;">Gérer Postulants</button>
                        </a>
                        
                        <?php if($acceptes == 0): // Suppression autorisée seulement si 0 ouvrier accepté ?>
                            <a href="supprimer_offre.php?id=<?= $o['id_offre'] ?>" onclick="return confirm('Voulez-vous vraiment supprimer cette offre ?')">
                                <button class="btn delete" style="padding: 5px 12px; background: #c62828; color: white; border: none; border-radius: 3px; cursor: pointer;">Supprimer</button>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (empty($offres)): ?>
                <tr>
                    <td colspan="6" style="padding: 40px; text-align: center; color: #888;">
                        Vous n'avez publié aucune offre de récolte pour le moment.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</div>

</body>
</html>