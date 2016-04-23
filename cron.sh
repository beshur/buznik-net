# do some cron:
cd "$(dirname "$0")"
command -v php >/dev/null 2>&1 || { echo >&2 "php is undefined. Setting alias."; alias php="/usr/local/bin/php"; }

echo "# Get resume texts:"
php get_resume_texts.php
echo ""
echo "# Caching pages:"
echo "index_rus.html"
php index_page.php > index_rus.html
echo "index_ukr.html"
php index_page.php ukr > index_ukr.html
echo "index_eng.html"
php index_page.php eng > index_eng.html