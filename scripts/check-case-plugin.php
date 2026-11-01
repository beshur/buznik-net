<?php
/** Plugin contract checks with a small WordPress adapter. No network or database. */
define('ABSPATH', '/tmp/buznik-plugin-check/www/j/');
define('ACF_VERSION', '6.2.2');
$GLOBALS['plugin_checks'] = ['hooks' => [], 'groups' => [], 'posts' => [], 'meta' => [], 'fields' => [], 'options' => [], 'updates' => 0];
class WP_Error {
	private $message;
	function __construct($code, $message) { $this->message = $message; }
	function get_error_message() { return $this->message; }
}
function is_wp_error($v) { return $v instanceof WP_Error; }
function add_action($hook, $callback, ...$args) { $GLOBALS['plugin_checks']['hooks'][] = $callback; }
function add_filter($hook, $callback, ...$args) { $GLOBALS['plugin_checks']['hooks'][] = $callback; }
function register_activation_hook($file, $callback) { $GLOBALS['plugin_checks']['hooks'][] = $callback; }
function register_post_type($type, $args) { $GLOBALS['plugin_checks']['type'] = [$type, $args]; }
function acf_add_local_field_group($group) { $GLOBALS['plugin_checks']['groups'][$group['key']] = $group; }
function wp_die($message) { throw new RuntimeException($message); }
function wp_is_post_revision($id) { return false; }
function wp_is_post_autosave($id) { return false; }
function get_posts($args) {
	$posts = array_values(array_filter($GLOBALS['plugin_checks']['posts'], function ($post) use ($args) {
		return $post->post_type === $args['post_type'] && (!isset($args['post_parent']) || $post->post_parent === $args['post_parent']);
	}));
	return ($args['fields'] ?? '') === 'ids' ? array_map(function ($post) { return $post->ID; }, $posts) : $posts;
}
function get_option($key) { return $GLOBALS['plugin_checks']['options'][$key] ?? false; }
function register_rest_field($type, $field, $args) { $GLOBALS['plugin_checks']['rest'][$type][$field] = $args; }
function get_post($id) { return $GLOBALS['plugin_checks']['posts'][$id] ?? null; }
function add_post_meta($id, $key, $value, $unique) {
	if (!isset($GLOBALS['plugin_checks']['meta'][$id][$key])) $GLOBALS['plugin_checks']['meta'][$id][$key] = $value;
}
function update_post_meta($id, $key, $value) { $GLOBALS['plugin_checks']['meta'][$id][$key] = $value; }
function wp_slash($value) { return is_array($value) ? array_map('wp_slash', $value) : (is_string($value) ? addslashes($value) : $value); }
function wp_update_post($values, $error) {
	$id = $values['ID'];
	foreach ($values as $key => $value) $GLOBALS['plugin_checks']['posts'][$id]->$key = is_string($value) ? stripslashes($value) : $value;
	$GLOBALS['plugin_checks']['updates']++;
	\Buznik\CaseStudies\queue_rebuild($id, get_post($id));
	return $id;
}
function update_field($key, $value, $id) {
	$name = preg_replace('/^field_buznik_case_(?:index_)?/', '', $key);
	$GLOBALS['plugin_checks']['fields'][$id][$name] = $value;
}
function get_field($key, $id) { return $GLOBALS['plugin_checks']['fields'][$id][$key] ?? null; }
function update_option($key, $value, $autoload = null) { $GLOBALS['plugin_checks']['options'][$key] = $value; }
function apply_filters($name, $value) { return $value; }
function is_admin() { return true; }
function wp_unslash($value) { return stripslashes($value); }
function sanitize_text_field($value) { return strip_tags($value); }

require __DIR__ . '/../../buznik-net-remote/j/wp-content/plugins/buznik-case-studies/buznik-case-studies.php';
function ensure($value, $message) { if (!$value) throw new RuntimeException($message); }
foreach ($GLOBALS['plugin_checks']['hooks'] as $callback) ensure(is_callable($callback), 'Invalid WordPress hook callback: ' . $callback);
\Buznik\CaseStudies\register_type();
\Buznik\CaseStudies\register_fields();
ensure($GLOBALS['plugin_checks']['type'][1]['rest_base'] === 'case-studies', 'REST route contract');
ensure($GLOBALS['plugin_checks']['type'][1]['public'] === false, 'Source records stay private by default');
$group = $GLOBALS['plugin_checks']['groups']['group_buznik_case_details'];
ensure(count($group['fields']) === 5 && $group['show_in_rest'] === 1, 'Five editable ACF fields exposed to REST');

$story = "<h2>The result</h2><p>Customers' documents stay private.</p>";
$legacy = '<h2>Role</h2><p>Full-stack</p><h2>Summary</h2><p>Registration &amp; signing</p><h2>Outcome</h2><p>Shipped</p><h2>Scope</h2><p>Frontend and backend</p>' . $story;
$GLOBALS['plugin_checks']['posts'] = [
	2770 => (object)['ID' => 2770, 'post_type' => 'page', 'post_parent' => 0, 'post_excerpt' => 'Stark Future'],
	2771 => (object)['ID' => 2771, 'post_type' => 'page', 'post_parent' => 2770, 'post_status' => 'private', 'post_name' => 'vehicle-registration', 'post_content' => $legacy],
];
ensure(\Buznik\CaseStudies\migrate_legacy_pages() === true, 'Migration succeeds');
$post = get_post(2771);
ensure($post->post_type === 'buznik_case' && $post->post_parent === 0, 'Move source to custom type');
ensure($post->post_status === 'private' && $post->post_name === 'vehicle-registration', 'Preserve privacy and slug');
ensure($post->post_content === $story, 'Preserve story and apostrophes');
ensure($GLOBALS['plugin_checks']['meta'][2771]['_buznik_case_legacy_content'] === $legacy, 'Back up original source');
ensure(get_field('summary', 2771) === 'Registration & signing', 'Migrate fields with decoded entities');
ensure(empty($GLOBALS['buznik_cases_rebuild_pending']), 'Suppress intermediate migration rebuilds');
$post->post_content = '<p>Later editor change</p>';
\Buznik\CaseStudies\migrate_legacy_pages();
ensure($GLOBALS['plugin_checks']['updates'] === 1 && $post->post_content === '<p>Later editor change</p>', 'Reactivation must not overwrite editor changes');

