<?php

class Token {


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
            $sql = "DELETE FROM authtokens WHERE Uid = :uid";
            $req = $pdo->prepare($sql);
            $req->bindParam(':uid', $userId , PDO::PARAM_INT);
            $req->execute();
        }catch(Exception $e){
            //oopsie
        }
    }


    public static function createTokenForUser($userId){
        $token = "";
        try{
            //We delete already existing tokens, just in case
            Token::destroyTokenFromUser($userId);
            
            //then create
            $token = openssl_random_pseudo_bytes(16, true);

            //then associate token to user
            $pdo = getPDO();
            $sql = "INSERT INTO `authtokens`(`Uid`, `Token`, `ConDate`, `LastSeen`, `ClientType`) VALUES ( :uid , :token , NOW() , NOW() ,'API Mobile')";
            $req = $pdo->prepare($sql);
            $req->bindParam(':uid', $userId , PDO::PARAM_INT);
            $req->bindParam(':token', $token , PDO::PARAM_STR);
            $req->execute();
        }catch(Exception $e){
            //oopsie
            $token = "";
        }
        return $token;
    }


}

?>
