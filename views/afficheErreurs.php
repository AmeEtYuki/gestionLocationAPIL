<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<meta http-equiv="refresh" content="2">
<style>
    .table {
        width: 100%;
        align-self: center;
    }
    .tr {
        border: 1px black;
    }
    td {
        padding:5px;
    }
</style>
<body>
<table class=table>
    <tr class="tr">
        <td class="tr">
            Date
        </td>
        <td class="tr">
            Route
        </td>
        <td class="tr">
            IP
        </td>
        <td class="tr">
            Contenu du dump
        </td>
    </tr>
    
<?php
foreach(Erreur::getAllErrors() as $erreur) {
    ?>
        <tr>
            <td class="tr">
                <?=$erreur["date"]?>
            </td>
            <td class="tr">
                <?=$erreur["route"]??"&NULL&"?>
            </td>
            <td class="tr">
                <?=$erreur["ip"]?>
            </td>
            <td class="tr">
                <?=json_encode($erreur["data"])?>
            </td>
        </tr>

    <?php
} 

//ouvre le fichier erreurs.txt puis affiche les erreurs
include "erreur.txt";
?>
</table>
</body>
</html>