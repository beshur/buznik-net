<?php
/**
 * Get and cache resume texts from a remote server
 * using cron tab.
 */
if (php_sapi_name() !== 'cli' && !defined('ABSPATH')) {
	die('1337!');
}
include('config.php');

// show that we need minimum from WP
define('SHORTINIT', true);

// load WordPress environment and database connection
require_once( dirname(__FILE__) . '/j/wp-load.php' );

// only DB
// globals $wp, $wp_query, $wp_the_query are not set...
global $wpdb;
$result = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id='". $config->page_id . "'");

$success = true;
if ($config->auto_update == false) {
	return;
}

$keys = $config->languages;
$texts = [];
foreach ($keys as $key) {
	$filename = 'text_'.$key.'.html';
	$text = "";
	foreach($result as $entry) {
		$log = "key " . $key . " " . $entry->meta_key;
    if ($key == $entry->meta_key) {
			$texts[$key] = $entry->meta_value;
			break;
		}
	}

	if (!empty($texts[$key])) {
		$file = fopen(dirname(__FILE__).'/'.$filename, 'w');
		fwrite($file, $texts[$key]);
		fclose($file);
	} else {
		$success = false;
		exit('ERROR: trying to download '.$filename);
	}
};
echo ($success) ? 'ALL OK' : 'Unexpected error';
?>
