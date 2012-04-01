<?php

function mysql_date_to_unixtime( $mysql_date )
{
    if ( preg_match_all( '/(\d{4}?)-(\d{2}?)-(\d{2}?) (\d{2}?):(\d{2}?):(\d{2}?)/', $mysql_date, $mysql_date_regex_result ) )
    {
        $unixtime = mktime(
                            $mysql_date_regex_result[4][0],
                            $mysql_date_regex_result[5][0],
                            $mysql_date_regex_result[6][0],
                            $mysql_date_regex_result[2][0],
                            $mysql_date_regex_result[3][0],
                            $mysql_date_regex_result[1][0]
                            );
        return $unixtime;
    }
    else
    {
        return false;
    }

}

function my_DBG($msg, $mode = 0)
{
    if (is_array($msg))
    {
    	$msg = var_export($msg, TRUE);
    }
    if ($mode == 1)
    {
        $debug = fopen("/tmp/deb.debug", "a");
        @fputs($debug, __FILE__."[DBG ". date("H:i:s d/m") ."]". $msg ."\n");
        fclose($debug);
    }
    else
    {
        echo $_SERVER['SCRIPT_FILENAME']."[DBG ". date("H:i:s d/m") ."]". $msg ." <br/>\n";
    }
}

function is_md5($string)
{
	preg_match('/[a-fA-F0-9]{32}/', $string, $matches);
	if (count($matches) > 0)
	{
		return TRUE;
	}
	return FALSE;
}

function shorten_url_bit_ly($url)
{
	$c = curl_init('http://api.bit.ly/shorten?version=2.0.1&longUrl='.urlencode($url).'&login=hydrarulz&apiKey=R_535d45c3cb122ca23c63ef7dde39427d');
	curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	$ret = curl_exec($c);
	$aux = json_decode($ret, TRUE);
	$short_url = $aux['results'][$url]['shortUrl'];
	return $short_url;
}

function validate_url($url)
{
	$pattern = '/^(([\w]+:)?\/\/)?(([\d\w]|%[a-fA-f\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w][-\d\w]{0,253}[\d\w]\.)+[\w]{2,4}(:[\d]+)?(\/([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&amp;?([-+_~.\d\w]|%[a-fA-f\d]{2,2})=?)*)?(#([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)?$/';
	return preg_match($pattern, $url);
}

function fahrenheit_to_celsius( $Fahrenheit, $decimals = 1 )
{
	$celsius = 5/9 * ( $Fahrenheit - 32 );
	return sprintf( "%.". $decimals ."f", $celsius );
}

function celsius_to_fahrenheit( $Celsius, $decimals = 1 )
{
	$fahrenheit = 9/5 * $Celsius + 32;
	return sprintf( "%.". $decimals ."f", $fahrenheit );
}

function rewrite_string_to_url( $string )
{
	$string_new = preg_replace( '/[^0-9a-zA-Z]/', '_', $string );
	return $string_new;
}

function filter_cross($filterMe)
{
   $tempMe = $filterMe;
   $filterMe = str_replace("&", "&amp;", $filterMe);
   $filterMe = str_replace("<", "&lt;", $filterMe);
   $filterMe = str_replace(">", "&gt;", $filterMe);
   $filterMe = str_replace('"', "&quot;", $filterMe);
   $filterMe = str_replace("'", "&#39;", $filterMe);

   if (strcmp($tempMe,$filterMe))
   {
       //logHacker();
   }
   return $filterMe;
}

function break_url( $Url = NULL )
{
    if ( $Url == NULL )
    {
        $Url = !empty( $_SERVER['SCRIPT_URL'] ) ? $_SERVER['SCRIPT_URL'] : $_SERVER['REQUEST_URI'] ;
    }
    $parts = explode( '/', $Url );
    if ( empty( $parts[0] ) )
    {
        unset( $parts[0] );
        $parts = array_values( $parts );
    }
    return $parts;
}

function get_smarty()
{
    global $_base_path, $_base_url, $_debug;
    $smarty_obj = new Smarty();
    $smarty_obj->template_dir = $_base_path .'/templates';
    $smarty_obj->compile_dir = $_base_path .'/templates_c';
    $smarty_obj->cache_dir = $_base_path .'/templates_cache';
    $smarty_obj->assign( 'base_url', $_base_url );
    if ( $_debug )
    {
        $smarty_obj->force_compile = true;
        $smarty_obj->caching = 0;
    }
    return $smarty_obj;
}

