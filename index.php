<?php
session_start();
include('service/bd.php');

if(isset($_SESSION['connected'])){
        header('location: chat.php');
        exit;
}

if(isset($_POST['se_connecter'])){

    $email = $_POST['email'];
    $mdp = md5($_POST['mdp']);

    $req = $connexion->prepare("SELECT * FROM utilisateur WHERE email=? and mdp=?");
    $req->bind_param('ss',$email,$mdp);
    
    if($req->execute()){
        
        $req->bind_result($id,$nom,$prenom,$email,$mdp);
        $req->store_result();

        if($req->num_rows() == 1){
            
            $req->fetch();

            $_SESSION['id'] = $id;
            $_SESSION['nom'] = $nom;
            $_SESSION['prenom'] = $prenom;
            $_SESSION['email'] = $email;
            $_SESSION['connected'] = true;

            echo '<script>
            window.location.href = "chat.php";
            </script>';
        }else {
            echo '<script>
            alert("Compte inexistant");
             </script>';
        }

    }else {
        echo '<script>
            window.location.href = "index.php";
            alert("Erreur de connexion");
            </script>';
    }


}

//dans le chat
if(isset($_POST['se_deconnecter'])){

    if(isset($_SESSION['connected'])){
        unset($_SESSION['connected']);
        unset($_SESSION['nom']);
        unset($_SESSION['email']);
        header('location: index.php');
        exit;
    }


}



?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Loqui</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>
    <div class="container">
        <div class="container_gauche">
            <img src="img/logo_loqui.png" alt="logo">
        </div>
        
        <div class="container_droite">
            <div class="form-container">
                <h2>Connexion</h2>
                <form method="POST" action="index.php">
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="mdp" placeholder="Mot de passe" required>
                    <button type="submit" name="se_connecter">Se connecter</button>
                </form>
                <p>Pas de compte ? <a href="inscription.php">S'inscrire</a></p>
            </div>        
        </div>

        
    </div>
</body>

</html>