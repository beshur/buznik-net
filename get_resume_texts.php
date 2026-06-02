<?php
/**
 * Get and cache resume texts, then write text_<lang>.html (which index_page.php
 * includes). Run from cron.sh.
 *
 * Server:  reads the eng/ukr post meta straight from the WordPress DB.
 * Local:   no WordPress/MySQL present, so it falls back to the WP REST API using
 *          the Application Password loaded from .env by config.php. Same output
 *          either way, so the PHP templating runs identically on both.
 */
if (php_sapi_name() != 'cli') {
	die('1337!');
}
require_once dirname(__FILE__) . '/config.php';

/**
 * Return [lang => html] for B_LANGUAGES — from the local WP DB if available,
 * otherwise from the REST API.
 */
function b_fetch_texts() {
	$langs  = B_LANGUAGES;
	$wpload = dirname(__FILE__) . '/../j/wp-load.php';
	$texts  = [];

	if (is_file($wpload)) {
		// --- Server: read straight from the WordPress DB (DB layer only) ---
		define('SHORTINIT', true);
		require_once $wpload;
		global $wpdb;
		$rows = $wpdb->get_results(
			"SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id='" . B_PAGE_ID . "'"
		);
		foreach ($rows as $row) {
			if (in_array($row->meta_key, $langs, true)) {
				$texts[$row->meta_key] = $row->meta_value;
			}
		}
		return $texts;
	}

	// --- Local: fall back to the WP REST API ---
	if (!defined('B_API_NAME') || !defined('B_API_KEY')) {
		exit("ERROR: no local WordPress (../j/wp-load.php) and no B_API_NAME/B_API_KEY in .env for the REST fallback\n");
	}
	$base = defined('B_REST_BASE') ? B_REST_BASE : 'https://buznik.net/j';
	$url  = rtrim($base, '/') . '/wp-json/wp/v2/pages/' . B_PAGE_ID . '?context=edit';
	$auth = base64_encode(B_API_NAME . ':' . B_API_KEY);

	// Shell out to the system curl: local PHP (MacPorts php85) ships without
	// ext-openssl/curl, so it can't make the HTTPS call itself.
	$cmd  = 'curl -fsS -H ' . escapeshellarg('Authorization: Basic ' . $auth)
		. ' ' . escapeshellarg($url);
	$body = shell_exec($cmd);
	if ($body === null || trim($body) === '') {
		exit("ERROR: curl fetch failed for $url (is `curl` on PATH and are the .env creds valid?)\n");
	}
	$data = json_decode($body, true);
	if (!is_array($data) || !isset($data['meta'])) {
		exit("ERROR: unexpected REST response from $url: " . substr($body, 0, 200) . "\n");
	}
	$meta = $data['meta'];
	foreach ($langs as $lang) {
		if (!empty($meta[$lang])) {
			$texts[$lang] = $meta[$lang];
		}
	}
	return $texts;
}

if (B_AUTO_UPDATE == false) {
	return;
}

$texts = b_fetch_texts();

foreach (B_LANGUAGES as $key) {
	$filename = 'text_' . $key . '.html';
	if (!empty($texts[$key])) {
		file_put_contents(dirname(__FILE__) . '/' . $filename, $texts[$key]);
	} else {
		exit('ERROR: trying to download ' . $filename);
	}
}
echo 'ALL OK';
