# do some cron:
cd "$(dirname "$0")"
command -v php >/dev/null 2>&1 || { echo >&2 "php is undefined. Setting alias."; alias php="/usr/local/bin/php"; }

echo "# Get resume texts (1):"
php get_resume_texts.php
echo ""
echo "# Caching pages:"
echo "ukr.html"
php index_page.php ukr > ukr.html
echo "index.html (eng)"
php index_page.php eng > index.html
