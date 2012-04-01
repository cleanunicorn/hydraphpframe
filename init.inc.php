<?php

session_start();

if ( !isset($_SESSION['session_started']) )
{
    $_SESSION['session_started'] = true;
    $_SESSION['session_new'] = true;
}
else
{
    $_SESSION['session_new'] = false;
}

date_default_timezone_set( 'Europe/Bucharest' );

$_SMARTY_DIR = '/lib/Smarty-3.0rc3/';

/*
 * If init is not loaded from something else than index.php
 * like a cronjob for example
 */
if ( empty( $_base_path ) )
{
    $_base_path = getcwd();
}

ini_set( 'include_path', ini_get('include_path') .
        PATH_SEPARATOR . $_base_path .$_SMARTY_DIR.
        PATH_SEPARATOR . $_base_path .'/src/'.
        PATH_SEPARATOR . $_base_path .'/handjobs/'.
        PATH_SEPARATOR . $_base_path .'/lib/'.
        PATH_SEPARATOR . $_base_path .'/lib/utils/'.
        PATH_SEPARATOR . $_base_path .'/lib/users/'.
        PATH_SEPARATOR . $_base_path .'/lib/design/'.
        PATH_SEPARATOR . $_base_path .'/lib/twitter-oauth/'.
        PATH_SEPARATOR . $_base_path .'/lib/PHPMailer_v5.1/'.
        PATH_SEPARATOR . $_base_path .'/lib/adodb5/'.
        PATH_SEPARATOR . $_base_path .'/lib/base_class/'.
        PATH_SEPARATOR . $_base_path .'/lib/cms/'
       );

require( 'config.php' );
require( 'functions.php' );
require( 'handy_user_class_extended.inc.php' );
require( 'adodb.inc.php' );
require( 'lib/design/design.inc.php' );
require( 'lib/PHPMailer_v5.1/class.phpmailer.php' );
require( 'lib/cms/cms_pages.inc.php' );
require( 'lib/base_class/base_class.inc.php' );

define( 'SMARTY_DIR', $_base_path .$_SMARTY_DIR );
require_once( SMARTY_DIR .'/Smarty.class.php' );

if ( $_debug )
{
    error_reporting( E_ALL );
    $_main_time_start = microtime( true );
}
else
{
    error_reporting( 0 );
    set_error_handler( 'error_handler' );
}

/* Initializing Smarty */
$current_page = get_smarty();

/* Initializing DB_MySQL */
$dsn_string = "$_database_type://$_database_user:$_database_pass@$_database_host/$_database_name";
$DB = NewADOConnection( $dsn_string );
if ( !$DB && $_debug )
{
    my_DBG( "Database connect error!" );
}
if ( $_debug )
{
    $DB->LogSQL( true );
    //$DB->debug = 1;
}

/* Timezone */
$_SESSION['datetime'] = date( "Y-m-d H:i:s" );
$_SESSION['datetime_unix'] = time();
$_SESSION['datetime_gmt'] = datetime_add_timezone( $_SESSION['datetime'], -$_SESSION['timezone_server'] );
$_SESSION['datetime_gmt_unix'] = $_SESSION['datetime_unix'] - $_SESSION['timezone_server'] * 3600;

/* Initializing User Class */
$User = new user_class_extended( $DB );
if ( !isset( $_SESSION['username'] ) )
{
    if ( $User->is_logged() )
    {
        $_SESSION['is_logged'] = 1;
    }
}

if ( $_design && FALSE ) // Making sure this is disabled until the library is updated to fit the new database library
{
    /* Initializing Design Monitoring */
    $Design = new Design_monitor( $DB, isset( $_SESSION['design_vizitator_id'] ) ? $_SESSION['design_vizitator_id'] : null, $User->USER_ID );
    if ( $Design )
    {
            $_SESSION['design_vizitator_id'] = $Design->Vizitator_id;
    }
}

/* Initializing CMS */
$Cms_pages = new cms_pages_class( $DB );

?>