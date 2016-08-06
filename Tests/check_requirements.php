<?php

$fail = array();
$pass = array();

if (version_compare(phpversion(), '5.2.4', '<')) {
  $fail[] = 'You need PHP 5.2.4 or greater';
}
else {
  $pass[] = 'You have PHP 5.2.4 or greater';
}

if (!extension_loaded('curl')) {
  $fail[] = 'You do not have cURL support enabled';
} else {
  $pass[] = 'cURL support is enabled';

  $curlVersion = curl_version();
  if (!($curlVersion['features'] & CURL_VERSION_SSL)) {
    $fail[] = 'You do not have SSL enabled';
  } else {
    $pass[] = 'SSL is enabled';
    $pass[] = 'SSL Version: '.$curlVersion['ssl_version'];
  }
}

if (PHP_SAPI != 'cli') {
  echo '<!DOCTYPE html>';
  echo '<html>';
  echo '<head><title>Requirements</title></head>';
  echo '<body>';
  if (count($fail)) {
    echo '<p><strong>The following requirements were not met:</strong>';
    echo '<ul><li>'.join('</li><li>',$fail).'</li></ul>';
  }
  if (count($pass)) {
    echo '<p><strong>The following requirements were successfully met:</strong>';
    echo '<ul><li>'.join('</li><li>',$pass).'</li></ul>';
  }
  echo '</body>';
  echo '</html>';
} else {
  if (count($fail)) {
    echo 'The following requirements were not met:'.PHP_EOL;
    echo join(PHP_EOL,$fail);
    echo PHP_EOL.PHP_EOL;
  }
  if (count($pass)) {
    echo 'The following requirements were successfully met:'.PHP_EOL;
    echo join(PHP_EOL,$pass);
  }
  echo PHP_EOL.PHP_EOL;
  exit(0);
}
?>
