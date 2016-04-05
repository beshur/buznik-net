<?php
/**
 * Config getter
 */
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

$config = file_get_contents('config.json');
if (count($config)) {
	$config = json_decode($config);
	return $config;
} else {
	throw new Exception('Error Getting Config', 1);	
}
?>