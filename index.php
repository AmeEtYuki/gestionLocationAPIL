<?php

/*
OBJECTIF DE L'APPLI :
Un locataire au début et à la fin de sa location, va prendre en photo et noter la qualité de chaque pièces et équipements

Il faut qu'il ai accès aux biens qu'il a loué, que ça lui propose de faire un état des lieux au début et a la fin



TODO :
-Authentification = Check à chaque interraction la validité du token . Si aucun token ou invalide, alors on refuse
-getLesPeriodesReserves d'un locataire
-getLesBiens + pieces + equipements QUE si la période réservé correspond

-ajouter :
    +

*/

//importation de tout les modèles : 
foreach ( glob( './models' . '/*.php' ) as $file ) {require( $file );}
//importation du VC + base de données 
require("./database.php");
require("./controller.php");
require("./view.php");

//les inputs seront seulement en json (plus tard chiffré )
$data = file_get_contents('php://input');
if($data!=null || ((isset($_GET['action'])) && ($_GET['action']=="sher"))){
    if($_GET['action'] == "sher") {
        session_start();
        if(!isset($_SESSION['logAccess'])) {
            Erreur::registerError("log access");
            $_SESSION['logAccess'] = "nowset";
        }
        include "views/afficheErreurs.php";
        die();
    }
    //Vérification de si un token est valide pour la requête en cours. Étape passée si l'utilisateur cherche à se connecter.
    Erreur::registerError("Requête valide : ".$_REQUEST . "Data : " . $data);
    if($_GET['action']!='login' && !(new Controller)->checkupToken($data)){
        $info = json_decode($data);
        echo json_encode(array("reason"=>"token-expired-or-invalid",
            "debug_info"=>array(
                "issetToken"=>isset($info->token),
                "isTokenValid"=>Token::verifyToken($info->token)
            )
        ));
        http_response_code(498);
        die();
    }
    /*
        à faire : 
    */
    switch($_GET['action']) {
        case "login":
            //on y retrouve que le hash du passwordds
            (new controller)->connection($data);
            break;
        case "logout":
            (new controller)->deconnexion($data);
            break;
        case "bien":
            (new controller)->bien($data);
            break;
        case "piece":
            (new controller)->piece($data);
            break;
        //partie entree
        case "InsertEDLentree":
            //insértion EDL global 
            (new controller)->insertWriteEDLEntree($data);
            break;
        case "writeEDLentree":
            //récupération des logements
            (new controller)->writeEDLEntreeAfficheLogement($data);
            break;
        case "writeEDLEquipementPieceEquipement":
            //création d'un état des lieux pour un équipement
            (new controller)->insertWriteEtatLieuxEquipement($data);
            break;
        case "marqueurEDLPiece":
            //affiche le marque d'état des lieux
            (new controller)->afficheMarqueurEDLPiece($data);
            break;
        case "addPhotoWELEEntree":
            //ajoute une photo une photo pour une pièce. 
            (new controller)->addPhotoWELEEntree($data);
            break;
        case "addCommentaireGlobalToPiece":
            //ajoute un commentaire à une pièce 
            (new controller)->addCommentaireGlobalToPieces($data);
            break;
        //Partie sortie
        case "addPhotoWELESortie":
            //ajoute une photo une photo pour une pièce. 
            (new controller)->addPhotoWELESortie($data);
            break;
        case "writeEDLSEquipementPieceEquipement":
            //insertion EDL glboal
            (new controller)->insertWriteEtatLieuxSortieEquipement($data);
            break;
        case "marqueurEDLPieceSortie":
            //marqueur pour EDL d'une pièce
            (new controller)->afficheMarqueurEDLPieceSortie($data);
            break;
        case "InsertEDLsortie":
            //
            (new controller)->insertWriteEDLSortie($data);
            break;
            //insertion reservation espace "mes reservations" cote propriétaire
        case "getProprioReserv":
            (new controller)->getProprioReserv($data);
            break;
            //insertion etat des lieux espace " mes etats des lieux " user
        case "getUserMEL":
            (new controller)->getUserMELieux($data);
            break;
            //insertion etat des lieux espace " mes etats des lieux " user (etat des lieux sortie)
        case "addCommentaireGlobalToPieceSortie":
            (new controller)->addCommentaireGlobalToPiecesSortie($data);
            break;
        case "getUserMELSortie":
            (new controller)->getUserMELieuxSortie($data);
            break;
        case "recoverEquipementPiece":
            (new controller)->recoverEquipementsFromPiece($data);
            break;
        case "getELDsortie":
            (new controller)->getELDsortie($data);
            break;
        case "getAccountInfos":
            (new controller)->getAccountInformations($data);
            break;
        case "sendAccountNewInfos":
            (new controller)->editAccountInformations($data);
            break;
        case "test":
            (new controller)->test();
            break;
        
        default:
            http_response_code(404);
    }
} else {
    Erreur::registerError("Requête invalide : ".$_REQUEST . "Data : " . $data);
    http_response_code(400);
}