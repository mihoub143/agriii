<?php
session_start();
include("config.php");

$message = "";

// 1. Ajouter un Type de Fruit
if (isset($_POST['add_fruit'])) {
    $libelle = trim($_POST['libelle_fruit']);
    if (!empty($libelle)) {
        try {
            $stmt = $conn->prepare("INSERT INTO type_fruit (libelle) VALUES (:libelle)");
            $stmt->execute([':libelle' => $libelle]);
            $message = "✅ Fruit ajouté avec succès !";
        } catch (PDOException $e) {
            $message = "❌ Erreur : " . $e->getMessage();
        }
    }
}

// 2. Ajouter un Gouvernorat
if (isset($_POST['add_gouv'])) {
    $libelle = trim($_POST['libelle_gouv']);
    if (!empty($libelle)) {
        try {
            $stmt = $conn->prepare("INSERT INTO gouvernorat (libelle) VALUES (:libelle)");
            $stmt->execute([':libelle' => $libelle]);
            $message = "✅ Gouvernorat ajouté avec succès !";
        } catch (PDOException $e) {
            $message = "❌ Erreur : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - Uber Cueillette</title>
    <link rel="stylesheet" href="ajouter_offre.css"> <style>
        .admin-container { display: flex; flex-direction: column; gap: 20px; padding: 20px; }
        .section-admin { background: #f9f9f9; padding: 15px; border-radius: 8px; border: 1px solid #ddd; }
h3 { color: #e65100; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="container" style="flex-direction: column; align-items: stretch; height: auto; margin: 20px;">
    <div class="right" style="width: 100%;">
        <div class="form">
            <h2>⚙️ Panneau d'Administration</h2>
            
            <?php if($message): ?>
                <p style="text-align: center; font-weight: bold; padding: 10px;"><?php echo $message; ?></p>
            <?php endif; ?>

            <div class="admin-container">
                <div class="section-admin">
                    <h3>Ajouter un Fruit</h3>
                    <form method="POST">
                        <input type="text" name="libelle_fruit" placeholder="Nom du fruit (ex: Dates, Pommes)" required>
                        <button type="submit" name="add_fruit">Enregistrer le fruit</button>
                    </form>
                </div>

                <div class="section-admin">
                    <h3>Ajouter un Gouvernorat</h3>
                    <form method="POST">
                        <input type="text" name="libelle_gouv" placeholder="Nom du gouvernorat (ex: Béja, Jendouba)" required>
                        <button type="submit" name="add_gouv">Enregistrer le gouvernorat</button>
                    </form>
                </div>
            </div>

            <p style="text-align: center; margin-top: 20px;">
                <a href="ajouter_offre.php" style="color: #e65100; text-decoration: none; font-weight: bold;">← Retour aux offres</a>
            </p>
        </div>
    </div>
</div>

</body>
</html>
