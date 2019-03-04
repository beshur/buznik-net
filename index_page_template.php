<?php
if ( !defined('ABSPATH') )
  die();
?>
<!DOCTYPE html>
<html>
<head>
  <title><?php render_string('name'); ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style type="text/css"><?php include('dist/css/main.css'); ?></style>
  <link href="favicon.ico?v=1" rel="shortcut icon" />
</head>

<body>
  <div id="container">
    <div class="topbar">
      <div class="topbar-location" title="<?php render_string('location'); ?>">
        <?php echo get_string_from_obj($config->currentLocation); ?>
      </div>
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
                <?php include('dist/img/shu.svg');?>
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
      </div><!--

     --><div class="links">
        <div><?php render_string('can'); ?>:</div>
        <ul class="list">
          <li><a href="https://github.com/beshur" target="_blank">github.com/beshur</a></li>
          <li><a href="/cv.pdf" target="_blank"><?php render_string('cv'); ?></a></li>
        </ul>
        <div><?php render_string('connect'); ?>:</div>
        <ul class="list">
          <li>Skype: <a href="skype:alexbuznik">alexbuznik</a></li>
          <li>E-mail: <a href="mailto:shu@buznik.net">shu@buznik.net</a></li>
          <li><a href="https://twitter.com/beshur" target="_blank">twitter.com/beshur</a></li>
        </ul>
      </div>
    </section>
  </div>
</body>
</html>