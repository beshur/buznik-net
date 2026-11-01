<?php
/**
 * Build the static pages.
 *
 *   php build.php                  build every page in every language
 *   php build.php home ukr         build one page in one language
 *   php build.php --print-outputs  list the generated file names (no build)
 *
 * Every PHP warning/notice aborts the build (exit 1), so broken
 * markup or undefined variables can never reach the generated files.
 * Files are written atomically (tmp + rename).
 */
if (php_sapi_name() !== 'cli') {
	die('1337!');
}

error_reporting(E_ALL);
set_error_handler(function ($severity, $message, $file, $line) {
	throw new ErrorException($message, 0, $severity, $file, $line);
});

require __DIR__ . '/config.php';
require __DIR__ . '/lib/strings.php';
require __DIR__ . '/lib/render.php';

if (($argv[1] ?? null) === '--print-outputs') {
	foreach (b_pages() as $page) {
		foreach (array_keys($page['output']) as $lang) {
			echo $page['output'][$lang] . "\n";
		}
	}
	exit(0);
}

$only_page = $argv[1] ?? null;
$only_lang = $argv[2] ?? null;

try {
	$built = 0;
	foreach (b_pages() as $page_id => $page) {
		if ($only_page !== null && $page_id !== $only_page) {
			continue;
		}
		foreach (array_keys($page['output']) as $lang) {
			if ($only_lang !== null && $lang !== $only_lang) {
				continue;
			}
			$html = render_page($page_id, $lang);
			if (strpos($html, '</html>') === false) {
				throw new RuntimeException("Truncated output for $page_id/$lang");
			}
			$out = B_ROOT . '/' . $page['output'][$lang];
			$tmp = $out . '.tmp';
			if (file_put_contents($tmp, $html) === false || !rename($tmp, $out)) {
				throw new RuntimeException("Cannot write $out");
			}
			printf("OK %s/%s -> %s (%d bytes)\n", $page_id, $lang, $page['output'][$lang], strlen($html));
			$built++;
		}
	}
	if ($built === 0) {
		throw new RuntimeException('Nothing matched ' . $only_page . '/' . $only_lang);
	}
} catch (Throwable $err) {
	fwrite(STDERR, sprintf("BUILD FAILED: %s @ %s:%d\n", $err->getMessage(), $err->getFile(), $err->getLine()));
	exit(1);
}
