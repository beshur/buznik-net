<?php
$cases = b_cases();
$index = b_case_index();
$companies = [];
foreach ($cases as $slug => $case) {
    $company = $case['company'] ?? $index['company'];
    $companies[$company][$slug] = $case;
}
uksort($companies, 'strnatcasecmp');
?>
    <main class="case-main" id="main">
      <header class="case-header">
        <p class="case-eyebrow">Alex Buznik / Selected work</p>
        <h1><?= e($index['title']) ?></h1>
        <div class="case-intro"><?= $index['intro_html'] ?></div>
      </header>
<?php $group = 0; foreach ($companies as $company => $companyCases): $group++; ?>
      <section class="case-company" aria-labelledby="case-company-<?= $group ?>">
        <h2 id="case-company-<?= $group ?>"><?= e($company) ?></h2>
      <ol class="case-list">
<?php $number = 0; foreach ($companyCases as $slug => $case): $number++; ?>
        <li>
          <span class="case-number" aria-hidden="true"><?= e(sprintf('%02d', $number)) ?></span>
          <div>
            <p class="case-eyebrow"><?= e($case['role']) ?></p>
            <h3><a href="<?= e(ltrim(page_path('case-' . $slug, 'eng'), '/')) ?>"><?= e($case['title']) ?> <?= arrow_icon('up-right') ?></a></h3>
            <p><?= e($case['summary']) ?></p>
            <p class="case-outcome"><?= e($case['outcome']) ?></p>
          </div>
        </li>
<?php endforeach; ?>
      </ol>
      </section>
<?php endforeach; ?>
      <footer class="case-footer"><a href="index.html">About me</a> · <a href="mailto:shu@buznik.net">Get in touch</a></footer>
    </main>
