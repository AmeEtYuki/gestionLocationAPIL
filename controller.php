<?php
class Controller {
    
    public static function connection($json) {
        $data = json_decode($json);
        $login = $data->login;
        $password = $data->password;

        if(Utilisateur::utilisateurExiste($login)) {
            //vérification des informations de connexion
            $connexion = Utilisateur::connexion($login, $password);
            if($connexion == 0) {
                //Je considère que l'utilisateur n'as qu'un seul téléphone, je détruirais donc tout autre token créé pour son compte.
                $idUtilisateur = Utilisateur::getUserIdByEmail($login);
                Token::destroyTokenFromUser($idUtilisateur);

                $token = Token::createTokenForUser($idUtilisateur);
                echo json_encode(array(
                    "token"=>$token,
                    "isHost"=>Utilisateur::isHost($idUtilisateur),
                    "nom"=>Utilisateur::getNom($idUtilisateur),
                    "prenom"=>Utilisateur::getPrenom($idUtilisateur)
                ));
                http_response_code(200);
            }
        }
    }
    public static function deconnexion($json) {
        $data = json_decode($json);
        $token = $data->token;
        if(Token::verifyToken($token)) {
            Token::destroyToken($token);
        }
    }
    public function checkupToken($json) {
        //vérifie si le Token en cours d'utilisation est toujours valide, si un JSON est envoyé.
        $data = json_decode($json);
        return (isset($data->token))?Token::verifyToken($data->token):false;
    }
    public static function bien($json) {
        $data = json_decode($json);
        $userID = Token::getUserID($data->token);
        $method = $_SERVER['REQUEST_METHOD'];
        if($method == "POST") {
            echo json_encode(Bien::getAllBiensFromUser($userID));
            http_response_code(200);
            exit();
        } else {
            Erreur::registerError(json_encode(array("message"=>"Wrong method", "used_method"=>$method)));
            http_response_code(400);
            //exit();
        }       
        
    }
    
    public static function photo($json){
        $data = json_decode($json);
        $userID = Token::getUserId($data->token);
        $method = $_SERVER['REQUEST_METHOD'];
        /*
            GET : Obtiens toute les photos d'un bien spécifique

        */
        switch ($method) {
            case 'POST':
                //echo json_encode(Photo::getAllPhotosFromBien($data->bienID));
                http_response_code(200);
                break;
            default:
                http_response_code(400);
        }
    }
    // mes etats lieux coté user
    public static function getUserMELieux($json){
        try{
            $data = json_decode($json);
            if (!isset($data->token)) {
                throw new Exception("Token missing  ='( ");
            }
            $idUser = Token::getUserID($data->token);
            $resultat = EtatLieuxEntree::getEtatLieuxReservationUser($idUser);
            echo json_encode($resultat);
            http_response_code(200);
        } catch (Exception $e){
            Erreur::registerError(json_encode(array("message"=>$e->getMessage())));
            http_response_code(400);
        }
    }
    // mes etats lieux coté user sortie
    public static function getUserMELieuxSortie($json){
        try{
            $data = json_decode($json);
            if (!isset($data->token)) {
                throw new Exception("Token missing  ='( ");
            }
            $idUser = Token::getUserID($data->token);
            $resultat = EtatLieuxEntree::getEtatLieuxSortieReservationUser($idUser);
            echo json_encode($resultat);
            http_response_code(200);
        } catch (Exception $e){
            Erreur::registerError(json_encode(array("message"=>$e->getMessage())));
            http_response_code(400);
        }
    }
    // mes reservations (user proprietaire) : 
    public static function getProprioReserv($json){
        try{
            $data = json_decode($json);
            if (!isset($data->token)) {
                throw new Exception("Token missing  ='( ");
            }
            $proprietaireID = Token::getUserID($data->token);
            $resultat = Reservation::getProprietaireReservation($proprietaireID);
            echo json_encode($resultat);
            http_response_code(200);
        } catch (Exception $e){
            Erreur::registerError(json_encode(array("message"=>$e->getMessage())));
            http_response_code(400);
        }
    }
    
