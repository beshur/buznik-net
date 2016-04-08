<?php
/**
 * Get and cache resume texts from a remote server
 * using cron tab.
 */
if (php_sapi_name() !== "cli" && !defined('ABSPATH')) {
	die('1337!');
}

include('config.php');
if ($config->auto_update == false) {
	return;
}

function parseHeaders( $headers ) {
    $head = array();
    foreach( $headers as $k=>$v ) {
        $t = explode( ':', $v, 2 );
        if( isset( $t[1] ) ) {
            $head[ trim($t[0]) ] = trim( $t[1] );
        } else {
            $head[] = $v;
            if( preg_match( "#HTTP/[0-9\.]+\s+([0-9]+)#",$v, $out ) ) {
                $head['response_code'] = intval($out[1]);
            }
        }
    }
    return $head;
}


$texts = $config->languages;
foreach ($texts as $key) {
	$filename = 'text_'.$key.'.html';
	$fileUrl = $config->text_location.$filename;
	$try_file = file_get_contents($fileUrl);
	$headers = parseHeaders($http_response_header);
	// echo 'file: '.$filename;
	// echo ' ('.$headers['response_code'].')';
	// echo ':<br>';
	// echo $try_file;
	if ($headers['response_code'] == 200) {
		$texts[$key] = $try_file;
		$file = fopen(__DIR__.'/'.$filename, 'w');
		fwrite($file, $try_file);
		fclose($file);
	} else {
		echo 'Error trying to download '.$filename;
	}
};

?>