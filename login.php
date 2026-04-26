<?php
ob_start();
session_start();
include("config.php");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $pseudo = isset($_POST['pseudo']) ? trim($_POST['pseudo']) : "";
    $password = isset($_POST['password']) ? trim($_POST['password']) : "";

    if (!empty($pseudo) && !empty($password)) {
        try {
            $user = null;
            $role = "";
            $redirect = "";
            $id_column = ""; 

            // 1. Recherche Agriculteur
            $sql1 = "SELECT * FROM agriculteur WHERE pseudo = :p";
            $stmt1 = $conn->prepare($sql1);
            $stmt1->execute([':p' => $pseudo]);
            $res = $stmt1->fetch(PDO::FETCH_ASSOC);

            if ($res) {
                $user = $res;
                $role = "agriculteur";
                $redirect = "dashboard_agriculteur.php";
                $id_column = "id_agriculteur";
            } else {
                // 2. Recherche Ouvrier
                $sql2 = "SELECT * FROM ouvrier WHERE pseudo = :p";
                $stmt2 = $conn->prepare($sql2);
                $stmt2->execute([':p' => $pseudo]);
                $res = $stmt2->fetch(PDO::FETCH_ASSOC);

                if ($res) {
                    $user = $res;
                    $role = "ouvrier";
                    $redirect = "dashboard_ouvrier.php";
                    $id_column = "id_ouvrier";
                }
            }

            if ($user) {
                // Vérification du mot de passe (Clair ou Haché)
                if (password_verify($password, $user['password']) || $password === $user['password']) {
                    
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user[$id_column]; 
                    $_SESSION['pseudo'] = $user['pseudo'];
                    $_SESSION['role'] = $role;

                    header("Location: " . $redirect);
                    exit();
                } else {
                    $error = "Mot de passe incorrect.";
                }
            } else {
                $error = "Utilisateur introuvable.";
            }

        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Uber-Cueillette</title>
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

        /* Conteneur principal identique aux registres */
        .container-main {
            display: flex;
            max-width: 900px;
            width: 95%;
            margin: 40px auto;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            min-height: 500px;
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
            max-height: 250px;
            object-fit: cover;
            border-radius: 10px;
            margin-top: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .right-panel {
            flex: 1.2;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-title {
            color: #e65100;
            margin-bottom: 25px;
            font-size: 26px;
            border-bottom: 2px solid #fff3e0;
            padding-bottom: 10px;
            text-align: center;
        }

        .input-group { margin-bottom: 20px; }
        .input-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 15px;
        }

        .input-group input:focus {
            border-color: #e65100;
            outline: none;
            box-shadow: 0 0 5px rgba(230, 81, 0, 0.2);
        }

        .btn-area {
            text-align: center;
            margin-top: 10px;
        }

        .btn-submit {
            padding: 14px 60px;
            background: #e65100;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 6px rgba(230, 81, 0, 0.2);
        }

        .btn-submit:hover {
            background: #bf360c;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(230, 81, 0, 0.3);
        }

        .error-box {
            background-color: #ffebee; 
            color: #c62828; 
            padding: 12px; 
            border-radius: 8px; 
            margin-bottom: 20px; 
            border: 1px solid #ffcdd2;
            text-align: center;
            font-weight: bold;
        }

        .register-links {
            margin-top: 25px;
            text-align: center;
            font-size: 14px;
            color: #666;
        }

        .register-links a {
            color: #e65100;
            font-weight: bold;
            text-decoration: none;
        }
    </style>
</head>
<body>

<header>
    <h1>🌿 Uber-Cueillette</h1>
    <nav>
        <a href="index.php" style="background: rgba(255,255,255,0.2); padding: 8px 15px; border-radius: 5px;">Accueil</a>
    </nav>
</header>

<div class="container-main">
    <div class="left-panel">
        <h1>Bon retour !</h1>
        <p>Accédez à votre espace pour gérer vos offres ou vos missions de récolte.</p>
        <img src="https://images.unsplash.com/photo-1500382017468-9049fed747ef" alt="Champ">
    </div>

    <div class="right-panel">
        <h2 class="form-title">Connexion</h2>

        <?php if (!empty($error)): ?>
            <div class="error-box"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <div class="input-group">
                <input type="text" name="pseudo" placeholder="Nom d'utilisateur" required>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="Mot de passe" required>
            </div>

            <div class="btn-area">
                <button type="submit" class="btn-submit">Se connecter</button>
            </div>

            <div class="register-links">
                Pas encore inscrit ? <br>
                S'inscrire en tant qu' <a href="register_agriculteur.php">Agriculteur</a> ou <a href="register_ouvrier.php">Ouvrier</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
<?php
ob_end_flush();
?>