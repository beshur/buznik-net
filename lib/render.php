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

/** Decorative navigation icon; the adjacent link text supplies its name. */
function arrow_icon(string $direction = 'right'): string {
	if (!in_array($direction, ['right', 'left', 'up-right'], true)) {
		throw new InvalidArgumentException('Unknown arrow direction');
	}
	return '<svg class="case-arrow case-arrow--' . $direction . '" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M4 12h16m-6-6 6 6-6 6"/></svg>';
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

/** Relative link to a page in $lang, or to its English version when untranslated. */
function page_href(string $page_id, string $lang): string {
	$page = b_pages()[$page_id] ?? null;
	if ($page === null) {
		throw new RuntimeException("Unknown page: '$page_id'");
	}
	return ltrim(page_path($page_id, isset($page['output'][$lang]) ? $lang : 'eng'), '/');
}

/** Whether a page has its own version in $lang. */
function page_has(string $page_id, string $lang): bool {
	return isset(b_pages()[$page_id]['output'][$lang]);
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
	$title = $page['title'] ?? t($page['title_key'], $lang);
	return render_template('layout.php', $vars + [
		'title' => is_array($title) ? $title[$lang] : $title,
		'content' => render_template($page['template'], $vars),
	]);
}

/** Menu label for the case index: marks it as English-only where it is untranslated. */
function cases_label(string $lang): string {
	return t(page_has('cases', $lang) ? 'cases' : 'cases_in_english', $lang);
}
