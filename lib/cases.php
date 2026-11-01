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
