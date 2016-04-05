<?php
/**
 * Language strings
 * Key => array(Russian version, Ukrainian version, English version)
 */
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

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
);

function get_string($key, $lang) {
	global $lang_strings;
	$pos = 0;
	if (isset($pos)) {
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
	} else {
		return false;
	}
};

function echo_string($key, $lang) {
	echo get_string($key, $lang);
};