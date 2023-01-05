<?php
//Informations API Twitch
require_once 'oauthtwitch.php';
$oauth = new OAuthTwitch('', '', '', '');

//Connexion BDD
session_start();
$BDD_hote = '';
$BDD_bd = '';
$BDD_utilisateur = '';
$BDD_mot_passe = '';

try{
    
 $bdd = new PDO('mysql:host='.$BDD_hote.';dbname='.$BDD_bd, $BDD_utilisateur, $BDD_mot_passe);
 $bdd->exec("SET CHARACTER SET utf8mb4");
 $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

catch(PDOException $e){
 echo 'Erreur : '.$e->getMessage();
 echo 'N° : '.$e->getCode();
}
ini_set("dislay_errors",1);
error_reporting(0);
?>
