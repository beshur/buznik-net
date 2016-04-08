<?php
/**
 * Config getter
 */
if (php_sapi_name() !== "cli" && !defined('ABSPATH')) {
	die('1337!');
}

$config = file_get_contents(__DIR__ . '/config.json');
if (count($config)) {
	$config = json_decode($config);
} else {
	throw new Exception('Error Getting Config', 1);	
}

// try get texts if they are not present
if (!file_exists('text_rus.html')) {
	include_once('get_resume_texts.php');
}

return $config;
?>