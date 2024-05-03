<?php 
class view {
    /*
        la fonction getPage permet de charger une vue sans écrire les informations tels que :
            * Le chemin ./views/... 
            * L'extension .php (exemple.php)
    */
    private static function getPage($fileName)  {
        include('./views'.$fileName.'.php');
    } 
    function loginInterface($code, $values) {
        getPage("login");
    }
    function genericAPIresponse($code, $values) {
        getPage("jsonResponse.php");
    }
}
?>