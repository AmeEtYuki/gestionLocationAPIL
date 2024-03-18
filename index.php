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
$in = json_decode(file_get_contents('php://input'));
if($in!=null){
    switch($_GET['action']) {
        case "login":
            //on y retrouve que le hash du password
            (new controller)->connection($in);
            break;
        case "logout":
            (new controller)->deconnexion($in);
            break;
        default:
            http_response_code(404);
    }
}else {
    http_response_code(400);
}