    // etat lieux 
    public static function piece($json) {
        try{
            $data = json_decode($json);

            if (!isset($data->token)) {
                throw new Exception("Token missing  ='( ");
            }
            $userID = Token::getUserID($data->token);
            $method = $_SERVER['REQUEST_METHOD'];

            if ($method != "POST") {
                throw new Exception("Wrong method snifou");
            }

            $affichebien = Piece::getAllPiecesFromBien($data->idBien);
            echo json_encode($affichebien);
            http_response_code(200);
        } catch (Exception $e){
            Erreur::registerError(json_encode(array("message"=>$e->getMessage())));
            http_response_code(400);
        }
    }


    //affichage des résevations 
    public static function writeEDLEntreeAfficheLogement($json){
        try {
            $data = json_decode($json);
    
            // Vérifier si le token est valide
            if (!isset($data->token)) {
                throw new Exception("Token missing");
            }
            $userID = Token::getUserID($data->token);
            $method = $_SERVER['REQUEST_METHOD'];
    
            // Vérifier la méthode HTTP utilisée
            if ($method != "POST") {
                throw new Exception("Wrong method");
            }
    
            // Récupérer les réservations pour l'utilisateur

            $reservations = (Utilisateur::isHost($userID))?EtatLieuxEntree::getReservationsToWriteEDLHote($userID):EtatLieuxEntree::getReservationsToWriteEDLLocataire($userID);
            
            echo json_encode($reservations);
            http_response_code(200);
        } catch (Exception $e) {
            Erreur::registerError(json_encode(array("message"=>$e->getMessage())));
            http_response_code(400);
        }
    }
    public function insertWriteEtatLieuxSortieEquipement($json) {
        try {
            $data = json_decode($json);
            $idReservation = $data -> idReservation;
            $idPiece = $data -> idPiece;
            $idEquipement = $data -> idEquipement;
            $note =  $data -> note;
            EtatLieuxSortie::createEtatLieuxEquipement($idReservation, $idPiece, $idEquipement ,$note) ;
        } catch (Exception $e) {
            Erreur::registerError(json_encode(array("message"=>$e)));
            http_response_code(500);
        }
    }
    
    //affiche les équipements par pièce
    public static function recoverEquipementsFromPiece($json){
        try{
            $data = json_decode($json);
            if (!isset($data->token)){
                throw new Exception ("Token missing");
            }
            $idPiece = $data->idPiece;
            $equipements = Equipement::getAllEquipementsFromPiece($idPiece);
            echo json_encode($equipements);
            http_response_code(200);
        }catch (Exception $e) {
            Erreur::registerError(json_encode(array("message"=>$e->getMessage())));
            http_response_code(400);
        }
    }

    //affiche si l'état des lieux est fait pou rune pièce données 
    public static function afficheMarqueurEDLPiece($json){
        try{
            $data = json_decode($json);
            if (!isset($data->token)) {
                throw new Exception("Token missing  ='( ");
            }
            $idReservation = $data->idReservation;
            $idPiece = $data->idPiece;
            $resultat = EtatLieuxEntree::afficheMarqueurEDLPieceRealisee($idReservation, $idPiece);
            if ($resultat){
                echo json_encode(1);
                http_response_code(200);
            }else{
                echo json_encode(0);
            }
        } catch (Exception $e){
            Erreur::registerError(json_encode(array("message"=>"snif"+$e->getMessage())));
            http_response_code(400);
        }
    }
    public static function afficheMarqueurEDLPieceSortie($json){
        try{
            $data = json_decode($json);
            if (!isset($data->token)) {
                throw new Exception("Token missing  ='( ");
            }
            $idReservation = $data->idReservation;
            $idPiece = $data->idPiece;
            $resultat = EtatLieuxSortie::afficheMarqueurEDLPieceRealisee($idReservation, $idPiece);
            if ($resultat){
                echo json_encode(1);
                http_response_code(200);
            }else{
                echo json_encode(0);
            }
        } catch (Exception $e){
            Erreur::registerError(json_encode(array("message"=>"snif"+$e->getMessage())));
            http_response_code(400);
        }
    }


