<?php

require_once ('config.php');
require_once ('oauthtwitch.php');

//Cookie de l'utilisateur pour savoir s'il est co ou non
$connected = $_COOKIE['connected'];

//Si connecté, on redirige vers la page de notre choix
if($connected == '1'){
    header('Location:/');
    exit;
}

//Sinon on créer/co le compte
else{
    //Code transmit par Twitch dans l'url
    $code = htmlspecialchars($_GET['code']);

    //On vérifie si il y a bien un code
    if($code){
        //On récupére les informations du compte après on stock dans nos variables
        $tok = $oauth->get_token($code);
        $token = $tok[0];
        $expiration = $tok[1];
        $refresh_token = $tok[2];

        //On envoie les headers avec le token
        $oauth->set_headers($token, 1);

        //On récupére les informations lié à la chaine Twitch dont on a obtenue précédent le token
        $info = $oauth->get_user_info();

        //On déchiffre les informations renvoyé par Twitch
        $decoded_info_json = json_decode(json_encode($info), true);
        $data = $decoded_info_json['data'];

        //On stocke dans des variables les données de notre choix pour pouvoir les utilisés
        foreach ($data as $donnee) {
            $id = $donnee['id'];
            $login = $donnee['login'];
            $display_name = $donnee['display_name'];
        }

        //On regarde en BDD si un utilisateur a l'id de chaine sur son compte
        $requete_verif_utilisateur = $bdd->query('SELECT * FROM utilisateur WHERE broadcaster_id = "' . $id . '"');
        $resultat_verif_utilisateur = $requete_verif_utilisateur->fetch();

        //Si aucun résultat, on doit créer le compte et le connecté
        if (!$resultat_verif_utilisateur) {
            //On créer le compte
            $requete = 'INSERT INTO utilisateur (pseudo, broadcaster_id, token, token_refresh, expiration) VALUES ("'.$display_name.'","'.$id.'","'.$token.'","'.$refresh_token.'","'.$expiration.'")';
            $new_user = $bdd->prepare($requete);
            $new_user->execute();


            //On récupére les informations du compte
            $requete_info_user = $bdd->query('SELECT * FROM utilisateur WHERE broadcaster_id = "' . $id . '"');
            $resultat_info_user = $requete_info_user->fetch();

            //On créer deux cookies : Un pour l'identifiant, l'autre pour dire qu'il est connecté (possible d'en faire qu'un avec l'identifiant)
            $cook_id = $resultat_info_user['id'];
            setcookie('connected', '1', time() + 2592000, "/");
            setcookie('user', $cook_id, time() + 2592000, "/");

            //On redirige vers la page de notre choix
            header('Location:compte.php');
            exit;
        }

        //Si un résultat, on connecte simplement
        else{
            //On créer les cookies
            $cook_id = $resultat_verif_utilisateur['id'];
            setcookie('connected', '1', time() + 2592000, "/");
            setcookie('user', $cook_id, time() + 2592000, "/");

            //On redirige vers la page de notre choix
            header('Location:compte.php');
            exit;
        }
    }

    //Si il y en a pas on redirige vers la page de notre choix
    else{
        header('Location:index.php');
        exit;
    }
}

?>
