<?php
/**
 * Buznik.net start
 */
/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

require_once ABSPATH . '/config.php';
require_once ABSPATH . '/lang_strings.php';

$lang = 'rus';
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
			$ret .= sprintf('><a href="/'.$key.'">%s</a>', $langValue);
		}
		$ret .= '</li>';
	}
	echo $ret;
}

function render_text($lang) {
	global $lang;
	include('text_' . $lang . '.html');
}

?><!DOCTYPE html>
<html>
<head>
	<title><?php render_string('name'); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<style type="text/css"><?php include('dist/css/main.css'); ?></style>
	<link href="favicon.ico?v=1" rel="shortcut icon" />
</head>

<body>
	<div id="container">
		<div class="topbar">
			<div class="topbar-location" title="<?php render_string('location'); ?>">
				<?php echo get_string_from_obj($config->currentLocation); ?>
			</div>
			<div class="topbar-lang">
				<ul class="list"><?php render_menu($config); ?></ul>
			</div>
		</div>
		<section class="resume">
			<header><h1>
				<span class="shu icon-shu"><?php include('dist/img/shu.svg');?></span>
				<?php render_string('name'); ?>
			</h2></header>
		
			<div class="resume-main">
				<?php render_text($lang); ?>
			</div><!--

		 --><div class="links">
				<div><?php render_string('can'); ?>:</div>
				<ul class="list">
					<li><a href="https://github.com/beshur" target="_blank">github.com/beshur</a></li>
					<li><a href="https://drive.google.com/file/d/1PsF0JMA2wo76udGsQdc0dnFSl4_BMBo1/view?usp=sharing" target="_blank"><?php render_string('cv'); ?></a></li>
				</ul>
				<div><?php render_string('connect'); ?>:</div>
				<ul class="list">
					<li>Skype: <a href="skype:alexbuznik">alexbuznik</a></li>
					<li>E-mail: <a href="mailto:shu@buznik.net">shu@buznik.net</a></li>
					<li><a href="https://twitter.com/beshur" target="_blank">twitter.com/beshur</a></li>
				</ul>
			</div>
		</section>
	</div>
</body>
</html>