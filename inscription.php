<?php

session_start();
include('service/bd.php');

if(isset($_SESSION['connected'])){
    echo '<script>
            window.location.href = "./chat.php";
         </script>';
    exit;
}

if(isset($_POST['inscription'])){

    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $mdp = $_POST['mdp'];

    $verif = $connexion->prepare("SELECT count(*) FROM utilisateur WHERE email = ?");

    $verif->bind_param('s',$email);
    $verif->execute();
    $verif->store_result();
    $verif->bind_result($num_rows);
    $verif->fetch();
        
            if ($num_rows > 0) {
                //clientVip existe
                echo '<script>alert("Cet e-mail déjà associé à un compte");</script>';

            }else {
                
                $req = $connexion->prepare("INSERT INTO utilisateur (nom,prenom,email,mdp)
                                    VALUES (?,?,?,?);");
                $req->bind_param('ssss',$nom,$prenom,$email,md5($mdp));
                if($req->execute()){
                    $_SESSION['id'] = $id;
                    $_SESSION['nom'] = $nom;
                    $_SESSION['prenom'] = $prenom;
                    $_SESSION['email'] = $email;
                    $_SESSION['connected'] = true;

                    echo '<script>
                    window.location.href = "./chat.php";
                    alert("Inscription réussie !");
                        </script>';
                }else {
                    echo '<script>
                    window.location.href = "./inscription.php";
                    alert("Inscription échouée !");
                        </script>';
                }
                $req->close();
            }

    $verif->close();
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>
    <div class="form-container inscription">
        <h2>Inscription</h2>

        <form method="POST" action="inscription.php">
            <input type="text" name="nom" placeholder="Nom" required>
            <input type="text" name="prenom" placeholder="Prénom" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="mdp" placeholder="Mot de passe" required>
            <input type="password" name="confirm" placeholder="Confirmation du mot de passe" required>

            <button type="submit"  name="inscription">S'inscrire</button>
        </form>

        <p>Déjà un compte ? <a href="index.php">Se connecter</a></p>
    </div>
</body>
</html>
