# Buznik.net Minimal

Buznik.net redesign (started 2016). Minimal for the client.

This is a repository for my home page.

## How the site is put together

Static HTML is generated from PHP templates:

- `pages.php` — the page registry: content template, `<title>` string key,
  and output file name per language. **Adding a page = adding a template
  in `templates/` plus one entry here** — the build, `cron.sh` publishing
  and the language menu pick it up automatically.
- `templates/layout.php` — shared skeleton (head, inlined `css/main.css`,
  language switcher). `templates/home.php` — home page content.
- `lib/strings.php` — UI strings per language + `t($key, $lang)`.
  Languages are listed in `B_LANGUAGES` (`config.php`).
- `lib/render.php` — tiny renderer: `render_page($id, $lang)` wraps a
  content template in the layout; `e()` escapes.
- `build.php` — CLI builder: renders every page × language to static
  files, atomically. Any PHP warning/notice fails the build (exit 1),
  so stray output can never leak into the generated HTML.
  `php build.php` / `php build.php home ukr` / `php build.php --print-outputs`.
- `get_resume_texts.php` — caches the bio text from the WordPress REST
  API into `text_<lang>.html` (used by `templates/home.php`).
- `index_page.php` — optional dynamic preview entry (`?lang=ukr`).
- `cron.sh` — server entry: fetch texts → `build.php` → copy the
  generated files one level up (www root).

Deploy flow: push to master → GitHub Action builds `dist/` and publishes
`dist.zip` as a release → the server webhook (`trigger_deploy.php` →
`deploy.sh`) downloads it into `www/dist` and runs `cron.sh` there.

## Building and development

Tooling is [bun](https://bun.sh) (`brew install oven-sh/bun/bun`); the only
dependency is `sharp` for image compression.

- `bun install` to install dependencies
- `bun run build` to build `dist/` (copy php/assets, minify css, compress images)
- `bun run start` to watch sources, rebuild `dist/` and re-render the html on change
- `php build.php` to just regenerate the html files

Deploys happen exclusively via push to master: the GitHub Action builds
`dist/`, publishes `dist.zip` as a release and pings the server webhook.
The build script is `scripts/build.ts`; note css minification is a small
in-script pass because `css/main.css` uses runtime web paths in `url()`
that bundlers would try to resolve at build time.
