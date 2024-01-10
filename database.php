<?php
    $data = parse_ini_file("../db.ini");
    $dbuser = $data["dbuser"];
    $dbpassword = $data["dbpassword"];
    $dbdatabase = $data["dbdatabase"];
    $dbhost = $data["dbhost"];
    $dbport = $data["dbport"];
    $dsn = "mysql:host=".$dbhost.";dbname=".$dbdatabase;

    //well no, it's not stored here anymore :)

    function getPDO() {
        global $dsn;
        global $dbuser;
        global $dbpassword;
        return new PDO($dsn, $dbuser, $dbpassword);
    }
?>