<?php
class Controller {
    public static function connection($json) {
        $data = json_decode($json);
        $login = $data->login;
        $password = $data->password;
        
        if(Utilisateur::utilisateurExiste($login)) {
            //vérification des informations de connexion
            $connexion = Utilisateur::connexion($login, $password);
            if($connexion == 0) {
                //Je considère que l'utilisateur n'as qu'un seul téléphone, je détruirais donc tout autre token créé pour son compte.
                $idUtilisateur = Utilisateur::getUserIdByEmail($login);
                Token::destroyTokenFromUser($idUtilisateur);
                $token = Token::createTokenForUser($idUtilisateur);
                echo json_encode(array(
                    "token"=>$token
                ));
                http_response_code(200);
            }
        }
    }
    public static function deconnexion($json) {
        $data = json_decode($json);
        $token = $data->token;
        if(Token::verifyToken($token)) {
             Token::destroyToken($token);
        }
    }
    public static function checkupToken($json) {
        //vérifie si le Token en cours d'utilisation est toujours valide, si un JSON est envoyé.
        $data = json_decode($json);
        return (isset($data->token))?Token::verifyToken($data->token):false;
    }
    public static function bien($json) {
        $data = json_decode($json);
        $userID = Token::getUserID($data->token);
        $method = $_SERVER['method'] ?? "";
        switch ($method) {
            case 'GET':
                echo json_encode(Bien::getAllBiensFromUser($userID));
            default:

        }
    }
    public static function piece($json) {
        $data = json_decode($json);
        $userID = Token::getUserID($data->token);
        $method = $_SERVER['method'] ?? "";
        /*
            GET : Obtiens toute les pièces d'un bien spécifique
            
        */
        switch ($method) {
            case 'GET':
                echo json_encode(Piece::getAllPiecesFromBien($data->idBien));
        }
    }
    public static function reservation($json) {
    }
}
?>