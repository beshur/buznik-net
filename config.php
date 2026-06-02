<?php

define("B_PAGE_ID", 2562);
define("B_AUTO_UPDATE", true);
define("B_LANGUAGES", ["eng", "ukr"]);

// Local development only. When there's no local WordPress/MySQL, get_resume_texts.php
// falls back to the WP REST API and needs an Application Password. Load it from .env
// (gitignored, never deployed). On the server there's no .env, so this is a no-op and
// the direct DB read is used instead.
$b_env = dirname(__FILE__) . '/.env';
if (is_file($b_env)) {
	foreach (file($b_env, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
		$line = trim($line);
		if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
			continue;
		}
		list($k, $v) = explode('=', $line, 2);
		$k = trim($k);
		$v = trim(trim($v), "\"'");
		if ($k === 'B_API_NAME'  && !defined('B_API_NAME'))  define('B_API_NAME', $v);
		if ($k === 'B_API_KEY'   && !defined('B_API_KEY'))   define('B_API_KEY', $v);
		if ($k === 'B_REST_BASE' && !defined('B_REST_BASE')) define('B_REST_BASE', $v);
	}
}
