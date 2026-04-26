<?php
require_once("config.php");

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $cin = $_POST['cin'];
    $email = $_POST['email'];
    $pseudo = $_POST['pseudo'];
    $password = $_POST['password']; 
    $desc = $_POST['description'] ?? '';

    try {
        if (!empty($_FILES['photo']['tmp_name'])) {
            $file = $_FILES['photo'];
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $file_type = mime_content_type($file['tmp_name']);
            $allowed_ext = ['jpg', 'jpeg', 'png'];

            if (in_array($extension, $allowed_ext)) {
                $photoData = file_get_contents($file['tmp_name']);
                $sql = "INSERT INTO ouvrier (nom, prenom, CIN, email, pseudo, password, description, photo) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(1, $nom);
                $stmt->bindParam(2, $prenom);
                $stmt->bindParam(3, $cin);
                $stmt->bindParam(4, $email);
                $stmt->bindParam(5, $pseudo);
                $stmt->bindParam(6, $password);
                $stmt->bindParam(7, $desc);
                $stmt->bindParam(8, $photoData, PDO::PARAM_LOB);
                
                if ($stmt->execute()) {
                    header("Location: login.php?success=1");
                    exit;
                }
            } else { $msg = "format"; }
        } else { $msg = "photo"; }
    } catch (PDOException $e) { $msg = "db"; }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription Ouvrier - Uber-Cueillette</title>
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

        /* Conteneur principal flexible */
        .container-main {
            display: flex;
            max-width: 1000px;
            width: 95%;
            margin: 20px auto;
            background: white;
            border-radius: 15px;
            overflow: hidden; /* Empêche le contenu de dépasser du cadre arrondi */
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
            max-height: 200px; /* Réduit pour laisser de la place */
            object-fit: cover;
            border-radius: 10px;
            margin-top: 20px;
        }

        .right-panel {
            flex: 1.2;
            padding: 30px;
            overflow-y: auto; /* Ajoute un scroll si le formulaire est trop long */
            max-height: 85vh; /* Empêche la page de devenir infinie */
        }

        .form-title {
            color: #e65100;
            margin-bottom: 20px;
            font-size: 22px;
            border-bottom: 2px solid #fff3e0;
            padding-bottom: 10px;
        }

        .input-group { margin-bottom: 15px; }
        .input-group label { display: block; font-weight: bold; margin-bottom: 5px; color: #e65100; }
        .input-group input, .input-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
        }

        /* Centre le bouton et le rend visible */
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

        .alert { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; }
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
        <h1>🚜 Inscription Ouvrier</h1>
        <p>Postulez aux offres et gérez vos missions facilement.</p>
        <img src="https://images.unsplash.com/photo-1595113316349-9fa4ee24f884?q=80&w=1000" alt="Ouvrier">
    </div>

    <div class="right-panel">
        <h2 class="form-title">Créer un compte</h2>

        <?php if($msg == "format") echo "<div class='alert'>❌ JPG ou PNG uniquement.</div>"; ?>
        <?php if($msg == "photo") echo "<div class='alert'>❌ La photo est obligatoire.</div>"; ?>

        <form action="register_ouvrier.php" method="POST" enctype="multipart/form-data">
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
                <input type="text" name="pseudo" placeholder="Nom d'utilisateur" required>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="Mot de passe" required>
            </div>
            <div class="input-group">
                <textarea name="description" placeholder="Votre expérience..." rows="2"></textarea>
            </div>
            <div class="input-group">
                <label>Photo de profil</label>
                <input type="file" name="photo" accept="image/jpeg, image/png" required>
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
