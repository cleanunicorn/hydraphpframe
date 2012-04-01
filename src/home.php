<?php

$current_page->assign( 'debug', $_SERVER );

$logged = $User->is_logged();

$current_page->assign( 'logged', $logged );

$_page_content = $current_page->fetch( 'home.tpl' );
?>