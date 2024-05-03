<?php

class piece {

    public static function getInformationFromPiece($id) {
        $request = DBA::db()->prepare("SELECT * FROM piece WHERE id = :id");
        $request->execute(array(
            ":id"=>$id
        ));
        return $request->fetch(PDO::FETCH_ASSOC);
    }
    //use
    public static function getAllPiecesFromBien($bien) {
        $request = DBA::db()->prepare("SELECT * FROM piece WHERE id_bien = :id_bien");
        $request->execute(array(
            ":id_bien"=>$bien
        ));
        return $request->fetchAll(PDO::FETCH_ASSOC);
    }


}

?>