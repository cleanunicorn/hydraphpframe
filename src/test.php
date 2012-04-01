<?php

$current_page = get_smarty();

$current_page->assign( 'debug', $_SERVER );

$logged = $User->login( 'hydrarulz', 'a', true );
$logged = $User->is_logged();

$current_page->assign( 'logged', $logged );

$_page_content = $current_page->fetch( 'test.tpl' );
?>