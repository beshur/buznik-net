<?php
/**
 * Config getter
 */
if (php_sapi_name() !== "cli" && !defined('ABSPATH')) {
	die('1337!');
}
$config = file_get_contents(dirname(__FILE__) . '/config.json');
if (count($config)) {
	$config = json_decode($config);
} else {
	throw new Exception('Error Getting Config', 1);
}

return $config;
?>
