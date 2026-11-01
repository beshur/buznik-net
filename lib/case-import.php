<?php
/** Read ACF case records, with legacy pages supported during plugin rollout. */
function case_content_from_wordpress(array $index, array $pages): array {
	if (empty($index['id']) || empty($pages)) {
		throw new RuntimeException('Case index and at least one case are required');
	}
	$result = ['index' => [
		'id' => (int)$index['id'],
		'title' => trim($index['title']['raw'] ?? strip_tags($index['title']['rendered'] ?? '')),
		'company' => trim(strip_tags($index['acf']['company'] ?? $index['excerpt']['raw'] ?? $index['excerpt']['rendered'] ?? '')),
		'intro_html' => $index['content']['rendered'] ?? $index['content']['raw'] ?? '',
	], 'cases' => []];
	if ($result['index']['title'] === '' || trim($result['index']['intro_html']) === '') {
		throw new RuntimeException('Case index title and introduction cannot be empty');
	}
	usort($pages, function ($a, $b) {
		return [$a['menu_order'] ?? 0, $a['id']] <=> [$b['menu_order'] ?? 0, $b['id']];
	});
	foreach ($pages as $page) {
		$slug = $page['slug'] ?? '';
		$is_acf_case = ($page['type'] ?? 'page') === 'buznik_case';
		if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug)
			|| isset($result['cases'][$slug]) || (!$is_acf_case && (int)($page['parent'] ?? 0) !== (int)$index['id'])) {
			throw new RuntimeException('Invalid slug, duplicate, or parent for case ' . $slug);
		}
		$html = $page['content']['rendered'] ?? $page['content']['raw'] ?? '';
		if ($is_acf_case) {
			$case = [
				'id' => (int)$page['id'],
				'title' => trim($page['title']['raw'] ?? strip_tags($page['title']['rendered'] ?? '')),
				'body_html' => $html,
			];
			foreach (['company', 'role', 'summary', 'outcome', 'scope'] as $field) {
				$value = $page['acf'][$field] ?? null;
				if (!is_string($value) || trim($value) === '') {
					throw new RuntimeException('Missing ACF field ' . $field . ' in ' . $slug);
				}
				$case[$field] = trim($value);
			}
			if ($case['title'] === '' || trim(strip_tags($html)) === '') {
				throw new RuntimeException('Case title and story are required in ' . $slug);
			}
			$result['cases'][$slug] = $case;
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
	return $result;
}

function save_case_content(array $content, string $file): void {
	$json = json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
	$tmp = $file . '.tmp';
	if (file_put_contents($tmp, $json) === false || !rename($tmp, $file)) {
		throw new RuntimeException('Cannot write case content cache');
	}
}