    public static function insertWriteEDLEntree($json){
        try{
            $data = json_decode($json);
            if (!isset($data->token)){
                throw new Exception ("Token missing");
            }
            $idReservation = $data->idReservation;
            $commentaire = $data->commentaire;
            $resultat = EtatLieuxEntree::insertEDLGlobalDuLogement($idReservation, $commentaire);
            if ($resultat){
                echo json_encode($resultat);
                http_response_code(200);
            }else{
                Erreur::registerError(json_encode(array("message"=>" Erreur lors de l'insertion de l'état des lieux ")));
                http_response_code(500);
            }
        } catch (Exception $e){
            Erreur::registerError(json_encode(array("message"=>$e->getMessage())));
            http_response_code(400);
        }
    }
    public static function insertWriteEDLSortie($json){
        try{
            $data = json_decode($json);
            if (!isset($data->token)){
                throw new Exception ("Token missing");
            }
            $idReservation = $data->idReservation;
            $commentaire = $data->commentaire;
            $resultat = EtatLieuxSortie::insertEDLGlobalDuLogement($idReservation, $commentaire);
                echo json_encode($resultat);
                http_response_code(200);
        } catch (Exception $e){
            Erreur::registerError(json_encode(array("message"=>$e->getMessage())));
            http_response_code(500);
        }
    }
    
    public static function insertWriteEtatLieuxEquipement($json){
        try{
            $data = json_decode($json);
            if(!isset($data->token)){
                throw new Exception("Token missing");
            }
            $idReservation = $data -> idReservation;
            $idPiece = $data -> idPiece;
            $idEquipement = $data -> idEquipement;
            $note =  $data -> note;
            $resultat = EtatLieuxEntree::createEtatLieuxEquipement($idReservation, $idPiece, $idEquipement ,$note);
            if ($resultat){
                echo json_encode($resultat);
                http_response_code(200);
            }else{
                Erreur::registerError(json_encode(array("message"=>" Erreur lors de l'insertion de l'état des lieux des équipements de la pièce")));
                http_response_code(500);
            }
        }catch (Exception $e){
            Erreur::registerError(json_encode(array("message"=>$e->getMessage())));
            http_response_code(500);
        }
    }
    
    public static function addPhotoWELEEntree($json){        
        try{
            $data = json_decode($json);
            if(!isset($data->token)){
                throw new Exception("Token missing");
            }
            $idReservation = $data -> idReservation;
            $idPiece = $data -> idPiece;
            $chemin = $data -> chemin;
            $resultat = Photo::addPhotosToPieceEntree($idPiece, $chemin, $idReservation);
            if ($resultat){
                echo json_encode($resultat);
                http_response_code(200);
            }else{
                Erreur::registerError(json_encode(array("message"=>" Erreur lors de l'insertion de l'état des lieux des photos de la pièce")));
                http_response_code(500);
            }
        }catch (Exception $e){
            Erreur::registerError(json_encode(array("message"=>$e->getMessage())));
            http_response_code(400);
        }
    }
    public static function addPhotoWELESortie($json){        
        try{
            $data = json_decode($json);
            if(!isset($data->token)){
                throw new Exception("Token missing");
            }
            $idReservation = $data -> idReservation;
            $idPiece = $data -> idPiece;
            $chemin = $data -> chemin;
            $resultat = Photo::addPhotosToPieceSortie($idPiece, $chemin, $idReservation);
            if ($resultat){
                echo json_encode($resultat);
                http_response_code(200);
            }else{
                Erreur::registerError(json_encode(array("message"=>" Erreur lors de l'insertion de l'état des lieux des photos de la pièce")));
                http_response_code(500);
            }
        }catch (Exception $e){
            Erreur::registerError(json_encode(array("message"=>$e->getMessage())));
            http_response_code(400);
        }
    }
    public static function addCommentaireGlobalToPieces($json){
        try{
            $data = json_decode($json);
            if(!isset($data->token)){
                throw new Exception("Token missing");
            }
            $idReservation = $data -> idReservation;
            $idPiece = $data -> idPiece;
            $commentaire = $data -> commentaire;
            $resultat = EtatLieuxEntree::createEtatLieuxPieceCommentaire($idReservation, $idPiece, $commentaire);
            if ($resultat){
                echo json_encode($resultat);
                http_response_code(200);
            }else{
                Erreur::registerError(json_encode(array("message"=>" Erreur lors de l'insertion de l'état des lieux des commentaire de la pièce")));
                http_response_code(500);
            }
        }catch (Exception $e){
            Erreur::registerError(json_encode(array("message"=>$e->getMessage())));
            http_response_code(400);
        }
    }
    public static function addCommentaireGlobalToPiecesSortie($json){
        try{
            $data = json_decode($json);
            if(!isset($data->token)){
                throw new Exception("Token missing");
            }
            $idReservation = $data -> idReservation;
            $idPiece = $data -> idPiece;
            $commentaire = $data -> commentaire;
            $resultat = EtatLieuxSortie::createEtatLieuxPiece($idReservation, $idPiece, $commentaire);
            if ($resultat){
                echo json_encode($resultat);
                http_response_code(200);
            }else{
                Erreur::registerError(json_encode(array("message"=>" Erreur lors de l'insertion de l'état des lieux des commentaire de la pièce")));
                http_response_code(500);
            }
        }catch (Exception $e){
            Erreur::registerError(json_encode(array("message"=>$e->getMessage())));
            http_response_code(400);
        }
    }
    public static function reservation($json) {
    }

