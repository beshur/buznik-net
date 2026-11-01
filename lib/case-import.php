<?php
/** Index title, introduction and company heading from a WordPress page record. */
function case_index_from_wordpress(array $index, string $fallback_company = ''): array {
	$result = [
		'id' => (int)($index['id'] ?? 0),
		'title' => trim($index['title']['raw'] ?? strip_tags($index['title']['rendered'] ?? '')),
		'company' => trim(strip_tags($index['acf']['company'] ?? $index['excerpt']['raw'] ?? $index['excerpt']['rendered'] ?? '')),
		'intro_html' => $index['content']['rendered'] ?? $index['content']['raw'] ?? '',
	];
	if ($result['company'] === '') $result['company'] = $fallback_company;
	if ($result['id'] === 0 || $result['title'] === '' || trim($result['intro_html']) === '') {
		throw new RuntimeException('Case index title and introduction cannot be empty');
	}
	return $result;
}

/** Title, story and the five required ACF fields of one buznik_case record. */
function case_from_acf(array $page, string $label): array {
	$html = $page['content']['rendered'] ?? $page['content']['raw'] ?? '';
	$case = [
		'id' => (int)$page['id'],
		'title' => trim($page['title']['raw'] ?? strip_tags($page['title']['rendered'] ?? '')),
		'body_html' => $html,
	];
	foreach (['company', 'role', 'summary', 'outcome', 'scope'] as $field) {
		$value = $page['acf'][$field] ?? null;
		if (!is_string($value) || trim($value) === '') {
			throw new RuntimeException('Missing ACF field ' . $field . ' in ' . $label);
		}
		$case[$field] = trim($value);
	}
	if ($case['title'] === '' || trim(strip_tags($html)) === '') {
		throw new RuntimeException('Case title and story are required in ' . $label);
	}
	return $case;
}

/**
 * Site language of a record from the plugin's Polylang `lang` field.
 * No field (Polylang or the plugin's REST fields absent) means the source language.
 * Returns null for Polylang languages the site does not publish.
 */
function case_record_language(array $record, array $languages): ?string {
	if (!isset($record['lang']) || $record['lang'] === '') return array_key_first($languages);
	$site = array_search($record['lang'], $languages, true);
	return $site === false ? null : $site;
}

/**
 * Read ACF case records, with legacy pages supported during plugin rollout.
 *
 * $pages holds every language: Polylang does not filter REST queries. English
 * records define slugs, order and pages; a translation is attached to the English
 * case its Polylang `translations` map points to. $index_translations holds the
 * translated index pages. $languages maps site language => Polylang slug, source first.
 */
function case_content_from_wordpress(array $index, array $pages, array $index_translations = [], array $languages = ['eng' => 'en', 'ukr' => 'uk']): array {
	if (empty($index['id']) || empty($pages)) {
		throw new RuntimeException('Case index and at least one case are required');
	}
	$source = array_key_first($languages);
	$result = ['index' => case_index_from_wordpress($index), 'cases' => []];
	foreach ($index_translations as $translated) {
		$lang = case_record_language($translated, $languages);
		if ($lang === null || $lang === $source) continue;
		$result['index']['translations'][$lang] = case_index_from_wordpress($translated, $result['index']['company']);
	}
	usort($pages, function ($a, $b) {
		return [$a['menu_order'] ?? 0, $a['id']] <=> [$b['menu_order'] ?? 0, $b['id']];
	});
	$translations = [];
	foreach ($pages as $page) {
		$lang = case_record_language($page, $languages);
		if ($lang === null) continue;
		if ($lang !== $source) {
			$translations[] = [$lang, $page];
			continue;
		}
		$slug = $page['slug'] ?? '';
		$is_acf_case = ($page['type'] ?? 'page') === 'buznik_case';
		if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug)
			|| isset($result['cases'][$slug]) || (!$is_acf_case && (int)($page['parent'] ?? 0) !== (int)$index['id'])) {
			throw new RuntimeException('Invalid slug, duplicate, or parent for case ' . $slug);
		}
		$html = $page['content']['rendered'] ?? $page['content']['raw'] ?? '';
		if ($is_acf_case) {
			$result['cases'][$slug] = case_from_acf($page, $slug);
			continue;
		}
		$dom = new DOMDocument();
		$previous = libxml_use_internal_errors(true);
		try {
			$dom->loadHTML('<!doctype html><html><head><meta charset="utf-8"></head><body>' . $html . '</body></html>');
		} finally {
			libxml_clear_errors();
			libxml_use_internal_errors($previous);
		}
		$case = [
			'id' => (int)$page['id'],
			'company' => $result['index']['company'],
			'title' => trim($page['title']['raw'] ?? strip_tags($page['title']['rendered'] ?? '')),
			'body_html' => '',
		];
		$field = null;
		$fields = ['role', 'summary', 'outcome', 'scope'];
		foreach ($dom->getElementsByTagName('body')->item(0)->childNodes as $node) {
			if ($node->nodeType === XML_COMMENT_NODE) continue;
			if ($node->nodeName === 'h2') {
				$label = strtolower(trim($node->textContent));
				$field = in_array($label, $fields, true) ? $label : null;
				if ($field !== null) {
					if (isset($case[$field])) throw new RuntimeException('Duplicate ' . $field . ' in ' . $slug);
					$case[$field] = '';
					continue;
				}
			}
			if ($field !== null) {
				$case[$field] = trim($case[$field] . ' ' . trim($node->textContent));
			} else {
				$case['body_html'] .= $dom->saveHTML($node);
			}
		}
		foreach (array_merge(['title', 'body_html'], $fields) as $required) {
			if (trim($case[$required] ?? '') === '') {
				throw new RuntimeException('Missing ' . $required . ' in WordPress case ' . $slug);
			}
		}
		$result['cases'][$slug] = $case;
	}
	$slugs_by_id = array_flip(array_map(function ($case) { return $case['id']; }, $result['cases']));
	foreach ($translations as [$lang, $page]) {
		$source_id = (int)($page['translations'][$languages[$source]] ?? 0);
		$slug = $slugs_by_id[$source_id] ?? null;
		$label = $lang . ' translation ' . ($page['id'] ?? '?');
		if ($slug === null || ($page['type'] ?? '') !== 'buznik_case') {
			throw new RuntimeException('No ' . $source . ' case for ' . $label);
		}
		if (isset($result['cases'][$slug]['translations'][$lang])) {
			throw new RuntimeException('Duplicate ' . $lang . ' translation of ' . $slug);
		}
		$result['cases'][$slug]['translations'][$lang] = case_from_acf($page, $label . ' of ' . $slug);
	}
	return $result;
}

function save_case_content(array $content, string $file): void {
	$json = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
	$tmp = $file . '.tmp';
	if (file_put_contents($tmp, $json) === false || !rename($tmp, $file)) {
		throw new RuntimeException('Cannot write case content cache');
	}
}
