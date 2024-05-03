<?php

class Token {

    public static function createToken(){
        return openssl_random_pseudo_bytes(16, true);
    }

    public static function verifyToken($token){
        $valid = false;
        try{
            $sql = "SELECT * FROM AuthTokens WHERE Tkn = :tkn";
            $req = DBA::db()->prepare($sql);
            $req->bindParam(':tkn', $token , PDO::PARAM_STR);
            $req->execute();
            $res = $req->fetch(PDO::FETCH_ASSOC); 
            //$valid = ($res["Uid"] == $userid); //check ownership
        
                //check time validity 
                // now - LastSeen = number of seconds since lastSeen
                //If this number is greater than a month (in seconds), we disconnect the user
                $timestam = strtotime($res['LastSeen']);
                if( (time()-$timestam)>(60*60*24*30) ){
                    $valid = false;
                    //DESTROY TOKEN
                    //Token::destroyToken($token);
                } else {
                    $valid = true;
                }
            
            
        }catch(Exception $e){
            $valid = false;
        }
        return $valid;
    }


    public static function destroyToken($token){
        try{
            $sql = "DELETE FROM AuthTokens WHERE Tkn = :tkn";
            $req = DBA::db()->prepare($sql);
            $req->bindParam(':tkn', $token , PDO::PARAM_STR);
            $req->execute();
        }catch(Exception $e){
            //oopsie
        }
    }

    public static function destroyTokenFromUser($userId){
        try{
            $sql = "DELETE FROM AuthTokens WHERE usrId = :usrId";
            $req = DBA::db()->prepare($sql);
            $req->bindParam(':usrId', $userId , PDO::PARAM_INT);
            $req->execute();
        }catch(Exception $e){
            http_response_code(500);
        }
    }
    public static function userHasToken($userId){
        $has = false;
        try{
            $sql = "SELECT * FROM AuthTokens WHERE usrId = :usrId";
            $req = DBA::db()->prepare($sql);
            $req->bindParam(':usrId', $userId , PDO::PARAM_INT);
            $req->execute();
            $has = (count($req->fetchAll(PDO::FETCH_ASSOC)) == 1);
        }catch(Exception $e){
            $has = false;
        }
        return $has;
    }
    //retourne vide si le token n'existe pas en base, ou renvoie le token si présent dans la base de donnée après activation de la fonction.
    public static function createTokenForUser($userId){
        //S'il en a déjà un, je le détruit
        //Token::destroyTokenFromUser($userId);
        /*
        if(Token::userHasToken($userId)){          
            delete
        }
        */
        //Création d'un token pour un utilisateur donné.
        $nonExistantToken = false;
        while (!$nonExistantToken) {
            $token = Token::generateToken();
            $nonExistantToken = !Token::verifyToken($token);
        }
        //Token généré et non existant, insertion du token
        try {
            $req = DBA::db()->prepare("INSERT INTO `AuthTokens` (`usrId`, `Tkn`, `ConDa`, `LastSeen`, `ClientType`) VALUES 
            (:id, :token, NOW(), NOW(), 'Mobile')");
            $req->execute(array(
                ':id'=>$userId,
                ':token'=>$token
            ));
            return $token;
        } catch (Exception $e) {
            return '';
        }
    } 
    private static function generateToken() {
        $token = '';
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($i = 0; $i < 90; $i++) {
            $token .= $characters[rand(0, strlen($characters)-1)];
        }
        return $token;
    }
    public static function getUserID($token) {
        try {
            $req = DBA::db()->prepare("SELECT * FROM AuthTokens WHERE Tkn = :token");
            $req->execute(
                array(
                    ":token"=>$token
                )
            );
            return $req->fetch()['usrId'];
        } catch (Exception $e) {
            return null;
        }
    }

}

?>
