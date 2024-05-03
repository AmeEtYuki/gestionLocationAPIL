<?php
class Bien {
    public static function getAllBiensFromUser($userID) {
        $prepare = DBA::db()->prepare('SELECT * FROM bien WHERE id_user = :user');
        $prepare->execute(array(
            ":user"=>$userID
        ));
        return $prepare->fetchAll(PDO::FETCH_ASSOC);
    }

    
}