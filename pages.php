<?php
/**
 * Page registry: everything build.php renders.
 *
 * Each page:
 *   'template'  — content template in templates/
 *   'title_key' — string key (lib/strings.php) for the <title>, or 'title' for a
 *                 literal: one string, or one per language
 *   'output'    — generated file name per supported language
 *
 * To add a page: create templates/<name>.php and add an entry here —
 * build.php, cron.sh and the language menu pick it up automatically.
 * Case pages exist only in languages that have a WordPress translation.
 */

require_once __DIR__ . '/lib/cases.php';

$pages = [
	'home' => [
		'template' => 'home.php',
		'title_key' => 'name',
		'output' => [
			'eng' => 'index.html',
			'ukr' => 'ukr.html',
		],
	],
];

foreach (array_merge([null], array_keys(b_cases())) as $slug) {
	$page = [
		'template' => $slug === null ? 'cases.php' : 'case.php',
		'title' => [],
		'output' => [],
	];
	foreach (b_case_languages($slug) as $lang) {
		$text = $slug === null ? b_case_index_in($lang) : b_case_in($slug, $lang);
		$page['title'][$lang] = $text['title'] . ' — ' . t('name', $lang);
		$page['output'][$lang] = b_case_output($slug, $lang);
	}
	$pages[$slug === null ? 'cases' : 'case-' . $slug] = $page;
}

return $pages;
