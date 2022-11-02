<?php
/**
 * Buznik.net start
 */
/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

require_once ABSPATH . '/config.php';
require_once ABSPATH . '/lang_strings.php';
$config = new Config();
// try get texts if they are not present
if (!file_exists('index.html')) {
	include_once('get_resume_texts.php');
}

$lang = 'eng';
if (isset($_GET['lang'])) {
	$lang = $_GET['lang'];
}
if (isset($argv) && count($argv) > 1) {
	$lang = $argv[1];
}

function get_string_from_obj($obj) {
	global $lang;
	$pos = 0;
	switch ($lang) {
		case 'ukr':
			$pos = 1;
			break;
		case 'eng':
			$pos = 2;
			break;
		default:
			break;
	}
	return $obj[$pos];
};

function render_string($key) {
	global $lang;
	echo_string($key, $lang);
}

function render_menu($config) {
	$langs = $config->languages;
	$ret = '';
	global $lang;
	foreach ($langs as $key) {
		$ret .= '<li';
		$langValue = get_string($key, $lang);
		if ($key == $lang) {
			$ret .= sprintf(' class="active">%s', $langValue);
		} else {
			if ($key === 'eng') {
				$key = 'index';
			}
			$ret .= sprintf('><a href="/'.$key.'.html">%s</a>', $langValue);
		}
		$ret .= '</li>';
	}
	echo $ret;
}

function render_text($lang) {
	global $lang;
	include('text_' . $lang . '.html');
}

include('index_page_template.php');

?>
