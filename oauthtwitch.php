<?php 
    define('API_LINK', 'https://api.twitch.tv/helix');
    
    class OAuthTwitch{
        private $_client_id;
        private $_client_secret;
        private $_redirect_uri;
        private $_scope;
        private $_token;
        private $_headers = [];

        public function __construct($client_id, $client_secret, $redirect_uri, $scope){
            $this->_client_id = $client_id;
            $this->_client_secret = $client_secret;
            $this->_redirect_uri = $redirect_uri;
            $this->_scope = $scope;
        }
        
        
        
        /*
        Fonction : Récupére le lien de connexion
        Retour : Lien de connexion à mettre dans le bouton (par exemple)
        Plus d'informations : https://dev.twitch.tv/docs/authentication/getting-tokens-oauth/#authorization-code-grant-flow
        */
        public function get_link_connect(){
            $link = "https://id.twitch.tv/oauth2/authorize?client_id=".$this->_client_id."&redirect_uri=".$this->_redirect_uri."&response_type=code&scope=".$this->_scope."&force_verify=true";
            return $link;
        }
        
        
        
        /*
        Fonction : Récupére le token
        Paramètres : 
        $code (string) -> $_GET['code'] présent dans l'URL s'être connecté
        Plus d'informations : https://dev.twitch.tv/docs/authentication/getting-tokens-oauth#use-the-authorization-code-to-get-a-token
        */
        public function get_token($code){
            // Lien pour avoir le token
            $link = "https://id.twitch.tv/oauth2/token?client_id=".$this->_client_id."&client_secret=".$this->_client_secret."&code=".$code."&grant_type=authorization_code&redirect_uri=".$this->_redirect_uri;
            // Request cURL POST pour get le token
            $ch = curl_init($link);

            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $res = curl_exec($ch);
            curl_close($ch);

            // Decode
            $token = json_decode($res);
            // On place le token en attribut privée 
            $this->_token = $token;

            // On return le token
            return array($token->access_token,$token->expires_in,$token->refresh_token);
        }

        /*
        Fonction : Récupére le token d'APP
        Paramètres : 
        Plus d'informations : https://dev.twitch.tv/docs/authentication/getting-tokens-oauth/#client-credentials-grant-flow
        */
        public function get_token_app(){
            // Lien pour avoir le token
            $link = "https://id.twitch.tv/oauth2/token?client_id=".$this->_client_id."&client_secret=".$this->_client_secret."&grant_type=client_credentials";
            // Request cURL POST pour get le token
            $ch = curl_init($link);

            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $res = curl_exec($ch);
            curl_close($ch);

            // Decode
            $token = json_decode($res);
            // On place le token en attribut privée 
            $this->_token = $token;

            // On return le token
            return $token->access_token;
        }

        /*
        Fonction : Refresh le token
        Paramètres : 
        $code (string) -> $_GET['code'] présent dans l'URL s'être connecté
        Plus d'informations : https://dev.twitch.tv/docs/authentication/refresh-tokens
        */
        public function refresh_token($code){
            // Lien pour avoir le token
            $link = "https://id.twitch.tv/oauth2/token?client_id=".$this->_client_id."&client_secret=".$this->_client_secret."&grant_type=refresh_token&refresh_token=".$code;
            // Request cURL POST pour get le token
            $ch = curl_init($link);

            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $res = curl_exec($ch);
            curl_close($ch);

            // Decode
            $token = json_decode($res);
            // On place le token en attribut privée 
            $this->_token = $token;

            // On return le token
            return array($token->access_token,$token->refresh_token);
        }
        
        /*
        Fonction : Transmet les headers
        Paramètres : 
        $token (string) -> Token récupéré lors de l'identification
        $type (int) -> Type de headers à transmettre
        Plus d'informations : https://dev.twitch.tv/docs/api
        */
        public function set_headers($token, $type){
        	if($type == 1){
        		$this->_headers = [
        			'Authorization: Bearer '.$token,
        			'Client-Id: '.$this->_client_id
            ];
        	}
        	else if($type == 2)
        	{
            $this->_headers = [
                'Authorization: Bearer '.$token,
                'Client-Id: '.$this->_client_id,
                'Content-Type: application/json'
            ];
        	}
        	
        	else
        	{
        		$this->_headers = [
        			'Authorization: Bearer '.$token,
        			'Client-Id: '.$this->_client_id
            ];
        	}
        }



        /*
        Fonction : Recherche une chaine avec le nom de la chaine
        Paramètres : 
        $name (string) -> Nom de la chaine
        Retourne : JSON
        Plus d'informations : https://dev.twitch.tv/docs/api/reference#search-channels
        */
        public function search_channel($name){
            $link = API_LINK."/search/channels?query=".$name;
            // cURL 
            $ch = curl_init($link);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_headers);

            $res = curl_exec($ch);
            curl_close($ch);

            // on decode et on renvoie
            return json_decode($res);
        }



        /*
        Fonction : Recherche une chaine avec l'ID de la chaine
        Paramètres : 
        $broadcaster_id (string) -> Identifiant de la chaine
        Retourne : JSON
        Plus d'informations : https://dev.twitch.tv/docs/api/reference#get-channel-information
        */
        public function get_channel_info($broadcaster_id){
            $link = API_LINK."/channels?broadcaster_id=".$broadcaster_id;
            
            $ch = curl_init($link);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_headers);

            $res = curl_exec($ch);
            curl_close($ch);

            return json_decode($res);
        }

        /*
        Fonction : Recherche les informations de la chaine dont on dispose le token
        Paramètres : 
        $jeton (string) -> Token
        Retourne : JSON
        Plus d'informations : https://dev.twitch.tv/docs/api/reference#get-users
        */
        public function get_user_info(){
            $link = API_LINK."/users";
            
            $ch = curl_init($link);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_headers);

            $res = curl_exec($ch);
            curl_close($ch);

            return json_decode($res);
        }
        
        
        
        /*
        Fonction : Créer une prédiction
        Paramètres : 
        $broadcaster_id (string) -> Identifiant de la chaine
        $titre (string) -> Titre de la prédiction
        $outcomes (array) -> Tableau avec les réponses de la prédiction
        $duree (int) -> Durée de la prédiction en secondes (min : 1, max : 1800)
        Retourne : JSON
        Plus d'informations : https://dev.twitch.tv/docs/api/reference#create-prediction
        */
        public function create_prediction($broadcaster_id, $titre, $outcomes, $duree){
        	$link = API_LINK."/predictions";
        	
        	$ch = curl_init($link);
        	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        	curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_headers);
        	/*
        	$outcomes = array(
						array("title" => "Oui"),
						array("title" => "Non"),
					);
					*/
					$json = array(
						"broadcaster_id" => $broadcaster_id,
						"title" => $titre,
						"outcomes" => $outcomes,
						"prediction_window" => $duree,
					);
					curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
					$res = curl_exec($ch);
					curl_close($ch);
					return json_decode($res);
        }
        
        
        
        /*
        Fonction : Terminier une prédiction
        Paramètres : 
        $broadcaster_id (int) -> Identifiant de la chaine
        $id (string) -> Identifiant de la prédiction
        $status (string) -> Resultat de la prédiction       
        $winning_outcome_id (string) [optionnel] -> Identifiant de la réponse gagnante de la prédiction
        Retourne : JSON
        Plus d'informations : https://dev.twitch.tv/docs/api/reference#end-prediction
        */
        public function end_prediction($broadcaster_id, $id, $status, $winning_outcome_id){
        	$link = API_LINK."/predictions";
        	
        	$ch = curl_init($link);
        	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        	curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_headers);
        	if(strtoupper($status) == "RESOLVED"){
						$json = array(
							"broadcaster_id" => $broadcaster_id,
							"id" => $id,
							"status" => strtoupper($status),
							"winning_outcome_id" => $winning_outcome_id,
						);
        	}
        	else
        	{
						$json = array(
							"broadcaster_id" => $broadcaster_id,
							"id" => $id,
							"status" => strtoupper($status),
						);
        	}
        	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
					$res = curl_exec($ch);
					curl_close($ch);
					return json_decode($res);
        }


        /*
        Fonction : Récupére les informations de la prédiction
        Paramètres : 
        $broadcaster_id (int) -> Identifiant de la chaine
        Retourne : JSON
        Plus d'informations : https://dev.twitch.tv/docs/api/reference#get-predictions
        */
        public function get_prediction($broadcaster_id){
        	$link = API_LINK."/predictions?broadcaster_id=".$broadcaster_id;
        	
            // cURL 
            $ch = curl_init($link);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_headers);

            $res = curl_exec($ch);
            curl_close($ch);

            // on decode et on renvoie
            return json_decode($res);
        }
    }
