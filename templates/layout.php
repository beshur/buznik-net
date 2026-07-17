<?php /** Layout skeleton. Receives: $lang, $page_id, $title, $content */ ?>
<!DOCTYPE html>
<html lang="<?= e($lang === 'ukr' ? 'uk' : 'en') ?>">
<head>
  <title><?= e($title) ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="last-modified" content="<?= e(date('Y-m-d')) ?>" />
  <style type="text/css"><?php include B_ROOT . '/css/main.css'; ?></style>
  <link href="./favicon.ico?v=2" rel="shortcut icon" />
</head>

<body>
  <div id="container">
    <div class="topbar">
      <div class="topbar-lang">
        <ul class="list"><?php foreach (B_LANGUAGES as $code): ?><?php if ($code === $lang): ?><li class="active"><?= e(B_LANG_LABELS[$code]) ?></li><?php else: ?><li><a href="<?= e(page_path($page_id, $code)) ?>"><?= e(B_LANG_LABELS[$code]) ?></a></li><?php endif; ?><?php endforeach; ?></ul>
      </div>
    </div>
<?= $content ?>
  </div>
</body>
</html>
