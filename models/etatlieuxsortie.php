<?php

class EtatLieuxSortie{
    public static function getForHost($userId) {
        $prepare = DBA::db()->prepare("
            SELECT periodeReserve.*, bien.rue as rue, bien.cp as cp, bien.ville as ville, bien.id as idBien FROM periodeReserve 
            LEFT JOIN etat_lieux_sortie ON periodeReserve.id = etat_lieux_sortie.id_reservation
            INNER JOIN periodeDispo ON periodeDispo.id = periodeReserve.id_periodeDispo 
            INNER JOIN bien ON bien.id = periodeDispo.id_bien 
            WHERE bien.id_user = :idUserLocataire AND etat_lieux_sortie.id_reservation IS NULL 
        ");
        // LEFT JOIN etat_lieux_sortie ON periodeReserve.id = etat_lieux_sortie.id_reservation
        // merde : AND etat_lieux_entree.id_reservation IS NOT NULL;
        $prepare->execute(array(
            ":idUserLocataire"=>$userId
        ));	
        return $prepare->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function getForRenter($userId) {
        try {
            $prepare = DBA::db()->prepare("
            SELECT periodeReserve.*, bien.rue as rue, bien.cp as cp, bien.ville as ville, bien.id as idBien
            FROM periodeReserve 
            LEFT JOIN bien ON periodeReserve.id_periodeDispo = bien.id 
            LEFT JOIN etat_lieux_sortie ON periodeReserve.id = etat_lieux_sortie.id_reservation 
            WHERE periodeReserve.id_locataire = :idUserLocataire AND etat_lieux_sortie.id_reservation IS NULL; 
            ");
            $prepare->execute(array(
                ':idUserLocataire'=>$userId
            ));
            return $prepare->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            Erreur::registerError($e);
            http_response_code(500);
        }
        
    }
    public static function createEtatLieuxEquipement($idReservation, $idPiece, $idEquipement ,$note) {
        try {
            $prepare = DBA::db()->prepare("INSERT INTO etat_lieux_sortie (id_reservation, id_piece, id_equipement, date_etat, note) 
            VALUES (:idReservation, :idPiece, :idEquipement ,CURRENT_TIMESTAMP, :note)");
            $prepare->execute(array(
                ":idReservation" => $idReservation,
                ":idPiece" => $idPiece,
                ":idEquipement" => $idEquipement,
                ":note" => $note));
            } catch (Exception $e) {
                Erreur::registerError($e);
                http_response_code(500);
            }
    }
    public static function createEtatLieuxPiece($periodeID, $pieceID, $commentaire) {
        try{
            $request = DBA::db()->prepare('INSERT INTO etat_lieux_sortie (id_reservation, id_piece, commentaire, date_etat) VALUES (:periodeID, :pieceID, :commentaire,CURRENT_TIMESTAMP)');
            $request->execute(array(
                ":periodeID" => $periodeID,
                ":pieceID" => $pieceID,
                ":commentaire" => $commentaire,
            ));
            return true;
        }catch(Exception $e){
            return false;
        }
    }
    public static function insertEDLGlobalDuLogement($idReservation, $commentaire) {
        try{
            $request = DBA::db()->prepare('INSERT INTO etat_lieux_sortie (id_reservation, commentaire, date_etat) VALUES (:idReservation, :commentaire,CURRENT_TIMESTAMP)');
            $request->execute(array(
                ":idReservation" => $idReservation,
                ":commentaire" => $commentaire,
            ));
            return true;
        } catch(Exception $e) {
            return false;
        }
    }
    public static function afficheMarqueurEDLPieceRealisee($idReservation, $idPiece){
        $prepare = DBA::db()->prepare('SELECT COUNT(*) AS count FROM etat_lieux_sortie WHERE id_reservation = :idReservation AND id_piece = :id_piece');
        $prepare->execute(array(
            ":idReservation" => $idReservation,
            ":id_piece" => $idPiece
        ));
        $result = $prepare->fetch(PDO::FETCH_ASSOC); 
        return $result['count'] > 0 ? $idPiece : 0; 
    }

}
?>