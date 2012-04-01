<?php
include( 'init.inc.php' );

$_top_page = get_smarty();
$_main_page = get_smarty();
$_footer_page = get_smarty();
$URL = break_url();

$_load = sys_getloadavg();
if ( $_load[1] > 5 )
{
    header('HTTP/1.1 503 Too busy, try again later');
    die('Server too busy. Please try again later.');
}

$layout = 'site';

$_top_content = '';
$_page_content = '';
$_footer_content = '';

$_page_title = '';
$_page_keywords = '';

$_output = true;

switch( $URL[0] )
{
    case '':
        include_once( 'src/home.php' );
        break;
    case 'debug':
        $_output = false;
        phpinfo();
        break;
    default:
        $url_full = '/'. implode( '/', $URL );

        $cms_data = $Cms_pages->read( $url_full );
        if ( $cms_data !== false )
        {
            if ( isset( $cms_data[ $Cms_pages->TABLE_COLUMNS['content'] ] ) )
            {
                $_page_content .= $cms_data[ $Cms_pages->TABLE_COLUMNS['content'] ];
            }
            if ( isset( $cms_data[ $Cms_pages->TABLE_COLUMNS['title'] ] ) )
            {
                $_page_title .= $cms_data[ $Cms_pages->TABLE_COLUMNS['title'] ];
            }
            if ( isset( $cms_data[ $Cms_pages->TABLE_COLUMNS['keywords'] ] ) )
            {
                $_page_keywords .= $cms_data[ $Cms_pages->TABLE_COLUMNS['keywords'] ];
            }
        }
        
        break;
}

if ( $_debug )
{
    $_run_time = microtime( true ) - $_main_time_start;
    $_main_page->assign( 'run_time', $_run_time );
}

$_main_page->assign( 'page_title', $_page_title );
$_main_page->assign( 'page_keywords', $_page_keywords );

$_main_page->assign( 'top_content', $_top_content );
$_main_page->assign( 'page_content', $_page_content );
$_main_page->assign( 'footer_content', $_footer_content );

// If it's a AJAX request, give the result json style
if( $_output )
{
    if ( !strtolower( @$_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest' )
    {
            $_main_page->display( '_layout_'. $layout .'.tpl' );
    }
    else
    {
            $_ajax_result = array(
                                    'error' => @$_ajax_message,
                                    'data' => @$_ajax_data
                                     );
            if ( $_debug )
            {
                    $_ajax_result['time'] = $_run_time;
            }
            echo json_encode( $_ajax_result );
    }
}

?>