<?php
class Erreur {
    public static function registerError($data) {
        try {
            $prepare = DBA::db()->prepare('INSERT INTO `erreurs` (`id`, `ip`, `data`, `route`) VALUES (NULL, :ip, :erreur, :route) ');
            $prepare->execute(array(
                ":erreur"=>serialize($data),
                ":ip"=>$_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'],
                ":route"=>$_GET['action']
            ));
        } catch (Exception $e) {
            //dans le fichier erreur.txt écrire sur une nouvelle ligne avec un petit espacement la ou les erreurs.

            $fileName = "erreur.html";
            if( file_exists($fileName) && (time()-filectime($fileName)) > 10*60 ) {
                unlink($fileName);
            }
            $file = fopen($fileName, "a");
            fwrite($file,serialize($data));
            "<tr>
                <td class='tr'>".$_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR']."
                <td class='tr'>
                    ".json_encode($data).$_GET['action']."
                </td>
            </tr><br>"."<br>\n";
            fwrite($file, $e->getMessage());
            fclose($file);
            http_response_code(500);
        }
        
    }
    public static function getAllErrors() {
        try {
            $prepare = DBA::db()->prepare("SELECT * FROM erreurs ORDER BY date desc");
            $prepare->execute();
            return $prepare->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            http_response_code(500);
        }
    }
}