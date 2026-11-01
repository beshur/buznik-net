<?php
/** Fetch editable WordPress case pages; replace the cache only after validation. */
if (PHP_SAPI !== 'cli') die('CLI only');
require __DIR__ . '/config.php';
require __DIR__ . '/lib/case-import.php';
if (is_file(__DIR__ . '/config_secrets.php')) require __DIR__ . '/config_secrets.php';
if (!B_AUTO_UPDATE) return;

function case_wp_get(string $path): array {
	$user = defined('B_WP_API_USER') ? B_WP_API_USER : getenv('B_WP_API_USER');
	$pass = defined('B_WP_API_PASS') ? B_WP_API_PASS : getenv('B_WP_API_PASS');
	if (!$user || !$pass) throw new RuntimeException('WordPress API credentials are missing');
	$context = stream_context_create(['http' => [
		'header' => 'Authorization: Basic ' . base64_encode($user . ':' . $pass),
		'ignore_errors' => true,
		'timeout' => 30,
	]]);
	$body = @file_get_contents(B_WP_API_BASE . '/wp-json/wp/v2/' . $path, false, $context);
	$headers = function_exists('http_get_last_response_headers') ? http_get_last_response_headers() : ($http_response_header ?? []);
	$status = $headers[0] ?? '';
	preg_match('/\s(\d{3})\s/', $status, $matches);
	$code = (int)($matches[1] ?? 0);
	if ($body === false || $code !== 200) {
		throw new RuntimeException('WordPress case fetch failed: ' . ($status ?: 'network unavailable'), $code);
	}
	return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
}

try {
	$index = case_wp_get('pages/' . B_CASE_INDEX_ID . '?context=edit');
	$pages = [];
	$endpoint = 'case-studies';
	for ($page = 1; ; $page++) {
		$args = [
			'context' => 'edit', 'status' => 'private,publish',
			'per_page' => 100, 'page' => $page, 'orderby' => 'menu_order', 'order' => 'asc',
		];
		if ($endpoint === 'pages') $args['parent'] = B_CASE_INDEX_ID;
		try {
			$batch = case_wp_get($endpoint . '?' . http_build_query($args));
		} catch (RuntimeException $error) {
			// Existing private Pages remain usable until the new plugin is activated.
			if ($endpoint !== 'case-studies' || $page !== 1 || $error->getCode() !== 404) throw $error;
			$endpoint = 'pages';
			$args['parent'] = B_CASE_INDEX_ID;
			$batch = case_wp_get($endpoint . '?' . http_build_query($args));
		}
		$pages = array_merge($pages, $batch);
		if (count($batch) < 100) break;
	}
	// Translated index pages, linked by Polylang (empty until the plugin exposes `translations`).
	$index_translations = [];
	foreach ($index['translations'] ?? [] as $pll => $id) {
		if ((int)$id !== (int)$index['id'] && in_array($pll, B_PLL_LANGUAGES, true)) {
			$index_translations[] = case_wp_get('pages/' . (int)$id . '?context=edit');
		}
	}
	$content = case_content_from_wordpress($index, $pages, $index_translations, B_PLL_LANGUAGES);
	save_case_content($content, __DIR__ . '/data/cases.json');
	$counts = [];
	foreach (array_slice(B_PLL_LANGUAGES, 1) as $lang => $pll) {
		$cases = count(array_filter($content['cases'], function ($case) use ($lang) { return isset($case['translations'][$lang]); }));
		$counts[] = sprintf('%s %d%s', $lang, $cases, isset($content['index']['translations'][$lang]) ? ' + index' : '');
	}
	printf("OK: fetched case index and %d cases from WordPress; translations: %s\n", count($content['cases']), implode(', ', $counts));
} catch (Throwable $error) {
	fwrite(STDERR, 'CASE FETCH FAILED: ' . $error->getMessage() . "\n");
	exit(1);
}
