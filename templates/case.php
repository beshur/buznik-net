<?php
$cases = b_cases();
$slug = substr($page_id, strlen('case-'));
$case = $cases[$slug];
$slugs = array_keys($cases);
$position = array_search($slug, $slugs, true);
?>
    <main class="case-main" id="main">
      <nav class="case-back" aria-label="Breadcrumb"><a href="cases.html"><?= arrow_icon('left') ?> All case studies</a></nav>
      <article>
        <header class="case-header">
          <p class="case-eyebrow"><?= e($case['company'] ?? b_case_index()['company']) ?> / <?= e($case['role']) ?></p>
          <h1><?= e($case['title']) ?></h1>
          <p class="case-intro"><?= e($case['summary']) ?></p>
          <dl class="case-facts">
            <div><dt>Outcome</dt><dd><?= e($case['outcome']) ?></dd></div>
            <div><dt>My scope</dt><dd><?= e($case['scope']) ?></dd></div>
          </dl>
        </header>
        <div class="case-story">
          <?= $case['body_html'] ?>
        </div>
      </article>
      <nav class="case-pagination" aria-label="Case studies">
<?php if ($position > 0): $previous = $slugs[$position - 1]; ?>
        <a href="<?= e(ltrim(page_path('case-' . $previous, 'eng'), '/')) ?>"><span><?= arrow_icon('left') ?> Previous case</span><?= e($cases[$previous]['title']) ?></a>
<?php endif; ?>
<?php if ($position < count($slugs) - 1): $next = $slugs[$position + 1]; ?>
        <a class="case-next" href="<?= e(ltrim(page_path('case-' . $next, 'eng'), '/')) ?>"><span>Next case <?= arrow_icon() ?></span><?= e($cases[$next]['title']) ?></a>
<?php endif; ?>
      </nav>
      <footer class="case-footer"><a href="cases.html">All case studies</a> · <a href="mailto:shu@buznik.net">Get in touch</a></footer>
    </main>
