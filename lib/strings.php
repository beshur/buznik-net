<?php
/**
 * UI strings, keyed by string id => [lang code => value].
 *
 * To add a language: add its code to B_LANGUAGES (config.php),
 * a label to B_LANG_LABELS, and a value for every key below.
 * t() falls back to English rather than emitting a notice.
 */

// how each language names itself in the language switcher
const B_LANG_LABELS = [
	'eng' => 'Eng',
	'ukr' => 'Укр',
];

const B_STRINGS = [
	'name' => [
		'eng' => 'Alex Buznik',
		'ukr' => 'Олександр Бузник',
	],
	'work' => [
		'eng' => 'Work stuff',
		'ukr' => 'Робоче',
	],
	'connect' => [
		'eng' => 'Contact',
		'ukr' => 'Зв`язатися',
	],
	'cv' => [
		'eng' => 'CV',
		'ukr' => 'Резюме',
	],
	'location' => [
		'eng' => 'Location',
		'ukr' => 'Знаходжусь',
	],
];

function t(string $key, string $lang): string {
	if (!isset(B_STRINGS[$key])) {
		throw new RuntimeException("Unknown string key: '$key'");
	}
	return B_STRINGS[$key][$lang] ?? B_STRINGS[$key]['eng'];
}
