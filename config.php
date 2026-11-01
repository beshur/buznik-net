<?php

// repo/deploy root — anchor for all includes and generated files
define("B_ROOT", __DIR__);
date_default_timezone_set('EET');

// page whose own content is the source text, per language
define("B_PAGE_IDS", ["eng" => 2562, "ukr" => 2760]);
// legacy single-page id (WP-side save hook still references it)
define("B_PAGE_ID", 2562);
// Private WordPress index page; case entries come from the ACF plugin's REST API.
define("B_CASE_INDEX_ID", 2770);
define("B_AUTO_UPDATE", true);
define("B_LANGUAGES", ["eng", "ukr"]);
// WordPress REST API root (application password creds live in config_secrets.php)
define("B_WP_API_BASE", "https://buznik.net/j");
// Site language => Polylang language slug. Polylang languages missing here (ru) are ignored.
define("B_PLL_LANGUAGES", ["eng" => "en", "ukr" => "uk"]);
// File-name suffix for translated case pages: cases-ukr.html, case-<slug>-ukr.html.
define("B_PAGE_SUFFIX", ["eng" => "", "ukr" => "-ukr"]);
