# do some cron:
cd "$(dirname "$0")"

# Pick PHP: honour $PHP, else the server's pinned build, else whatever's on PATH (local dev).
if [ -z "${PHP:-}" ]; then
  if [ -x /usr/local/php73/bin/php ]; then PHP=/usr/local/php73/bin/php; else PHP="$(command -v php || true)"; fi
fi
[ -n "$PHP" ] || { echo "ERROR: no php found (set PHP=/path/to/php)"; exit 1; }

echo "# Get resume texts (1):"
"$PHP" get_resume_texts.php
echo ""
echo "# Caching pages:"
echo "ukr.html"
"$PHP" index_page.php ukr > ukr.html
echo "index.html (eng)"
"$PHP" index_page.php eng > index.html

# On the server the public site lives one level up; mirror the built pages there.
# Skip locally, where there's no WordPress alongside.
if [ -f ../j/wp-load.php ]; then
  cp ukr.html ../ukr.html
  cp index.html ../index.html
fi
