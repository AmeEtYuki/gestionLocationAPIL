<?php

class Token {

    public static function createToken(){
        return openssl_random_pseudo_bytes(16, true);
    }

    public static function verifyToken($token,$userid){
        $valid = false;
        try{
            $pdo = getPDO();
            $sql = "SELECT * FROM authtokens WHERE Token = :tkn";
            $req = $pdo->prepare($sql);
            $req->bindParam(':tkn', $token , PDO::PARAM_STR);
            $req->execute();
            $res = $req->fetch(PDO::FETCH_ASSOC); 
            $valid = ($res["Uid"] == $userid); //check ownership
            if($valid){
                //check time validity 
                // now - LastSeen = number of seconds since lastSeen
                //If this number is greater than a month (in seconds), we disconnect the user
                if( (time()-$res["LastSeen"])>(60*60*24*30)){
                    $valid = false;
                    //DESTROY TOKEN
                    Token::destroyToken($token);
                } else {
                    $valid = true;
                }
            }
            
        }catch(Exception $e){
            $valid = false;
        }
        return $valid;
    }


    public static function destroyToken($token){
        try{
            $pdo = getPDO();
            $sql = "DELETE FROM authtokens WHERE Token = :tkn";
            $req = $pdo->prepare($sql);
            $req->bindParam(':tkn', $token , PDO::PARAM_STR);
            $req->execute();
        }catch(Exception $e){
            //oopsie
        }
    }

    public static function destroyTokenFromUser($userId){
        try{
            $pdo = getPDO();
            $sql = "DELETE FROM authtokens WHERE usrId = :usrId";
            $req = $pdo->prepare($sql);
            $req->bindParam(':usrId', $userId , PDO::PARAM_INT);
            $req->execute();
        }catch(Exception $e){
            //oopsie
        }
    }
    public static function userHasToken($userId){
        $has = false;
        try{
            $pdo = getPDO();
            $sql = "SELECT * FROM authtokens WHERE usrId = :usrId";
            $req = $pdo->prepare($sql);
            $req->bindParam(':usrId', $userId , PDO::PARAM_INT);
            $req->execute();
            $has = (count($req->fetchAll(PDO::FETCH_ASSOC)) == 1);
        }catch(Exception $e){
            $has = false;
        }
    }
    //retourne vide si le token n'existe pas en base, ou renvoie le token si présent dans la base de donnée après activation de la fonction.
    public static function createTokenForUser($userId){
        
        //S'il en a déjà un, je le détruit
        Token::destroyTokenFromUser($userId);
        /*if(Token::userHasToken($userId)){          
            //delete
        }*/
        //Création d'un token pour un utilisateur donné.
        $token = '';
        $nonExistantToken = false;
        while (!$nonExistantToken) {
            $token = Token::generateToken();
            $nonExistantToken = Token::verifyToken($token, $userId);
        }
        //Token généré et non existant, insertion du token
        $pdo = getPDO();
        $req = $pdo->prepare("INSERT INTO `AuthTokens` (`usrId`, `Tkn`, `ConDa`, `LastSeen`, `ClientType`) VALUES 
        (:id, :token, NOW(), NOW(), 'Mobile')");
        $req->execute(array(
            ':id'=>$userId,
            ':token'=>$token
        ));
        return (Token::verifyToken($token,$userId))?$token:'';
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
        $pdo = getPDO();
        $req = $pdo->prepare("SELECT * FROM AuthTokens WHERE Tkn = :token;");
        $req->execute(
            array(
                ":token"=>$token
            )
        );
        return $req->fetch()['usrId'];
    }

}

?>
