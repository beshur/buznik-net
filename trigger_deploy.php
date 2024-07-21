<?

/*

  Webhook to trigger deploy
*/
if ( !defined('ABSPATH') )
        define('ABSPATH', dirname(__FILE__) . '/');

require_once ABSPATH . '/trigger_code.php';


$realm = 'Restricted area';

function require_auth() {
  $AUTH_USER = 'deploy';
  $AUTH_PASS = TRIGGER_CODE;
  header('Cache-Control: no-cache, must-revalidate, max-age=0');
  $has_supplied_credentials = !(empty($_SERVER['PHP_AUTH_USER']) && empty($_SERVER['PHP_AUTH_PW']));
  $is_not_authenticated = (
    !$has_supplied_credentials ||
    $_SERVER['PHP_AUTH_USER'] != $AUTH_USER ||
    $_SERVER['PHP_AUTH_PW']   != $AUTH_PASS
  );

  error_log('is_not_authenticated: ' . $is_not_authenticated);

  if ($is_not_authenticated) {
    header('HTTP/1.1 401 Authorization Required');
    header('WWW-Authenticate: Basic realm="Access denied"');
    exit;
  }

  return true;
}

// ok, valid username & password
if (require_auth()) {
  echo 'You are from github, I suppose';
}

$output = shell_exec('sh deploy.sh >> hook_log.txt')

echo $output;

header('HTTP/1.1 200 OK');
