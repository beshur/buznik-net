<?php
if ( !defined('ABSPATH') )
  die();
  date_default_timezone_set('EET');
?>
<!DOCTYPE html>
<html>
<head>
  <title><?php render_string('name'); ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="last-modified" content="<?php echo date('Y-m-d'); ?>" />
  <style type="text/css"><?php include('css/main.css'); ?></style>
  <link href="./favicon.ico?v=2" rel="shortcut icon" />
</head>

<body>
  <div id="container">
    <div class="topbar">
      <div class="topbar-lang">
        <ul class="list"><?php render_menu($config); ?></ul>
      </div>
    </div>
    <section class="resume">
      <header>
        <div class="pic">
          <span class="flipper">
            <span class="shu icon-shu">
              <span class="face front"></span>
              <span class="face back">
                <?php include('img/shu.svg');?>
              </span>
            </span>
          </span>
        </div>
        <h1 ontouchstart="document.querySelector('.flipper').classList.toggle('hover');">
          <?php render_string('name'); ?>
        </h1>
      </header>

      <div class="resume-main">
        <?php render_text($lang); ?>
      </div>

      <div class="links">
        <div><?php render_string('work'); ?>:</div>
        <ul class="list">
          <li>Current project: <a href="https://starkfuture.com/">Stark Future</a></li>
          <li><a href="https://github.com/beshur">github.com/beshur</a></li>
          <li><a href="https://linkedin.com/in/alexbuznik">LinkedIn</a></li>
          <li><a href="/cv.pdf"><?php render_string('cv'); ?></a></li>
        </ul>
        <div><?php render_string('connect'); ?>:</div>
        <ul class="list">
          <li>E-mail: <a href="mailto:shu@buznik.net">shu@buznik.net</a></li>
          <li><a href="https://instagram.com/shu.makes">Instagram</a></li>
          <li style="display: none"><a rel="me" href="https://mastodon.social/@Beshur">Mastodon</a></li>
        </ul>
      </div>
    </section>
  </div>
</body>
</html>
