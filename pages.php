<?php
/**
 * Page registry: everything build.php renders.
 *
 * Each page:
 *   'template'  — content template in templates/
 *   'title_key' — string key (lib/strings.php) for the <title>, or 'title' for a literal
 *   'output'    — generated file name per supported language
 *
 * To add a page: create templates/<name>.php and add an entry here —
 * build.php, cron.sh and the language menu pick it up automatically.
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
	'cases' => [
		'template' => 'cases.php',
		'title' => b_case_index()['title'] . ' — Alex Buznik',
		'output' => ['eng' => 'cases.html'],
	],
];

foreach (b_cases() as $slug => $case) {
	$pages['case-' . $slug] = [
		'template' => 'case.php',
		'title' => $case['title'] . ' — Alex Buznik',
		'output' => ['eng' => 'case-' . $slug . '.html'],
	];
}

return $pages;
