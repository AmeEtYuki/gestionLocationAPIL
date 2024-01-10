<?php
//importation de tout les modèles : 
echo "actualise !";
foreach ( glob( './models' . '/*.php' ) as $file ) {require( $file );}
//importation du VC + base de données 
require("./database.php");
require("./controller.php");
require("./view.php");

(new controller)->test();