<?php
class EtatLieuxEntree{

    // creer etat lieux pour une reservation.
    public static function createEtatLieuxEntree($periodeID, $etatLieux){
        try{
            foreach($etatLieux as $pieceID => $equipements){
                foreach($equipements as $equipementsID => $commentaire){
                    self::createEtatLieuxEquipement($periodeID, $pieceID, $equipementsID ,$commentaire);
                }
                self::createEtatLieuxPiece($periodeID, $pieceID, $commentaire);
            }
            self::createEtatLieuxLogement($periodeID, $commentaire);
            return true;
        }catch(Exception $e){
            return false;
        }
    }

    public static function createEtatLieuxPiece($periodeID, $pieceID, $commentaire){
        try{
            $request = DBA::db()->prepare('INSERT INTO etat_lieux_entree (id_reservation, id_piece, commentaire) VALUES (:periodeID, :pieceID, :commentaire)');
            return $request->execute(array(
                ":periodeID" => $periodeID,
                ":pieceID" => $pieceID,
                ":commentaire" => $commentaire,
            ));
        }catch(Exception $e){
            return false;
        }
    }

    public static function createEtatLieuxLogement($periodeID, $commentaire){
        try{
            $request = DBA::db()->prepare('INSERT INTO etat_lieux_entree (id_reservation, commentaire) VALUES (:periodeID, :commentaire)');
            return $request->execute(array(
                ":periodeID" => $periodeID,
                ":commentaire" => $commentaire,
            ));;
        }catch(Exception $e){
            return false;
        }
    }

    
    public static function getReservationsToWriteEDLLocataire($user){
        $prepare = DBA::db()->prepare('
            SELECT periodeReserve.* 
            , bien.rue as rue, bien.cp as cp, bien.ville as ville, bien.id as idBien
            FROM periodeReserve 
            LEFT JOIN bien ON periodeReserve.id_periodeDispo = bien.id 
            LEFT JOIN etat_lieux_entree ON periodeReserve.id = etat_lieux_entree.id_reservation 
            WHERE periodeReserve.id_locataire = :idUserLocataire AND etat_lieux_entree.id_reservation IS NULL; 
        ');
        $prepare->execute(array(
            ":idUserLocataire"=>$user
        ));
        $result = $prepare->fetchAll(PDO::FETCH_ASSOC);
        Erreur::registerError($result);
        return $result;
    }

    //use
    public static function afficheMarqueurEDLPieceRealisee($idReservation, $idPiece){
        $prepare = DBA::db()->prepare('SELECT COUNT(*) AS count FROM etat_lieux_entree WHERE id_reservation = :idReservation AND id_piece = :id_piece');
        $prepare->execute(array(
            ":idReservation" => $idReservation,
            ":id_piece" => $idPiece
        ));
        $result = $prepare->fetch(PDO::FETCH_ASSOC); 
        return $result['count'] > 0 ? $idPiece : 0; 
    }
    

    public static function getReservationsToWriteEDLHote($user){
        $prepare = DBA::db()->prepare('
        SELECT periodeReserve.*, bien.rue as rue, bien.cp as cp, bien.ville as ville, bien.id as idBien 
        FROM periodeReserve 
        LEFT JOIN etat_lieux_entree ON etat_lieux_entree.id_reservation = periodeReserve.id 
        INNER JOIN periodeDispo ON periodeDispo.id = periodeReserve.id_periodeDispo 
        INNER JOIN bien ON bien.id = periodeDispo.id_bien 
        WHERE bien.id_user = :idUserLocataire AND etat_lieux_entree.id_reservation IS NULL; 
    ');
    $prepare->execute(array(
        ":idUserLocataire"=>$user
    ));
    $resultat = $prepare->fetchAll(PDO::FETCH_ASSOC);
    Erreur::registerError($resultat);
    return $resultat;
    }
    public static function insertEDLGlobalDuLogement($idReservation, $commentaire){
        try {
            $prepare = DBA::db()->prepare('INSERT INTO etat_lieux_entree (id_reservation, commentaire, date_etat) VALUES (:idReservation, :commentaire, CURRENT_TIMESTAMP)');
            $prepare->execute(array(
                ":idReservation" => $idReservation,
                ":commentaire" => $commentaire
            ));
            return true; 
        } catch (PDOException $e) {
            error_log('Erreur lors de l\'insertion de l\'état des lieux global du logement : ' . $e->getMessage());
            return false;
        }
    }

    public static function createEtatLieuxEquipement($idReservation, $idPiece, $idEquipement ,$note){
        try{
            $date = date("Y-m-d");
            $request = DBA::db()->prepare('INSERT INTO etat_lieux_entree (id_reservation, id_piece, id_equipement, date_etat, note) VALUES (:idReservation, :idPiece, :idEquipement ,CURRENT_TIMESTAMP, :note)');
            $request->execute(array(
                ":idReservation" => $idReservation,
                ":idPiece" => $idPiece,
                ":idEquipement" => $idEquipement,
                ":note" => $note,
            ));
            return true;
        }catch(Exception $e){
            return false;
        }
    }

    public static function createEtatLieuxPieceCommentaire($idReservation, $idPiece, $commentaire){
        try{
            $date = date("Y-m-d");
            $request = DBA::db()->prepare('INSERT INTO etat_lieux_entree (id_reservation, id_piece, commentaire, date_etat) VALUES (:idReservation, :idPiece, :commentaire,CURRENT_TIMESTAMP)');
            $request->execute(array(
                ":idReservation" => $idReservation,
                ":idPiece" => $idPiece,
                ":commentaire" => $commentaire
            ));
            return true;
        }catch(Exception $e){
            return false;
        }
    }
// get la liste des etat lieux entree user 
    public static function getEtatLieuxReservationUser($idUser){
        try{
            $request = DBA::db()->prepare('
            SELECT 
            etat_lieux_entree.id,
            etat_lieux_entree.date_etat,
            etat_lieux_entree.commentaire,
            bien.description
            FROM 
            etat_lieux_entree
            JOIN 
            periodeReserve ON etat_lieux_entree.id_reservation = periodeReserve.id
            JOIN 
            periodeDispo ON periodeReserve.id_periodeDispo = periodeDispo.id
            JOIN 
            bien ON periodeDispo.id_bien = bien.id
            JOIN 
            user ON bien.id_user = user.id
            WHERE 
            user.id = :idUser
            AND etat_lieux_entree.id_piece IS NULL
            AND etat_lieux_entree.id_equipement IS NULL
            AND etat_lieux_entree.note IS NULL
            ');
            $request->execute(array(
                ":idUser" => $idUser
            ));
            return $request->fetchAll(PDO::FETCH_ASSOC);
        } catch(Exception $e){
            return false;
        }
    }
    public static function getEtatLieuxSortieReservationUser($idUser){
        try{
            $request = DBA::db()->prepare('
                SELECT 
                    etat_lieux_sortie.id,
                    etat_lieux_sortie.date_etat,
                    etat_lieux_sortie.commentaire,
                    bien.description
                FROM 
                    etat_lieux_sortie
                JOIN 
                    periodeReserve ON etat_lieux_sortie.id_reservation = periodeReserve.id
                JOIN 
                    periodeDispo ON periodeReserve.id_periodeDispo = periodeDispo.id
                JOIN 
                    bien ON periodeDispo.id_bien = bien.id
                JOIN 
                    user ON bien.id_user = user.id
                WHERE 
                    user.id = :idUser
                    AND etat_lieux_sortie.id_piece IS NULL
                    AND etat_lieux_sortie.id_equipement IS NULL
                    AND etat_lieux_sortie.note IS NULL
            ');
            $request->execute(array(
                ":idUser" => $idUser
            ));
            return $request->fetchAll(PDO::FETCH_ASSOC);
        } catch(Exception $e){
            return false;
        }
    }

    
}

?>