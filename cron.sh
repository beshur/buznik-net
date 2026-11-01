# do some cron:
cd "$(dirname "$0")"
# hardcode php path (aliases are NOT expanded in non-interactive scripts);
# fall back to PATH lookup for local runs
PHP="/usr/local/php85/bin/php"
[ -x "$PHP" ] || PHP="$(command -v php)" || { echo "php not found" >&2; exit 1; }

echo "# Get resume texts (1):"
"$PHP" get_resume_texts.php || exit 1
echo "# Get case studies:"
"$PHP" get_case_texts.php || exit 1
echo ""
echo "# Building pages:"
"$PHP" build.php || exit 1
# publish generated pages one level up (www root)
for f in $("$PHP" build.php --print-outputs); do
  cp "$f" "../$f"
done
cp sitemap.xml ../sitemap.xml
