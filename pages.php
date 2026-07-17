<?php
/**
 * Page registry: everything build.php renders.
 *
 * Each page:
 *   'template'  — content template in templates/
 *   'title_key' — string key (lib/strings.php) for the <title>
 *   'output'    — generated file name per language
 *
 * To add a page: create templates/<name>.php and add an entry here —
 * build.php, cron.sh and the language menu pick it up automatically.
 */

return [
	'home' => [
		'template' => 'home.php',
		'title_key' => 'name',
		'output' => [
			'eng' => 'index.html',
			'ukr' => 'ukr.html',
		],
	],
];