    public static function test(){
        //fonction testant la capacité de l'api à fonctionner (test simple visant à déterminer si le code est globalement défaillant au niveau de l'api)
        try {
            //test de fonction de la base de données. 
            $bddWorks = DBA::db()->prepare('SELECT * FROM bien limit 10')->execute();
            $staticJsonToTest = array(
                "TestValue"=>"ThisValueIsHereToTestAPIGoodCommunications"
            );
            echo json_encode($staticJsonToTest);
            http_response_code(200);
        } catch (Exception $e) {
            Erreur::registerError(json_encode(array("message"=>$e->getMessage())));
            http_response_code(500);
        }
    }

    public function getELDsortie($data) {
        try {
            $data = json_decode($data);
            $userID = Token::getUserID($data->token);
            $method = $_SERVER['REQUEST_METHOD'];
            if ($method != "POST") {
                http_response_code(405);
            }
            echo json_encode(
                (Utilisateur::isHost($userID))?EtatLieuxSortie::getForHost($userID):EtatLieuxSortie::getForRenter($userID)
            );
            Erreur::registerError(json_encode(
                (Utilisateur::isHost($userID))?EtatLieuxSortie::getForHost($userID):EtatLieuxSortie::getForRenter($userID)
            ));
            http_response_code(200);
            die();
        } catch (Exception $e) {
            Erreur::registerError(json_encode(array("message"=>$e->getMessage())));
            http_response_code(500);
        }
    }
    public function getAccountInformations($data) {
        try {
            $data = json_decode($data);
            $userID = Token::getUserID($data->token);
            //
            echo json_encode(Utilisateur::getAllInformations($userID));
        } catch (Exception $e) {
            Erreur::registerError(json_encode(array("message"=>$e->getMessage())));
            http_response_code(500);
        }
    }
    public function editAccountInformations($data) {
        try {
            $data = json_decode($data);
            $userID = Token::getUserID($data->token);
            //$code = Utilisateur::connexion($userID, $data->actualPassword ?? "");
            $code = 0;
            switch ($code) {
                case 0: {
                    $newLogin = $data->login ?? Utilisateur::getAllInformations($userID)["login"];
                    $newPassword = $data->password ?? Utilisateur::getAllInformations($userID)["password"];
                    $newNom = $data->nom ?? Utilisateur::getAllInformations($userID)["nom"];
                    $newPrenom = $data->prenom ?? Utilisateur::getAllInformations($userID)["prenom"];
                    Utilisateur::editAccount($userID, $newLogin, $newPassword, $newNom, $newPrenom);
                    echo json_encode(array("message"=>"done"));
                    http_response_code(200);
                }
                case 1: {
                    http_response_code(401);
                }
                case 2: {
                    http_response_code(418);
                }
            }
        } catch (Exception $e) {
            Erreur::registerError(json_encode(array("message"=>$e->getMessage())));
            http_response_code(500);
            die();
        }
    }

}
?>