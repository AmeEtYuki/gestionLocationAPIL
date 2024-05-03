<?php

class Photo {

    
    public static function addPhotosToPieceEntree($idPiece, $chemin, $idReservation){
        try{
            $prepare = DBA::DB()->prepare('SELECT id FROM etat_lieux_entree WHERE id_reservation = :reservationID');
            $prepare->execute(array(
                ':reservationID' => $idReservation
            ));
            $etatLieuxID = $prepare->fetchColumn();
    
            if($etatLieuxID) {
                $prepare = DBA::DB()->prepare('INSERT INTO photo (`chemin`, `id_piece`, `id_etat_lieux_entree`) VALUES (:chemin, :idPiece, :idEtatLieux) ');
                $prepare->execute(array(
                    ':chemin' => $chemin,
                    ':idPiece' => $idPiece,
                    ':idEtatLieux' => $etatLieuxID 
                ));
                return true;
            } else {
                return false;
            }
        } catch (Exception $e){
            return false; 
        }
    }
    
    
    public static function addPhotosToPieceSortie($pieceID, $chemin, $etatLieuxID){
        try{
            $prepare = DBA::DB()->prepare('INSERT INTO photo (`chemin`, `id_piece`, `id_etat_lieux_sortie`) VALUES (:chemin, :idPiece, :idEtatLieux) ');
            $prepare->execute(array(
                ':chemin' => $chemin,
                ':idPiece' => $pieceID,
                ':idEtatLieux' => $etatLieuxID
            ));
            return true;
        } catch (Exception $e){
            return null; 
        }
    }
    
    //use
    


}

?>