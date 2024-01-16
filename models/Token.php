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
            $req = $this->pdo->prepare($sql);
            $req->bindParam(':tkn', $token , PDO::PARAM_STR);
            $req->execute();
            $res = $req->fetch(PDO::FETCH_ASSOC); 
            $valid = ($res["Uid"] == $userid); //check ownership
            if($valid){
                //check time validity 
                // now-LastSeen = number of seconds since lastSeen
                //If this number is greater than a month (in seconds), we disconnect the user
                if( (time()-$res["LastSeen"])>(60*60*24*30)){
                    $valid = false;
                    //DESTROY TOKEN
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
            $req = $this->pdo->prepare($sql);
            $req->bindParam(':tkn', $token , PDO::PARAM_STR);
            $req->execute();
        }catch(Exception $e){
            //oopsie
        }
    }


    public static function createTokenForUser($userId){
        //il faut check s'il en a deja un
        if($this->userHasToken($userId)){
            //delete
        }
        //then create

    }


}

?>