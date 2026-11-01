<?php
/** Case index. Untranslated cases are listed in English and link to the English page. */
$index = b_case_index_in($lang);
$companies = [];
foreach (array_keys(b_cases()) as $slug) {
    $case = b_case_in($slug, $lang) ?? b_case_in($slug, 'eng');
    $companies[$case['company'] ?? $index['company']][$slug] = $case;
}
uksort($companies, 'strnatcasecmp');
?>
    <main class="case-main" id="main">
      <header class="case-header">
        <p class="case-eyebrow"><?= e(t('name', $lang)) ?> / <?= e(t('selected_work', $lang)) ?></p>
        <h1><?= e($index['title']) ?></h1>
        <div class="case-intro"><?= $index['intro_html'] ?></div>
      </header>
<?php $group = 0; foreach ($companies as $company => $companyCases): $group++; ?>
      <section class="case-company" aria-labelledby="case-company-<?= $group ?>">
        <h2 id="case-company-<?= $group ?>"><?= e($company) ?></h2>
      <ol class="case-list">
<?php $number = 0; foreach ($companyCases as $slug => $case): $number++; $translated = page_has('case-' . $slug, $lang); ?>
        <li<?= $translated ? '' : ' lang="en"' ?>>
          <span class="case-number" aria-hidden="true"><?= e(sprintf('%02d', $number)) ?></span>
          <div>
            <p class="case-eyebrow"><?= e($case['role']) ?><?= $translated ? '' : ' · <span lang="' . e($lang === 'ukr' ? 'uk' : 'en') . '">' . e(t('in_english', $lang)) . '</span>' ?></p>
            <h3><a href="<?= e(page_href('case-' . $slug, $lang)) ?>"><?= e($case['title']) ?> <?= arrow_icon('up-right') ?></a></h3>
            <p><?= e($case['summary']) ?></p>
            <p class="case-outcome"><?= e($case['outcome']) ?></p>
          </div>
        </li>
<?php endforeach; ?>
      </ol>
      </section>
<?php endforeach; ?>
      <footer class="case-footer"><a href="<?= e(page_href('home', $lang)) ?>"><?= e(t('about_me', $lang)) ?></a> · <a href="mailto:shu@buznik.net"><?= e(t('get_in_touch', $lang)) ?></a></footer>
    </main>
