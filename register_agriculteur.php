<?php
include("config.php");

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $cin = $_POST['cin'];
    $email = $_POST['email'];
    $adresse = $_POST['adresse'];
    $pseudo = $_POST['pseudo'];
    $password = $_POST['password']; // Texte clair

    try {
        $sql = "INSERT INTO agriculteur (nom, prenom, cin, email, adresse, pseudo, password)
                VALUES (:nom, :prenom, :cin, :email, :adresse, :pseudo, :password)";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':cin' => $cin,
            ':email' => $email,
            ':adresse' => $adresse,
            ':pseudo' => $pseudo,
            ':password' => $password
        ]);

        $msg = "success";
    } catch (PDOException $e) {
        $msg = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription Agriculteur - Uber-Cueillette</title>
    <link rel="stylesheet" href="dashboard_agriculteur.css">
    <style>
        body {
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Conteneur principal identique à l'ouvrier */
        .container-main {
            display: flex;
            max-width: 1000px;
            width: 95%;
            margin: 20px auto;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            min-height: 600px;
        }

        .left-panel {
            flex: 1;
            background: #e65100;
            color: white;
            padding: 30px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .left-panel img {
            width: 100%;
            max-height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-top: 20px;
        }

        .right-panel {
            flex: 1.2;
            padding: 30px;
            overflow-y: auto;
            max-height: 85vh;
        }

        .form-title {
            color: #e65100;
            margin-bottom: 20px;
            font-size: 22px;
            border-bottom: 2px solid #fff3e0;
            padding-bottom: 10px;
        }

        .input-group { margin-bottom: 15px; }
        .input-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
        }

        /* Bouton avec retouches accueil */
        .btn-area {
            text-align: center;
            margin-top: 20px;
            padding-bottom: 20px;
        }

        .btn-submit {
            padding: 12px 50px;
            background: #e65100;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            text-transform: uppercase;
        }

        .btn-submit:hover {
            background: #bf360c;
            transform: scale(1.02);
        }

        .alert { padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<header>
    <h1>🌿 Uber-Cueillette</h1>
    <nav>
        <a href="index.php" style="background: rgba(255,255,255,0.2); padding: 8px 15px; border-radius: 5px;">Accueil</a>
        <a href="login.php">Connexion</a>
    </nav>
</header>

<div class="container-main">
    <div class="left-panel">
        <h1>🌾 Espace Agriculteur</h1>
        <p>Inscrivez-vous pour publier vos offres et trouver de la main d'œuvre qualifiée.</p>
        <img src="https://images.unsplash.com/photo-1500595046743-cd271d694d30" alt="Ferme">
    </div>

    <div class="right-panel">
        <h2 class="form-title">Inscription Agriculteur</h2>

        <?php if($msg == "success"): ?>
            <div class='alert success'>✅ Inscription réussie ! <a href="login.php">Se connecter</a></div>
        <?php elseif($msg == "error"): ?>
            <div class='alert error'>❌ Une erreur est survenue lors de l'inscription.</div>
        <?php endif; ?>

        <form action="register_agriculteur.php" method="POST">
            <div class="input-group">
                <input type="text" name="nom" placeholder="Nom" required>
            </div>
            <div class="input-group">
                <input type="text" name="prenom" placeholder="Prénom" required>
            </div>
            <div class="input-group">
                <input type="text" name="cin" placeholder="CIN (8 chiffres)" required maxlength="8">
            </div>
            <div class="input-group">
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="input-group">
                <input type="text" name="adresse" placeholder="Adresse de l'exploitation" required>
            </div>
            <div class="input-group">
                <input type="text" name="pseudo" placeholder="Nom d'utilisateur" required>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="Mot de passe" required>
            </div>

            <div class="btn-area">
                <button type="submit" class="btn-submit">S'inscrire</button>
            </div>
            
            <p style="text-align: center; color: #666;">
                Déjà inscrit ? <a href="login.php" style="color: #e65100; font-weight: bold; text-decoration: none;">Se connecter</a>
            </p>
        </form>
    </div>
</div>

</body>
</html>