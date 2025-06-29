<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sig In</title>
    <link rel="stylesheet" href="CSS/style_log.css">
    <link rel="stylesheet" href="CSS/animation.css">
    <link rel="stylesheet" href="CSS/style_sombre.css">
</head>
<body>
    <div class="container">
        <div class="box_log">
            <img src="image/metallica.jpg" alt="image" class="back_img">
            
            <h1 class="title_top">Se connecter</h1>
            <div class="logo_top">
                <a href="Acceuil.php"><img src="image/metallica.jpg" alt="image" class="img_logo_top"></a>
            </div>

            <form action="PHP/log.php" method="POST">
                <input type="text" placeholder="email" id="email_1" name="email" required>
                <input type="password" placeholder="Mot de passe" id="mdp_1" name="mdp" required>

                <div class="btn">
                    <button id="connexion" type="submit"><a href="#">se connecter</a></button>
                    <button id="s_inscrire" type="submit"><a href="s_inscrire.php">s'inscrire</a></button>
                </div>
            </form>
        </div>
    </div>
    <button id="btnChange">yoh</button>
    <script src="JavaScript/script_sombre.js"></script>
</body>
</html>