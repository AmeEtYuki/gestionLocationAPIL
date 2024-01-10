<?php
class Controller {
    foreach ( glob( './models' . '/*.php' ) as $file ) {require( $file );}
}
?>