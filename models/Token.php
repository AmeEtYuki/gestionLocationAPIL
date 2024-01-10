<?php

class Token {

    public static function createToken(){
        return openssl_random_pseudo_bytes(16, true);
    }

    public static function checkTokenUser($token,$userId){
        $valid = false;
        try{
            $pdo = getPDO();
            $sql = "SELECT * FROM authtokens WHERE Token = :tkn";
            $req = $this->pdo->prepare($sql);
            $req->bindParam(':tkn', $token , PDO::PARAM_STR);
            $req->execute();
            $res = $req->fetch(PDO::FETCH_ASSOC); 
            $valid = ($res["Uid"] == $userId);
            
        }catch(Exception $e){
            $valid = false;
        }

        return $valid;
    }

    public static function userHasToken($userId){
        $has = false;
        try{
            $pdo = getPDO();
            $sql = "SELECT * FROM authtokens WHERE Uid = :uid";
            $req = $this->pdo->prepare($sql);
            $req->bindParam(':uid', $userId , PDO::PARAM_INT);
            $req->execute();
            $has = (count($req->fetchAll(PDO::FETCH_ASSOC)) == 1);
            
        }catch(Exception $e){
            $has = false;
        }

        return $has;
    }

    public static function deleteTokenFromUser($userId){
        try{
            $pdo = getPDO();
            $sql = "SELECT * FROM authtokens WHERE Uid = :uid";
            $req = $this->pdo->prepare($sql);
            $req->bindParam(':uid', $userId , PDO::PARAM_INT);
            $req->execute();
            $has = (count($req->fetchAll(PDO::FETCH_ASSOC)) == 1);
            
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