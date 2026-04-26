<?php
session_start();
include("config.php");

// Vérifier connexion (Rôle agriculteur)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "agriculteur"){
    header("Location: login.php");
    exit();
}

$agriculteur_id = $_SESSION['user_id'];
$error = "";

// --- NOUVEAU : Récupération dynamique des données pour les listes ---
try {
    // Récupérer les fruits
    $stmt_fruits = $conn->query("SELECT id_type_fruit, libelle FROM type_fruit ORDER BY libelle ASC");
    $fruits = $stmt_fruits->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les gouvernorats
    $stmt_gouv = $conn->query("SELECT id_gouvernorat, libelle FROM gouvernorat ORDER BY libelle ASC");
    $gouvernorats = $stmt_gouv->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "❌ Erreur de chargement des listes : " . $e->getMessage();
}

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_type_fruit = $_POST['id_type_fruit'] ?? "";
    $id_gouvernorat = $_POST['id_gouvernorat'] ?? "";
    $adresse = trim($_POST['adresse'] ?? "");
    $date_debut = $_POST['date_debut'] ?? "";
    $date_fin = $_POST['date_fin'] ?? "";
    $nombre_ouvriers = (int) ($_POST['nombre_ouvriers'] ?? 0);
    $prix_journee = (float) ($_POST['prix_journee'] ?? 0);
    $date_limite = $_POST['date_limite'] ?? "";

    // Validation
    if (
        empty($id_type_fruit) || empty($id_gouvernorat) || empty($adresse) ||
        empty($date_debut) || empty($date_fin) || empty($nombre_ouvriers) ||
        empty($prix_journee) || empty($date_limite)
    ) {
        $error = "❌ Tous les champs sont obligatoires";
    }
    elseif ($date_debut > $date_fin) {
        $error = "❌ La date de début doit être antérieure à la date de fin";
    }
    else {
        try {
            $sql = "INSERT INTO offre 
            (id_type_fruit, id_gouvernorat, adresse, date_debut, date_fin, nombre_ouvriers, prix_journee, date_limite, id_agriculteur)
            VALUES 
            (:id_type_fruit, :id_gouvernorat, :adresse, :date_debut, :date_fin, :nombre_ouvriers, :prix_journee, :date_limite, :id_agriculteur)";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':id_type_fruit'  => $id_type_fruit,
                ':id_gouvernorat' => $id_gouvernorat,
                ':adresse'        => $adresse,
                ':date_debut'     => $date_debut,
                ':date_fin'       => $date_fin,
                ':nombre_ouvriers'=> $nombre_ouvriers,
                ':prix_journee'   => $prix_journee,
                ':date_limite'    => $date_limite,
                ':id_agriculteur' => $agriculteur_id
            ]);

            header("Location: dashboard_agriculteur.php");
            exit();

        } catch (PDOException $e) {
            $error = "❌ Erreur SQL : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une offre - Uber Cueillette</title>
    <link rel="stylesheet" href="ajouter_offre.css">
</head>
<body>

<div class="container">
    <div class="left">
        <h1>🌾 Uber Cueillette</h1>
        <p>Publiez vos besoins en main-d'œuvre pour vos récoltes et trouvez les meilleurs ouvriers rapidement.</p>
        <img src="ajouter_offre.png" alt="Recolte">
    </div>

    <div class="right">
        <div class="form">
            <h2>Publier une offre</h2>
            
            <?php if($error): ?>
                <p style="color: red; text-align: center; margin-bottom: 10px;"><?php echo $error; ?></p>
            <?php endif; ?>

            <form method="POST" action="">
                <label>Type de fruit</label>
                <select name="id_type_fruit" required>
                    <option value="">Choisir un fruit</option>
                    <?php foreach($fruits as $fruit): ?>
                        <option value="<?php echo $fruit['id_type_fruit']; ?>">
                            <?php echo htmlspecialchars($fruit['libelle']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Gouvernorat</label>
                <select name="id_gouvernorat" required>
                    <option value="">Choisir une région</option>
                    <?php foreach($gouvernorats as $gouv): ?>
                        <option value="<?php echo $gouv['id_gouvernorat']; ?>">
                            <?php echo htmlspecialchars($gouv['libelle']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Lieu exact</label>
                <input type="text" name="adresse" placeholder="Ex: Ferme El Hana, Route de Tunis" required>

                <div style="display: flex; gap: 10px;">
                    <div style="flex: 1;">
                        <label>Date début</label>
                        <input type="date" name="date_debut" required>
                    </div>
                    <div style="flex: 1;">
                        <label>Date fin</label>
                        <input type="date" name="date_fin" required>
                    </div>
                </div>

                <div style="display: flex; gap: 10px;">
                    <div style="flex: 1;">
                        <label>Ouvriers</label>
                        <input type="number" name="nombre_ouvriers" placeholder="Nb" required>
                    </div>
                    <div style="flex: 1;">
                        <label>Prix/Jour (DT)</label>
                        <input type="number" step="0.01" name="prix_journee" placeholder="Prix" required>
                    </div>
                </div>

                <label>Date limite candidature</label>
                <input type="date" name="date_limite" required>

                <button type="submit">Publier l'offre</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>