function error_handler( $error_code, $error_message, $error_file, $error_line, $error_context )
{
    global $_path_to_logs;

    $error_type = array (
                E_ERROR              => 'Error',
                E_WARNING            => 'Warning',
                E_PARSE              => 'Parsing Error',
                E_NOTICE             => 'Notice',
                E_CORE_ERROR         => 'Core Error',
                E_CORE_WARNING       => 'Core Warning',
                E_COMPILE_ERROR      => 'Compile Error',
                E_COMPILE_WARNING    => 'Compile Warning',
                E_USER_ERROR         => 'User Error',
                E_USER_WARNING       => 'User Warning',
                E_USER_NOTICE        => 'User Notice',
                E_STRICT             => 'Runtime Notice',
                E_RECOVERABLE_ERROR  => 'Catchable Fatal Error'
                );

	$error_file_name = $_path_to_logs . '_-application-error-log.txt';
	$error_date_time = date("Y-m-d H:i:s (T)");

	$error_content = "<errorentry> \r\n";
	$error_content .= "\t<datetime>{$error_date_time}</datetime> \r\n";
	$error_content .= "\t<error_code>" . $error_type[$error_code] . "</error_code> \r\n";
	$error_content .= "\t<error_message>{$error_message}</error_message> \r\n";
	$error_content .= "\t<error_file>{$error_file}</error_file> \r\n";
	$error_content .= "\t<error_line>{$error_line}</error_line> \r\n";
	$error_content .= "\t<vartrace>: " . print_r( $error_context, true )  . "</vartrace> \r\n";
	$error_content .= "</errorentry> \r\n";

	@$error_file_handler = fopen( $error_file_name, 'a' );
	@fwrite( $error_file_handler, $error_content );
	@fclose( $error_file_handler );

	switch( $error_code )
	{
		case E_USER_ERROR:
			header('Location: application_error.html');
			die();
			return false;
		break;
		default:
			// continua executia
			return true;
		break;
	}
}

// Generate a random character string
function rand_str($length = 32, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890')
{
    $chars_length = (strlen($chars) - 1);
    $string = $chars{rand(0, $chars_length)};
    for ( $i = 1; $i < $length; $i++ )
    {
        $r = $chars{rand(0, $chars_length)};
        if ($r != $string{$i - 1})
        {
			$string .=  $r;
		}
		else
		{
			$i--;
		}
    }
    return $string;
}

function valid_email($email)
{
   $isValid = true;
   $atIndex = strrpos($email, "@");
   if (is_bool($atIndex) && !$atIndex)
   {
      $isValid = false;
   }
   else
   {
      $domain = substr($email, $atIndex+1);
      $local = substr($email, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64)
      {
         // local part length exceeded
         $isValid = false;
      }
      else if ($domainLen < 1 || $domainLen > 255)
      {
         // domain part length exceeded
         $isValid = false;
      }
      else if ($local[0] == '.' || $local[$localLen-1] == '.')
      {
         // local part starts or ends with '.'
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $local))
      {
         // local part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
      {
         // character not valid in domain part
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $domain))
      {
         // domain part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
                 str_replace("\\\\","",$local)))
      {
         // character not valid in local part unless 
         // local part is quoted
         if (!preg_match('/^"(\\\\"|[^"])+"$/',
             str_replace("\\\\","",$local)))
         {
            $isValid = false;
         }
      }
      if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
      {
         // domain not found in DNS
         $isValid = false;
      }
   }
   return $isValid;
}

function datetime_add_timezone( $Datetime, $Timezone )
{
    $unix = mysql_date_to_unixtime( $Datetime );
    $unix_timezone = $Timezone * 3600;
    $new_date = $unix + $unix_timezone;
    $new_date_formatted = date( "Y-m-d H:i:s", $new_date );
    return $new_date_formatted;
}

function percent_value( $Total, $Percentage )
{
    $value = $Total * $Percentage / 100;
    return $value;
}

function my_sendmail( $From, $To, $Subject, $Body, $From_name = '', $To_name = '' )
{
    global $_mail_host, $_mail_user, $_mail_port, $_mail_pass;

    $mail             = new PHPMailer();
    $body             = $Body ;
    $mail->IsSMTP(); // telling the class to use SMTP
    $mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
                                               // 1 = errors and messages
                                               // 2 = messages only
    $mail->SMTPAuth   = true;                  // enable SMTP authentication
    $mail->Host       = $_mail_host;      // SMTP server
    $mail->Port       = $_mail_port;
    $mail->Username   = $_mail_user;        // SMTP account username
    $mail->Password   = $_mail_pass;        // SMTP account password

    $mail->SetFrom( $From, $From_name );

    $mail->AddReplyTo( $From, $From_name );

    $mail->Subject    = $Subject;
    $mail->MsgHTML($body);

    $mail->AddAddress( $To, $To_name );

    if(!$mail->Send())
    {
        return $mail->ErrorInfo;
    }
    else
    {
        return true;
    }
}

