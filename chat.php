<?php
session_start();
include('service/bd.php');

if (!isset($_SESSION['connected'])) {
    header('location: index.php');
    exit;
}
$id = $_SESSION['id'];
$ip = $_SERVER['REMOTE_ADDR'];

$destinataire = $_GET['destinataire'] ?? null;

if(isset($_POST['se_deconnecter'])){

    if(isset($_SESSION['connected'])){
        unset($_SESSION['connected']);
        unset($_SESSION['nom']);
        unset($_SESSION['email']);
        header('location: index.php');
        exit;
    }
}

if (isset($_POST['envoyer']) || isset($_FILES['fichier'])) {
    $message = $_POST['message'] ?? null;
    $chemin_fichier = null;

    // fichier
    if (!empty($_FILES['fichier']['name'])) {
        $dossier = "./chargement/";
        $nom_fichier = time() . "_" . basename($_FILES["fichier"]["name"]);
        $chemin_fichier = $dossier . $nom_fichier;
        move_uploaded_file($_FILES["fichier"]["tmp_name"], $chemin_fichier);
    }

    $stmt = $connexion->prepare("
    INSERT INTO messages(destinateur_id, destinataire_id, message, fichier, destinateur_ip)
        VALUES(?,?,?,?,?)
    ");
    $stmt->bind_param("iisss", $id, $destinataire, $message, $chemin_fichier, $ip);
    $stmt->execute();

    header("location: chat.php" . ($destinataire ? "?destinataire=$destinataire" : ""));
    exit;
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

<div class="chat-container">

    <!-- COLONNE GAUCHE : LISTE DES MEMBRES -->
    <div class="sidebar">
        <div class="user-info">
            <?php if($_SESSION['email'] == "admin@admin.com"){ ?>
                <a href="admin.php" class="member-link">Voir l'historique</a>
            <?php } else { ?>
                <p>Bonjour, <?php echo $_SESSION['prenom']; ?></p>
            <?php } ?>
        </div>  
        <h3>Chats</h3>
        <a href="chat.php" class="member-link">
            <div class="member">
                Chat général
            </div>
        </a>

        <h3>Membres</h3>
        <div id="members-list">
            <?php
                $id = $_SESSION['id'];
                $req = $connexion->prepare("SELECT id, prenom FROM utilisateur WHERE id !=? ORDER BY prenom ASC");
                $req->bind_param('i',$id);
                $req->execute();
                $resultat = $req->get_result();
                while ($row = $resultat->fetch_assoc()) {
                    echo '<a href="chat.php?destinataire='.$row['id'].'" class="member-link">
                            <div class="member">'.$row['prenom'].'</div>
                        </a>';
            }?>
        </div>
        <div class="form-bouton">
            <form method="POST" action="chat.php">
                <button type="submit" name="se_deconnecter">Déconnexion</button>
            </form>
        </div>
    </div>

    <!-- messages -->
    <div class="chat-area">
        <div id="messages">
            <?php
                $destinataire = $_GET['destinataire'] ?? null;
                if ($destinataire) {
                    $stmt = $connexion->prepare("
                        SELECT m.*, u.prenom 
                        FROM messages m JOIN utilisateur u 
                        ON m.destinateur_id = u.id
                        WHERE (m.destinateur_id = ? AND m.destinataire_id = ?)
                            OR (m.destinateur_id = ? AND m.destinataire_id = ?)
                        ORDER BY m.date_envoi ASC
                    ");
                    $stmt->bind_param("iiii", $id, $destinataire, $destinataire, $id);
                    $stmt->execute();
                    $msgs = $stmt->get_result();
                } else {
                    // Chat général
                    $msgs = $connexion->query("
                    SELECT m.*, u.prenom 
                    FROM messages m
                    JOIN utilisateur u ON m.destinateur_id = u.id
                    WHERE m.destinataire_id IS NULL
                    ORDER BY m.date_envoi ASC
                    ");
                }

                while ($m = $msgs->fetch_assoc()) {
                    echo '<div class="message">';
                        echo '<div class="contenu">';
                            echo '<strong>'.$m['prenom'].' :</strong><br>';

                            if (!empty($m['message'])) {
                                echo nl2br(htmlspecialchars($m['message']));
                            }

                            if (!empty($m['fichier'])) {
                                echo "<br><a href='".$m['fichier']."' target='_blank'>Fichier joint</a>";
                            }
                        echo '</div>';

                        echo '<div class="date">';
                            echo '<p>'.$m['date_envoi'].'</p>';
                        echo '</div>';
                    echo '</div>';
                }
            ?>
        </div>

        <form id="send-form" method="POST" action="chat.php<?php echo $destinataire ? '?destinataire='.$destinataire : ''; ?>" enctype="multipart/form-data">
            <input type="text" name="message" id="message" placeholder="Écrire un message..." required>
            <input type="file" name="fichier">
            <button type="submit" name="envoyer">Envoyer</button>
        </form>
    </div>

</div>

</body>
</html>
