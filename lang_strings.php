<?php
/**
 * Language strings
 * Key => array(Russian version, Ukrainian version, English version)
 */
if (php_sapi_name() !== "cli" && !defined('ABSPATH')) {
	die('1337!');
}

$lang_strings = array(
	'rus' => array('Рус', 'Рус', 'Рус'),
	'ukr' => array('Укр', 'Укр', 'Укр'),
	'eng' => array('Eng', 'Eng', 'Eng'),
	'name' => array(
		'Александр Бузник',
		'Олександр Бузник',
		'Alex Buznik'
	),
	'can' => array(
		'Умею',
		'Вмію',
		'Can do'
	),
	'connect' => array(
		'Связаться',
		'Зв`язатися',
		'Contact'
	),
	'cv' => array(
		'Резюме в .PDF',
		'Резюме у .PDF',
		'CV in .PDF'
	),
	'location' => array(
		'Нахожусь',
		'Знаходжусь',
		'Location'
	)
);

function get_string($key, $lang) {
	global $lang_strings;
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
	return $lang_strings[$key][$pos];
};

function echo_string($key, $lang) {
	echo get_string($key, $lang);
};