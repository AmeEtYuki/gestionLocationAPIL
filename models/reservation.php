<?php
class Reservation {
    /*public static function getReservationsBien($bien) {
        $prepare = DBA::db()->prepare("SELECT * FROM periodeReserve WHERE ")
    }*/
    public static function getLesEntrerReservation($user) {
        //calcule des dates 
        $result = NULL;
        $requestProprio = "SELECT * FROM `periodeReserve` INNER JOIN periodeDispo ON periodeDispo.id = periodeReserve.id_periodeDispo INNER JOIN bien ON bien.id = periodeDispo.id_bien WHERE DATEDIFF(day, NOW(), periodeReserve.id_periodeDispo) AND id_user = :idUtilisateur";
        $requestUser = "SELECT * FROM `periodeReserve` WHERE DATEDIFF(day, NOW(), periodeReserve.id_periodeDispo) AND id_locataire = :idUtilisateur";
        //si l'utilisateur est hôte
        $requête = (Utilisateur::isHost($user))?$requestProprio:$requestUser;
        try {
            $prepare = DBA::db()->prepare("");
            $prepare->execute(array(
                ":idLocataire"=>$user
            ));
            $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            http_response_code(500);
        }
        return $result;
    }
}