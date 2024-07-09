# do some cron:
cd "$(dirname "$0")"
# hardcode php path
alias php="/usr/local/php73/bin/php"

echo "# Get resume texts (1):"
php get_resume_texts.php
echo ""
echo "# Caching pages:"
echo "ukr.html"
php index_page.php ukr > ukr.html
echo "index.html (eng)"
php index_page.php eng > index.html
