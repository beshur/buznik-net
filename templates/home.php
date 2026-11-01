<?php
/**
 * Home page content. Receives: $lang, $page_id.
 * The bio text comes from text_<lang>.html, cached from WordPress
 * by get_resume_texts.php.
 */
$text_file = B_ROOT . '/text_' . $lang . '.html';
if (!is_file($text_file)) {
	throw new RuntimeException("Missing $text_file — run get_resume_texts.php first");
}
?>
    <section class="resume">
      <header>
        <div class="pic">
          <span class="flipper">
            <span class="shu icon-shu">
              <span class="face front"></span>
              <span class="face back">
                <?php include B_ROOT . '/img/shu.svg'; ?>
              </span>
            </span>
          </span>
        </div>
        <h1 ontouchstart="document.querySelector('.flipper').classList.toggle('hover');">
          <?= e(t('name', $lang)) ?>
        </h1>
      </header>

      <div class="resume-main">
        <?php include $text_file; ?>
      </div>

      <div class="links">
        <div><?= e(t('work', $lang)) ?>:</div>
        <ul class="list">
          <li>Current project: <a href="https://starkfuture.com/">Stark Future</a></li>
          <li><a href="https://github.com/beshur">github.com/beshur</a></li>
          <li><a href="https://linkedin.com/in/alexbuznik">LinkedIn</a></li>
          <li><a href="/cv.pdf"><?= e(t('cv', $lang)) ?></a></li>
          <li><a href="cases.html"><?= e(t('cases', $lang)) ?></a></li>
        </ul>
        <div><?= e(t('connect', $lang)) ?>:</div>
        <ul class="list">
          <li>E-mail: <a href="mailto:shu@buznik.net">shu@buznik.net</a></li>
          <li><a href="https://instagram.com/shu.makes">Instagram</a></li>
          <li style="display: none"><a rel="me" href="https://mastodon.social/@Beshur">Mastodon</a></li>
        </ul>
      </div>
    </section>
