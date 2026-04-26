<?php
session_start();
require_once("config.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "agriculteur") {
    header("Location: login.php");
    exit;
}

$id_agri = $_SESSION['user_id'];
$msg = "";

$stmt = $conn->prepare("SELECT * FROM agriculteur WHERE id_agriculteur = ?");
$stmt->execute([$id_agri]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $adresse = $_POST['adresse'];

    try {
        $sql = "UPDATE agriculteur SET nom = ?, prenom = ?, email = ?, adresse = ? WHERE id_agriculteur = ?";
        $conn->prepare($sql)->execute([$nom, $prenom, $email, $adresse, $id_agri]);
        $msg = "✅ Informations enregistrées !";
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
    <title>Modifier Profil Agriculteur</title>
    <link rel="stylesheet" href="ajouter_offre.css">
</head>
<body>
    <div class="container">
        <form method="POST" class="form" style="padding:40px;">
            <h2>Mon Profil Agriculteur</h2>
            <?php if($msg) echo "<p>$msg</p>"; ?>
            
            <label>Nom</label>
            <input type="text" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required>
            
            <label>Prénom</label>
            <input type="text" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required>
            
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            
            <label>Adresse du siège</label>
            <input type="text" name="adresse" value="<?= htmlspecialchars($user['adresse']) ?>" required>
            
            <button type="submit">Mettre à jour</button>
            <br><br>
            <a href="dashboard_agriculteur.php">Retour</a>
        </form>
    </div>
</body>
</html>