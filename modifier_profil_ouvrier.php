<?php
session_start();
require_once("config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "ouvrier") {
    header("Location: login.php");
    exit;
}

$id_ouv = $_SESSION['user_id'];
$msg = "";

// 1. Charger les données actuelles
$stmt = $conn->prepare("SELECT * FROM ouvrier WHERE id_ouvrier = ?");
$stmt->execute([$id_ouv]);
$user = $stmt->fetch();

// 2. Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $desc = $_POST['description'];

    try {
        // Mise à jour des textes
        $sql = "UPDATE ouvrier SET nom = ?, prenom = ?, email = ?, description = ? WHERE id_ouvrier = ?";
        $params = [$nom, $prenom, $email, $desc, $id_ouv];
        $conn->prepare($sql)->execute($params);

        // Gestion de la nouvelle photo si téléchargée
        if (!empty($_FILES['photo']['tmp_name'])) {
            $photoData = file_get_contents($_FILES['photo']['tmp_name']);
            $stmt_img = $conn->prepare("UPDATE ouvrier SET photo = ? WHERE id_ouvrier = ?");
            $stmt_img->bindParam(1, $photoData, PDO::PARAM_LOB);
            $stmt_img->bindParam(2, $id_ouv);
            $stmt_img->execute();
        }

        $msg = "✅ Profil mis à jour avec succès !";
        // Rafraîchir les données locales
        header("Refresh:1");
    } catch (PDOException $e) {
        $msg = "❌ Erreur : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Profil Ouvrier</title>
    <link rel="stylesheet" href="ajouter_offre.css"> </head>
<body>
    <div class="container">
        <form method="POST" enctype="multipart/form-data" class="form" style="padding:40px;">
            <h2>Modifier mon profil</h2>
            <?php if($msg) echo "<p>$msg</p>"; ?>
            
            <label>Nom</label>
            <input type="text" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required>
            
            <label>Prénom</label>
            <input type="text" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required>
            
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            
            <label>Description (Expériences)</label>
            <textarea name="description" style="width:100%; border-radius:8px; padding:10px;"><?= htmlspecialchars($user['description']) ?></textarea>
            
            <label>Nouvelle Photo (Optionnel)</label>
            <input type="file" name="photo" accept="image/*">
            
            <button type="submit">Enregistrer les modifications</button>
            <br><br>
            <a href="dashboard_ouvrier.php">Retour au Dashboard</a>
        </form>
    </div>
</body>
</html>