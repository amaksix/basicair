<?php namespace ProcessWire;

/**
 * Command-line entry point for the content import.
 * Usage: php boot-build.php
 */
if (php_sapi_name() !== 'cli') {
  echo "Run this from the command line.\n";
  exit(1);
}

$root = __DIR__;
require $root . '/wire/core/ProcessWire.php';
$config = ProcessWire::buildConfig($root);
$wire = new ProcessWire($config);
$admin = $wire->users->get('admin');
if ($admin && $admin->id) $wire->users->setCurrentUser($admin);
require $root . '/custom-site/build-site.php';
