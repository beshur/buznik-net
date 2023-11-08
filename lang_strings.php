<?php
/**
 * Language strings
 * Key => array(Russian version, Ukrainian version, English version)
 */
if (php_sapi_name() !== "cli" && !defined('ABSPATH')) {
	die('1337!');
}

$lang_strings = array(
	'ukr' => array('Укр', 'Укр', 'Укр'),
	'eng' => array('Eng', 'Eng', 'Eng'),
	'name' => array(
		'Олександр Бузник',
		'Alex Buznik'
	),
	'work' => array(
		'Робоче',
		'Work stuff'
	),
	'connect' => array(
		'Зв`язатися',
		'Contact'
	),
	'cv' => array(
		'Резюме',
		'CV'
	),
	'location' => array(
		'Знаходжусь',
		'Location'
	)
);

function get_string($key, $lang) {
	global $lang_strings;
	$pos = 0;
	switch ($lang) {
		case 'ukr':
			$pos = 0;
			break;
		case 'eng':
			$pos = 1;
			break;
		default:
			break;
	}
	return $lang_strings[$key][$pos];
};

function echo_string($key, $lang) {
	echo get_string($key, $lang);
};
