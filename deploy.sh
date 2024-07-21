DOWNLOAD_URL="https://github.com/beshur/buznik-net/releases/latest/download/dist.zip"
TMP_FILE="_tmp_dist.zip"

wget $DOWNLOAD_URL -O $TMP_FILE \
&& unzip -o $TMP_FILE \
&& rm $TMP_FILE \
&& cd dist \
&& sh cron.sh \
&& sh ../deploy_postback.sh
