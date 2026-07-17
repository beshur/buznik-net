<?php
/**
 * Get and cache resume texts from the WordPress REST API
 * using cron tab.
 *
 * Each language maps to a page id (B_PAGE_IDS); the page's own
 * content is the source text. Pages are private, so requests are
 * authenticated with an application password defined in
 * config_secrets.php (B_WP_API_USER / B_WP_API_PASS) or taken
 * from same-named environment variables for local runs.
 */
if (php_sapi_name() != 'cli') {
	die('1337!');
}
require_once dirname(__FILE__) . '/config.php';
if (file_exists(dirname(__FILE__) . '/config_secrets.php')) {
	require_once dirname(__FILE__) . '/config_secrets.php';
}

if (B_AUTO_UPDATE == false) {
	return;
}

function fail($message) {
	fwrite(STDERR, $message . "\n");
	exit(1);
}

$api_user = defined('B_WP_API_USER') ? B_WP_API_USER : getenv('B_WP_API_USER');
$api_pass = defined('B_WP_API_PASS') ? B_WP_API_PASS : getenv('B_WP_API_PASS');
if (!$api_user || !$api_pass) {
	fail('ERROR: B_WP_API_USER / B_WP_API_PASS not set (config_secrets.php or env)');
}

function fetch_page_content($id, $user, $pass) {
	$url = B_WP_API_BASE . '/wp-json/wp/v2/pages/' . $id . '?context=edit';
	$context = stream_context_create(['http' => [
		'header' => 'Authorization: Basic ' . base64_encode($user . ':' . $pass),
		'ignore_errors' => true,
		'timeout' => 30,
	]]);
	$body = file_get_contents($url, false, $context);
	$page = json_decode($body, true);
	if (!is_array($page) || empty($page['content'])) {
		$err = is_array($page) && isset($page['message']) ? $page['message'] : 'no response';
		fail('ERROR: fetching page ' . $id . ': ' . $err);
	}
	// prefer raw (matches how the texts were stored before), fall back to rendered
	$content = !empty($page['content']['raw']) ? $page['content']['raw'] : $page['content']['rendered'];
	return $content;
}

$success = true;
foreach (B_LANGUAGES as $key) {
	if (empty(B_PAGE_IDS[$key])) {
		fail('ERROR: no page id configured for ' . $key);
	}
	$filename = 'text_' . $key . '.html';
	error_log('GET_TEXTS: ' . $key . ' from page ' . B_PAGE_IDS[$key]);
	$text = fetch_page_content(B_PAGE_IDS[$key], $api_user, $api_pass);

	if (!empty($text)) {
		file_put_contents(dirname(__FILE__) . '/' . $filename, $text);
	} else {
		$success = false;
		fail('ERROR: empty content for ' . $filename);
	}
}
echo ($success) ? 'ALL OK' : 'Unexpected error';
