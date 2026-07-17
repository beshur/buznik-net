<?php

// page whose own content is the source text, per language
define("B_PAGE_IDS", ["eng" => 2562, "ukr" => 2760]);
// legacy single-page id (WP-side save hook still references it)
define("B_PAGE_ID", 2562);
define("B_AUTO_UPDATE", true);
define("B_LANGUAGES", ["eng", "ukr"]);
// WordPress REST API root (application password creds live in config_secrets.php)
define("B_WP_API_BASE", "https://buznik.net/j");
