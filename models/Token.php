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
            $req = $pdo->prepare($sql);
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
            $req = $pdo->prepare($sql);
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
            $req = $pdo->prepare($sql);
            $req->bindParam(':uid', $userId , PDO::PARAM_INT);
            $req->execute();
            $has = (count($req->fetchAll(PDO::FETCH_ASSOC)) == 1);
            
        }catch(Exception $e){
            //oopsie
        }
    }
    //retourne vide si le token n'existe pas en base, ou renvoie le token si présent dans la base de donnée après activation de la fonction.
    public static function createTokenForUser($userId){
        
        //il faut check s'il en a deja un
        if(Token::userHasToken($userId)){
            //delete
        }
        //Création d'un token pour un utilisateur donné.
        $token = '';
        $nonExistantToken = false;
        while (!$nonExistantToken) {
            $token = Token::generateToken();
            $nonExistantToken = Token::checkTokenUser($token, $userId);
        }
        //Token généré et non existant, insertion du token
        $pdo = getPDO();
        $req = $pdo->prepare("INSERT INTO `AuthTokens` (`usrId`, `Tkn`, `ConDa`, `LastSeen`, `ClientType`) VALUES 
        (:id, :token, NOW(), NOW(), 'Mobile')");
        $req->execute(array(
            ':id'=>$userId,
            ':token'=>$token
        ));
        return (Token::checkTokenUser($token,$userId))?$token:'';
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