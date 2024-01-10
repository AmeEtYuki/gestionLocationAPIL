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
echo "actualise !";
foreach ( glob( './models' . '/*.php' ) as $file ) {require( $file );}
//importation du VC + base de données 
require("./database.php");
require("./controller.php");
require("./view.php");

(new controller)->test();