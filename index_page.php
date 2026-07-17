<?php
/**
 * Dynamic entry point: renders the home page on the fly
 * (?lang=ukr to preview Ukrainian). The live site serves the
 * pre-built index.html / ukr.html — see build.php and cron.sh.
 *
 * CLI back-compat: `php index_page.php ukr` still prints the page.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/strings.php';
require_once __DIR__ . '/lib/render.php';

$lang = $_GET['lang'] ?? ($argv[1] ?? 'eng');
if (!in_array($lang, B_LANGUAGES, true)) {
	$lang = 'eng';
}

echo render_page('home', $lang);
