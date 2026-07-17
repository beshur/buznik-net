# do some cron:
cd "$(dirname "$0")"
# hardcode php path (aliases are NOT expanded in non-interactive scripts)
PHP="/usr/local/php85/bin/php"

echo "# Get resume texts (1):"
"$PHP" get_resume_texts.php || exit 1
echo ""
echo "# Caching pages:"
echo "ukr.html"
"$PHP" index_page.php ukr > ukr.html
echo "index.html (eng)"
"$PHP" index_page.php eng > index.html
cp ukr.html ../ukr.html
cp index.html ../index.html
cp sitemap.xml ../sitemap.xml
