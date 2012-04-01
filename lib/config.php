<?php

/* MySQL */
$_database_type = 'mysql';
$_database_host = 'localhost';
$_database_user = 'mysql_user';
$_database_pass = 'mysql_password';
$_database_name = 'hydraphpframe';

/* Twitter oauth */
$_twitter_consumer_key = 'foo';
$_twitter_consumer_secret = 'bar';
$_twitter_oauth_callback = 'http://site.com/callback';

/* Site Server */
$_base_path = '/var/www/nivelminusunu';
$_base_url = 'http://hydraphpframe.tbwa';

$_debug = true;
$_design = false;

/* Memcached */
$_memcached_enabled = true;
$_memcached_host = '127.0.0.1';
$_memcached_port = 11211;
$_memcached_timeout = 10;

$_mail_host = 'localhost';
$_mail_port = 25;
$_mail_user = 'mailuser';
$_mail_pass = 'mailpass';

if ( @session_start() && !isset( $_SESSION['timezone_server'] ) )
{
    $_timezone_date = new DateTime();
    $_SESSION['timezone_server'] = $_timezone_server = $_timezone_date->format('Z') / 3600;
}

?>
