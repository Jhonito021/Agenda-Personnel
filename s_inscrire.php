<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="CSS/style_log.css">
    <link rel="stylesheet" href="CSS/animation.css">
    <link rel="stylesheet" href="CSS/style_sombre.css">
</head>
<bdy>
    <div class="container">
        <div class="box_log">
            <img src="image/metallica.jpg" alt="image" class="back_img">
            <h1 class="title_top">S'inscrire</h1>
           
            <form action="PHP/log.php" method="POST">
                <input type="text" placeholder="Nom" id="nom" name="nom" required>
                <input type="text" placeholder="Prenom" id="prenom" name="prenom" required>
                <input type="email" placeholder="email" id="email" name="email" required>
                <input type="password" placeholder="Mot de passe" id="mdp" name="mdp" required>
                <input type="password" placeholder="Confirmer le mot de passe" id="confirmation" name="confirmation" required>

                <div class="btn">
                    <button id="enregistrer" type="submit"><a href="#">Enregistrer</a></button>
                    <button id="dejaCompte"><a href="se_connecter.php">J'ai déja un compte</a></button>
                </div>
            </form>
            
            <div class="logo_bottom">
                <a href="Acceuil.php"><img src="image/metallica.jpg" alt="image" class="img_logo_bottom"></a>
            </div>
        </div>
    </div>
    <button id="btnChange">yoh</button>
    <script src="JavaScript/script_sombre.js"></script>
</body>
</html>