$GLOBALS['plugin_checks']['posts'][2772] = (object)['ID' => 2772, 'post_type' => 'page', 'post_parent' => 2770, 'post_content' => '<p>Incomplete</p>'];
ensure(is_wp_error(\Buznik\CaseStudies\migrate_legacy_pages()), 'Incomplete legacy data must fail preflight');
ensure(get_post(2772)->post_type === 'page', 'Failed preflight leaves source unchanged');
\Buznik\CaseStudies\queue_rebuild(2771, $post);
\Buznik\CaseStudies\rebuild();
ensure(isset($GLOBALS['plugin_checks']['options']['buznik_cases_build_error']), 'Missing build integration is visible in admin');
class CompanyQuery {
	public $values = ['post_type' => 'buznik_case', 'meta_query' => ['relation' => 'OR', ['key' => 'existing', 'value' => 'one']]];
	function is_main_query() { return true; }
	function get($key) { return $this->values[$key] ?? null; }
	function set($key, $value) { $this->values[$key] = $value; }
}
$_GET['buznik_company'] = 'Stark Future';
$query = new CompanyQuery();
\Buznik\CaseStudies\filter_company_query($query);
ensure($query->values['meta_query']['relation'] === 'AND', 'Company filter must constrain existing query');
ensure($query->values['meta_query'][1]['value'] === 'Stark Future', 'Company filter uses selected company');
$other = new CompanyQuery(); $other->values['post_type'] = 'page';
\Buznik\CaseStudies\filter_company_query($other);
ensure($other->values['meta_query']['relation'] === 'OR', 'Company filter must not change other admin lists');
unset($_GET['buznik_company']);
// Polylang: functions are defined only now, as when Polylang loads after this plugin.
$GLOBALS['plugin_checks']['languages'] = [2771 => 'en'];
$GLOBALS['plugin_checks']['translations'] = [2770 => ['en' => 2770, 'uk' => 2780], 2780 => ['en' => 2770, 'uk' => 2780]];
function pll_default_language($field) { return 'en'; }
function pll_get_post_language($id, $field = 'slug') { return $GLOBALS['plugin_checks']['languages'][$id] ?? false; }
function pll_set_post_language($id, $lang) { $GLOBALS['plugin_checks']['languages'][$id] = $lang; }
function pll_get_post_translations($id) { return $GLOBALS['plugin_checks']['translations'][$id] ?? []; }
$types = \Buznik\CaseStudies\polylang_post_types(['post' => 'post', 'page' => 'page'], false);
ensure(($types['buznik_case'] ?? null) === 'buznik_case', 'Case studies are translatable');
ensure(!isset(\Buznik\CaseStudies\polylang_post_types(['buznik_case' => 'buznik_case'], true)['buznik_case']), 'Translatability is forced, not a settings checkbox');
\Buznik\CaseStudies\register_language_fields();
foreach (['buznik_case', 'page'] as $type) {
	ensure(isset($GLOBALS['plugin_checks']['rest'][$type]['lang'], $GLOBALS['plugin_checks']['rest'][$type]['translations']), "REST lang and translations on $type");
}
$rest = $GLOBALS['plugin_checks']['rest']['page'];
ensure($rest['lang']['get_callback'](['id' => 2771]) === 'en', 'REST lang is the Polylang slug');
ensure($rest['lang']['get_callback'](['id' => 9999]) === null, 'REST lang is null without a language');
ensure((array)$rest['translations']['get_callback'](['id' => 2770]) === ['en' => 2770, 'uk' => 2780], 'REST translations map language to post ID');
$GLOBALS['plugin_checks']['posts'][2773] = (object)['ID' => 2773, 'post_type' => 'buznik_case', 'post_parent' => 0];
\Buznik\CaseStudies\assign_default_language();
ensure(($GLOBALS['plugin_checks']['languages'][2773] ?? null) === 'en' && $GLOBALS['plugin_checks']['languages'][2770] === 'en', 'Existing cases and index get the default language');
$GLOBALS['plugin_checks']['languages'][2773] = 'uk';
\Buznik\CaseStudies\assign_default_language();
ensure($GLOBALS['plugin_checks']['languages'][2773] === 'uk', 'Language backfill runs once and never overrides a language');
unset($GLOBALS['buznik_cases_rebuild_pending']);
\Buznik\CaseStudies\queue_rebuild(2780, (object)['ID' => 2780, 'post_type' => 'page']);
ensure(!empty($GLOBALS['buznik_cases_rebuild_pending']), 'Saving a translated index page rebuilds the site');
unset($GLOBALS['buznik_cases_rebuild_pending']);
\Buznik\CaseStudies\queue_rebuild(2562, (object)['ID' => 2562, 'post_type' => 'page']);
ensure(empty($GLOBALS['buznik_cases_rebuild_pending']), 'Other pages do not trigger case rebuilds');
echo "PASS: plugin hooks, ACF schema, migration, reactivation, deployment notice and scoped company filtering, Polylang translations\n";
