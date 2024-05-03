<?php
class Reservation {
    /*public static function getReservationsBien($bien) {
        $prepare = DBA::db()->prepare("SELECT * FROM periodeReserve WHERE ")
    }*/

    //recupere les reservation associé a un utilisateur
    public static function getLesEntrerReservation($user) {
        //calcule des dates 
        $result = NULL;
        $requestProprio = "SELECT periodeReserve.* , bien.id as idBien FROM `periodeReserve` INNER JOIN periodeDispo ON periodeDispo.id = periodeReserve.id_periodeDispo INNER JOIN bien ON bien.id = periodeDispo.id_bien WHERE DATEDIFF(day, NOW(), periodeReserve.id_periodeDispo) AND id_user = :idUtilisateur";
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

    public static function getReservationsBien($bienId){
        $result = NULL;
        try {
            $request = DBA::db()->prepare('SELECT * FROM periodeReserve WHERE id_periodeDispo IN (SELECT id FROM periodeDispo WHERE id_bien = bien:id)');
            $request->execute(array(
                'bienId'=>$bienId
            ));
        }catch(Exception $e){
            http_response_code(500);
        }
        return $result;
    }
    public static function getBien($reservationID) {
        try {

        } catch (Exception $e) {
            Erreur::registerError($e);
        }
    }

    public static function getProprietaireReservation($proprietaireID){
        try{
            $request = DBA::db()->prepare('
            SELECT periodeReserve.*, bien.rue, bien.cp, locataire.nom AS nom_locataire, locataire.prenom AS prenom_locataire
            FROM periodeReserve
            JOIN periodeDispo ON periodeReserve.id_periodeDispo = periodeDispo.id
            JOIN bien ON periodeDispo.id_bien = bien.id
            JOIN user AS hote ON bien.id_user = hote.id
            JOIN user AS locataire ON periodeReserve.id_locataire = locataire.id
            WHERE hote.id = :proprietaireID            
            ');
            $request->execute(array(":proprietaireID" => $proprietaireID));
            return $request->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $e){
            return false;
        }
    }
    


}