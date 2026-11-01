<?php
/** Case detail, rendered only in languages the case is translated into. */
$slug = substr($page_id, strlen('case-'));
$case = b_case_in($slug, $lang);
$slugs = array_keys(b_cases());
$position = array_search($slug, $slugs, true);
/** Neighbour link: the translated page and title where they exist, English otherwise. */
$neighbour = function (string $other) use ($lang): array {
    $text = b_case_in($other, $lang) ?? b_case_in($other, 'eng');
    return [page_href('case-' . $other, $lang), $text['title'], page_has('case-' . $other, $lang)];
};
?>
    <main class="case-main" id="main">
      <nav class="case-back" aria-label="<?= e(t('breadcrumb', $lang)) ?>"><a href="<?= e(page_href('cases', $lang)) ?>"><?= arrow_icon('left') ?> <?= e(t('all_cases', $lang)) ?></a></nav>
      <article>
        <header class="case-header">
          <p class="case-eyebrow"><?= e($case['company'] ?? b_case_index_in($lang)['company']) ?> / <?= e($case['role']) ?></p>
          <h1><?= e($case['title']) ?></h1>
          <p class="case-intro"><?= e($case['summary']) ?></p>
          <dl class="case-facts">
            <div><dt><?= e(t('outcome', $lang)) ?></dt><dd><?= e($case['outcome']) ?></dd></div>
            <div><dt><?= e(t('scope', $lang)) ?></dt><dd><?= e($case['scope']) ?></dd></div>
          </dl>
        </header>
        <div class="case-story">
          <?= $case['body_html'] ?>
        </div>
      </article>
      <nav class="case-pagination" aria-label="<?= e(t('cases', $lang)) ?>">
<?php if ($position > 0): [$href, $title, $own] = $neighbour($slugs[$position - 1]); ?>
        <a href="<?= e($href) ?>"><span><?= arrow_icon('left') ?> <?= e(t('previous_case', $lang)) ?></span><?= $own ? e($title) : '<span lang="en">' . e($title) . '</span>' ?></a>
<?php endif; ?>
<?php if ($position < count($slugs) - 1): [$href, $title, $own] = $neighbour($slugs[$position + 1]); ?>
        <a class="case-next" href="<?= e($href) ?>"><span><?= e(t('next_case', $lang)) ?> <?= arrow_icon() ?></span><?= $own ? e($title) : '<span lang="en">' . e($title) . '</span>' ?></a>
<?php endif; ?>
      </nav>
      <footer class="case-footer"><a href="<?= e(page_href('cases', $lang)) ?>"><?= e(t('all_cases', $lang)) ?></a> · <a href="mailto:shu@buznik.net"><?= e(t('get_in_touch', $lang)) ?></a></footer>
    </main>
