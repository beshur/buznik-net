<?php
/**
 * Minimal template rendering.
 *
 * A template is a plain PHP file in templates/ that receives its
 * variables as locals and echoes HTML. A page is an entry in
 * pages.php: content template + output file per language.
 * Rendering a page wraps its content template in layout.php.
 */

function e(?string $value): string {
	return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/** The page registry (pages.php), loaded once. */
function b_pages(): array {
	static $pages = null;
	if ($pages === null) {
		$pages = require B_ROOT . '/pages.php';
	}
	return $pages;
}

/** Public path of a page in a given language — used by the language menu. */
function page_path(string $page_id, string $lang): string {
	$page = b_pages()[$page_id] ?? null;
	if (!isset($page['output'][$lang])) {
		throw new RuntimeException("No output configured for page '$page_id' / lang '$lang'");
	}
	return '/' . $page['output'][$lang];
}

function render_template(string $name, array $vars = []): string {
	$file = B_ROOT . '/templates/' . $name;
	if (!is_file($file)) {
		throw new RuntimeException("Template not found: $file");
	}
	extract($vars, EXTR_SKIP);
	ob_start();
	try {
		include $file;
	} catch (Throwable $err) {
		ob_end_clean();
		throw $err;
	}
	return ob_get_clean();
}

function render_page(string $page_id, string $lang): string {
	$page = b_pages()[$page_id] ?? null;
	if ($page === null) {
		throw new RuntimeException("Unknown page: '$page_id'");
	}
	if (!in_array($lang, B_LANGUAGES, true)) {
		throw new RuntimeException("Unknown language: '$lang'");
	}
	$vars = ['lang' => $lang, 'page_id' => $page_id];
	return render_template('layout.php', $vars + [
		'title' => t($page['title_key'], $lang),
		'content' => render_template($page['template'], $vars),
	]);
}
