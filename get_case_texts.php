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
	$content = case_content_from_wordpress($index, $pages);
	save_case_content($content, __DIR__ . '/data/cases.json');
	printf("OK: fetched case index and %d cases from WordPress\n", count($content['cases']));
} catch (Throwable $error) {
	fwrite(STDERR, 'CASE FETCH FAILED: ' . $error->getMessage() . "\n");
	exit(1);
}
