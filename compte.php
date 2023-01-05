<?php

require_once ('config.php');
require_once ('oauthtwitch.php');

//on récup le cookie de connexion
$connect = $_COOKIE['connected'];


//Si l'utilisateur est connecté
if($connect == '1'){
    //On récupére le cookie de l'id utilisateur
    $user = $_COOKIE['user'];

    //On récupére les informations de l'user
    $requete_info_user = $bdd->query('SELECT * FROM utilisateur WHERE id = "' . $user . '"');
    $resultat_info_user = $requete_info_user->fetch();

    $pseudo = $resultat_info_user['pseudo'];

    echo 'Bonjour ' . $pseudo . ' votre compte est bien créer en base de données.';

}
//Sinon on redirige vers la connexion
else{
    header('Location:index.php');
    exit;
}

?>
