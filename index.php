<?php

require_once ('config.php');
require_once ('oauthtwitch.php');

$lien = $oauth->get_link_connect();

echo '<a href="'.$lien.'">Connexion Twitch</a>';

?>
