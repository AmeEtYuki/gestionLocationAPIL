<?php
class Utilisateur {
    private $pdo;
    public function __construct() {
        
    }
    private static function chargerPDO() {
        try {
            $this->$pdo = getPDO();
        } catch (Exception) {
            die("ouille, coup dur pour guillaume.");
        }
    }
    public function connexion($email, $password) {
        chargerPDO();
        $prepare=$this->pdo->prepare("SELECT * FROM `user` WHERE `login` = :l");
        $prepare->execute(array(
            ":l"=>$email
         ));
        /*$prepare->bindParam(':l', $email , PDO::PARAM_INT);
        $prepare->execute();*/
        $res = $prepare->fetch();
        // 0 = ok 1 = mdp/user erroné 2 = il existe pas fréro.
        if(!(count($res) == 0)) {
            if(password_verify($password , $res["password"])) {
                $_SESSION['userID'] = $res['id'];
                $_SESSION['userName'] = $res['login'];
                $_SESSION['usrType'] = $res['type'];
                return 0;
                //userID    userName     usrTyp
                
            } else {
                return 1;
            }
        } else {
            return 2;
        }
    }
    public static function utilisateurExiste($email) {
        chargerPDO();
        $prepare = $this->pdo->prepare("SELECT COUNT(*) FROM user WHERE `login`=:email");
        $prepare->bindParam(':email', $email , PDO::PARAM_INT);
        $prepare->execute();
        $res = $prepare->fetch();
        return (count($res) == 0);
    }
}