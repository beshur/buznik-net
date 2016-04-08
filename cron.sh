# do some cron:
echo "# Get resume texts:"
php get_resume_texts.php
echo "\n"
echo "# Caching pages:"
echo "index_rus.html"
php index_page.php > index_rus.html
echo "index_ukr.html"
php index_page.php ukr > index_ukr.html
echo "index_eng.html"
php index_page.php eng > index_eng.html