<?php
class Utilisateur {
    private $pdo;
    public function __construct() {
        
    }
    private function chargerPDO() {
        try {
            $this->pdo = getPDO();
        } catch (Exception) {
            die("ouille, coup dur pour guillaume.");
        }
    }
    public static function connexion($email, $password) {
        $prepare=DBA::db()->prepare("SELECT * FROM `user` WHERE `login` = :login");
        $prepare->execute(array(
            ":login"=>$email
         ));
        $res = $prepare->fetch();
        // 0 = ok 1 = mdp/user erroné 2 = il existe pas fréro.
        if(!(count($res) == 0)) {
            if(password_verify($password , $res["password"])) {
                return 0;
                //userID    userName     usrTyp
            } else {
                return 1;
            }
        } else {
            return 2;
        }
    }
    public static function utilisateurExiste($login) {
        try {
            $prepare = DBA::db()->prepare("SELECT * FROM user WHERE `login`=:login");
            $prepare->bindParam(':login', $login , PDO::PARAM_INT);
            $prepare->execute();
            $res = $prepare->fetchAll();
        return (count($res) != 0);
        } catch (Exception $e) {
            echo $e;
        }
        
    }
    //Retourne en INT l'id de l'utilisateur selon son identifiant
    public static function getUserIdByEmail($login) {
        $pdo = DBA::db();
        $prepare = $pdo->prepare("SELECT * FROM user WHERE `login`=:leLogin");
        $prepare->execute(array(
            ":leLogin"=>$login
        ));
        $res = $prepare->fetch();
        return $res['id'];

    }
    //Résilie tout les tokens lié au compte (considération du fait qu'on ne connecte qu'une seule application.)
    public static function deconnexion($token) {
        $pdo = DBA::db();
        $userID = Token::getUserID($token);
        Token::destroyTokenFromUser($userID);
    }
    public static function isHost($userID) {
        $prepare = DBA::db()->prepare("SELECT type FROM user WHERE id=:id");
        $prepare->execute(array(
            ":id"=>$userID
        ));
        return ($prepare->fetch(PDO::FETCH_ASSOC)['type']=="Hote");
    }
    public static function getNom($userID) {
        $prepare = DBA::db()->prepare("SELECT nom FROM user WHERE id=:id");
        $prepare->execute(array(
            ":id"=>$userID
        ));
        return $prepare->fetch(PDO::FETCH_ASSOC)['nom'];
    }
    public static function getPrenom($userID) {
        $prepare = DBA::db()->prepare("SELECT prenom FROM user WHERE id=:id");
        $prepare->execute(array(
            ":id"=>$userID
        ));
        return $prepare->fetch(PDO::FETCH_ASSOC)['prenom'];
    }
    public static function getAllInformations($userID) {
        $prepare = DBA::db()->prepare("SELECT * FROM user WHERE id=:id");
        $prepare->execute(array(
            ":id"=>$userID
        ));
        //Erreur::registerError($prepare);
        return $prepare->fetch(PDO::FETCH_ASSOC);
    }
    public static function editAccount($userID, $login, $password, $nom, $prenom) {
        try {
            $prepare = DBA::db()->prepare("UPDATE user SET login=:login, password=:password, nom=:nom, prenom=:prenom WHERE id=:id");
            $executed = $prepare->execute(array(
                ":login"=>$login,
                ":password"=>password_hash($password, PASSWORD_BCRYPT),
                ":nom"=>$nom,
                ":prenom"=>$prenom,
                ":id"=>$userID
            ));
            Erreur::registerError($prepare);
            return $executed;
        } catch (Exception $e) {
            Erreur::registerError(array("Code"=>500,"Message"=>$e->getMessage()));
            http_response_code(500);
            die();
        }
        
    }
}