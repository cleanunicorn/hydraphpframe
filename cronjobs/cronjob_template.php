<?php
$_base_path = '/var/www/hydraphpframe';
// Include the init for the application
ini_set( 'include_path', ini_get('include_path') .
        PATH_SEPARATOR . $_base_path
        );
require( 'init.inc.php' );

// Example code
// my_DBG( $_SERVER );
?>