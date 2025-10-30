<?php

// get now playing
// fetch tracks from last.fm
header('Content-Type: application/json');
require_once dirname(__FILE__) . '/config_secrets.php';
$secrets = new Secrets();
$tracks = json_decode(file_get_contents("https://ws.audioscrobbler.com/2.0/?method=user.getrecenttracks&user={$secrets->LASTFM_USER}&api_key={$secrets->LASTFM_API_KEY}&format=json"));
// get the first track name that's inside recenttracks->track
//

if ($tracks != false) {
  print_r(json_encode($tracks->recenttracks->track[0]));
} else {
  echo "error";
}