function get_web_page( $url )
{
    $options = array(
        CURLOPT_RETURNTRANSFER      => true,     // return web page
        CURLOPT_HEADER              => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION      => true,     // follow redirects
        CURLOPT_ENCODING            => "",       // handle all encodings
        CURLOPT_USERAGENT           => "spider", // who am i
        CURLOPT_AUTOREFERER         => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT      => 120,      // timeout on connect
        CURLOPT_TIMEOUT             => 120,      // timeout on response
        CURLOPT_MAXREDIRS           => 10,       // stop after 10 redirects
        CURLOPT_SSL_VERIFYHOST      => 0,       // stop after 10 redirects
    );

    $ch      = curl_init( $url );
    curl_setopt_array( $ch, $options );
    $content = curl_exec( $ch );
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );
    curl_close( $ch );

    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;
    $header['content'] = $content;
    return $header;
}


function xml2array($contents, $get_attributes=1, $priority = 'tag')
{
    if(!$contents) return array();

    if(!function_exists('xml_parser_create')) {
        //print "'xml_parser_create()' function not found!";
        return array();
    }

    //Get the XML parser of PHP - PHP must have this module for the parser to work
    $parser = xml_parser_create('');
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, trim($contents), $xml_values);
    xml_parser_free($parser);

    if(!$xml_values) return;//Hmm...

    //Initializations
    $xml_array = array();
    $parents = array();
    $opened_tags = array();
    $arr = array();

    $current = &$xml_array; //Refference

    //Go through the tags.
    $repeated_tag_index = array();//Multiple tags with same name will be turned into an array
    foreach($xml_values as $data) {
        unset($attributes,$value);//Remove existing values, or there will be trouble

        //This command will extract these variables into the foreach scope
        // tag(string), type(string), level(int), attributes(array).
        extract($data);//We could use the array by itself, but this cooler.

        $result = array();
        $attributes_data = array();

        if(isset($value)) {
            if($priority == 'tag') $result = $value;
            else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode
        }

        //Set the attributes too.
        if(isset($attributes) and $get_attributes) {
            foreach($attributes as $attr => $val) {
                if($priority == 'tag') $attributes_data[$attr] = $val;
                else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
            }
        }

        //See tag status and do the needed.
        if($type == "open") {//The starting of the tag '<tag>'
            $parent[$level-1] = &$current;
            if(!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
                $current[$tag] = $result;
                if($attributes_data) $current[$tag. '_attr'] = $attributes_data;
                $repeated_tag_index[$tag.'_'.$level] = 1;

                $current = &$current[$tag];

            } else { //There was another element with the same tag name

                if(isset($current[$tag][0])) {//If there is a 0th element it is already an array
                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
                    $repeated_tag_index[$tag.'_'.$level]++;
                } else {//This section will make the value an array if multiple tags with the same name appear together
                    $current[$tag] = array($current[$tag],$result);//This will combine the existing item and the new item together to make an array
                    $repeated_tag_index[$tag.'_'.$level] = 2;

                    if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
                        $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                        unset($current[$tag.'_attr']);
                    }

                }
                $last_item_index = $repeated_tag_index[$tag.'_'.$level]-1;
                $current = &$current[$tag][$last_item_index];
            }

        } elseif($type == "complete") { //Tags that ends in 1 line '<tag />'
            //See if the key is already taken.
            if(!isset($current[$tag])) { //New Key
                $current[$tag] = $result;
                $repeated_tag_index[$tag.'_'.$level] = 1;
                if($priority == 'tag' and $attributes_data) $current[$tag. '_attr'] = $attributes_data;

            } else { //If taken, put all things inside a list(array)
                if(isset($current[$tag][0]) and is_array($current[$tag])) {//If it is already an array...

                    // ...push the new element into that array.
                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;

                    if($priority == 'tag' and $get_attributes and $attributes_data) {
                        $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
                    }
                    $repeated_tag_index[$tag.'_'.$level]++;

                } else { //If it is not an array...
                    $current[$tag] = array($current[$tag],$result); //...Make it an array using using the existing value and the new value
                    $repeated_tag_index[$tag.'_'.$level] = 1;
                    if($priority == 'tag' and $get_attributes) {
                        if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well

                            $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                            unset($current[$tag.'_attr']);
                        }

                        if($attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
                        }
                    }
                    $repeated_tag_index[$tag.'_'.$level]++; //0 and 1 index is already taken
                }
            }

        } elseif($type == 'close') { //End of tag '</tag>'
            $current = &$parent[$level-1];
        }
    }

    return($xml_array);
}

function client_ip()
{
    if ( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    elseif ( !empty( $_SERVER['REMOTE_ADDR'] ) )
    {
        return $_SERVER['REMOTE_ADDR'];
    }
}

?>