<?php
class Equipement {

    //use 
    public static function getAllEquipementsFromPiece($idPiece) {
        try{
            $request = DBA::db()->prepare("SELECT * FROM equipement WHERE id_piece = :idPiece");
            $request->execute(array(
                ":idPiece"=>$idPiece
            ));
            $equipements = $request->fetchAll(PDO::FETCH_ASSOC);
            return $equipements;
        } catch (PDOException $e){
            error_log('Erreur lors de la recupération des équipements de la pièces ');
            return false;  
        }
    }


}