<?php
/** WordPress content cache. Refresh with php get_case_texts.php. */
function b_case_content(): array {
    static $content = null;
    if ($content === null) {
        $file = __DIR__ . '/../data/cases.json';
        $content = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        if (empty($content['cases']) || empty($content['index']['title'])) {
            throw new RuntimeException('Invalid case content cache');
        }
    }
    return $content;
}

function b_cases(): array {
    return b_case_content()['cases'];
}

function b_case_index(): array {
    return b_case_content()['index'];
}

/** Languages a case (or the index, with $slug null) is published in, source first. */
function b_case_languages(?string $slug): array {
    $record = $slug === null ? b_case_index() : b_cases()[$slug];
    return array_values(array_intersect(B_LANGUAGES, array_merge(['eng'], array_keys($record['translations'] ?? []))));
}

/** A case in $lang, or null when it has no translation. English is the source. */
function b_case_in(string $slug, string $lang): ?array {
    $case = b_cases()[$slug];
    if ($lang === 'eng') return $case;
    return $case['translations'][$lang] ?? null;
}

/** The index title, introduction and company heading in $lang, falling back to English. */
function b_case_index_in(string $lang): array {
    $index = b_case_index();
    return $lang === 'eng' ? $index : ($index['translations'][$lang] ?? $index);
}

/** Generated file name of a case page (or the index, with $slug null). */
function b_case_output(?string $slug, string $lang): string {
    return ($slug === null ? 'cases' : 'case-' . $slug) . B_PAGE_SUFFIX[$lang] . '.html';
}
