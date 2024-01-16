<?php
//importation de tout les modèles : 
foreach ( glob( './models' . '/*.php' ) as $file ) {require( $file );}
//importation du VC + base de données 
require("./database.php");
require("./controller.php");
require("./view.php");

switch($_GET['action']) {
    case "login":
        (new controller)->connection();
        break;
    case "logout":
        (new controller)->logout();
        break;
    default:
        http_response_code(404);
}