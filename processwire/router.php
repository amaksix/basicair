<?php
/**
 * Router for PHP's built-in server. Apache on serveris.lv uses .htaccess instead.
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$file = __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, $uri);
if ($uri !== '/' && is_file($file)) {
  return false;
}
require __DIR__ . '/index.php';
