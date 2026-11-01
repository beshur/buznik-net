<?php
/** Run with php scripts/check-case-import.php. No network or WordPress writes. */
require __DIR__ . '/../lib/case-import.php';

function check(bool $condition, string $message): void {
	if (!$condition) throw new RuntimeException($message);
}
function rejects(callable $operation, string $message): void {
	try { $operation(); } catch (RuntimeException $error) { return; }
	throw new RuntimeException($message);
}

$index = ['id' => 10, 'title' => ['raw' => 'Case studies'],
	'excerpt' => ['raw' => 'Stark Future'], 'content' => ['rendered' => '<p>Introduction</p>']];
$page = ['id' => 11, 'parent' => 10, 'slug' => 'registration', 'menu_order' => 2,
	'title' => ['raw' => 'Vehicle registration'], 'content' => ['rendered' =>
	'<h2>Role</h2><p>Full-stack &amp; product</p><h2>Summary</h2><p>Original summary</p>' .
	'<h2>Outcome</h2><p>Shipped</p><h2>Scope</h2><p>Frontend and backend</p>' .
	'<h2>The result</h2><p>Customers can <strong>sign documents</strong>.</p><ul><li>Online submission</li></ul>']];

$content = case_content_from_wordpress($index, [$page]);
$case = $content['cases']['registration'];
check($case['role'] === 'Full-stack & product', 'Metadata must decode entities');
check(strpos($case['body_html'], '<strong>sign documents</strong>') !== false, 'Keep editor formatting');
check(strpos($case['body_html'], '<ul>') !== false, 'Keep editor lists');
check(strpos($case['body_html'], '<h2>Role</h2>') === false, 'Metadata must not appear in the story');

$edited = $page;
$edited['title']['raw'] = 'Updated title';
$edited['content']['rendered'] = str_replace('Original summary', 'Edited in WordPress', $page['content']['rendered']);
$updated = case_content_from_wordpress($index, [$edited]);
check($updated['cases']['registration']['summary'] === 'Edited in WordPress', 'Admin summary edits must reach index data');
check($updated['cases']['registration']['title'] === 'Updated title', 'Admin title edits must reach detail data');

$acf = $page;
$acf['type'] = 'buznik_case';
$acf['parent'] = 0;
$acf['content']['rendered'] = '<h2>The result</h2><p>Customers can <strong>sign documents</strong>.</p>';
$acf['acf'] = ['company' => 'Stark Future', 'role' => 'Full-stack', 'summary' => 'Edited ACF summary', 'outcome' => 'Shipped', 'scope' => 'Frontend and backend'];
$acfContent = case_content_from_wordpress($index, [$acf]);
check($acfContent['cases']['registration']['summary'] === 'Edited ACF summary', 'Read structured ACF fields without heading parsing');
check(strpos($acfContent['cases']['registration']['body_html'], '<strong>') !== false, 'Keep ACF case story formatting');
$invalidAcf = $acf; unset($invalidAcf['acf']['scope']);
rejects(function () use ($index, $invalidAcf) { case_content_from_wordpress($index, [$invalidAcf]); }, 'Reject missing ACF fields');
$invalidAcf = $acf; $invalidAcf['acf']['summary'] = ['unexpected'];
rejects(function () use ($index, $invalidAcf) { case_content_from_wordpress($index, [$invalidAcf]); }, 'Reject non-text ACF values');

$second = $page;
$second['id'] = 12; $second['slug'] = 'earlier-case'; $second['menu_order'] = 1;
$ordered = case_content_from_wordpress($index, [$page, $second]);
check(array_keys($ordered['cases']) === ['earlier-case', 'registration'], 'Page Order must control navigation');

$invalid = $page; $invalid['slug'] = '../index';
rejects(function () use ($index, $invalid) { case_content_from_wordpress($index, [$invalid]); }, 'Reject unsafe filenames');
$invalid = $page; $invalid['parent'] = 99;
rejects(function () use ($index, $invalid) { case_content_from_wordpress($index, [$invalid]); }, 'Reject pages outside the case parent');
$invalid = $page; $invalid['content']['rendered'] = str_replace('<h2>Scope</h2><p>Frontend and backend</p>', '', $page['content']['rendered']);
rejects(function () use ($index, $invalid) { case_content_from_wordpress($index, [$invalid]); }, 'Reject incomplete editor fields');
rejects(function () use ($index, $page) { case_content_from_wordpress($index, [$page, $page]); }, 'Reject duplicate URLs');
rejects(function () use ($index) { case_content_from_wordpress($index, []); }, 'Reject empty fetches');

echo "PASS: editor updates, metadata, rich text, ordering and malformed input\n";
