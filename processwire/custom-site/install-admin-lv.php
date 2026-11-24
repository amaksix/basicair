<?php namespace ProcessWire;

/**
 * Latvian labels for the admin screens an editor uses.
 * Safe to run again. Usage: php custom-site/install-admin-lv.php
 */

if (!isset($wire) || !$wire instanceof ProcessWire) {
  if (php_sapi_name() !== 'cli') {
    echo "Run this from the command line.\n";
    exit(1);
  }
  $root = dirname(__DIR__);
  require $root . '/wire/core/ProcessWire.php';
  $config = ProcessWire::buildConfig($root);
  $wire = new ProcessWire($config);
  $admin = $wire->users->get('admin');
  if ($admin && $admin->id) $wire->users->setCurrentUser($admin);
}

require __DIR__ . '/admin-lv.php';

function ba_translate_admin($language, $relative, $map) {
  $translator = $language->translator;
  $translator->setCurrentLanguage($language);
  $file = wire('config')->paths->root . str_replace('/', DIRECTORY_SEPARATOR, $relative);
  if (!is_file($file)) {
    echo "Missing $relative\n";
    return 0;
  }
  $translator->addFileToTranslate($relative, false, false);
  if (!class_exists(__NAMESPACE__ . '\\LanguageParser', false)) {
    require dirname(__DIR__) . '/wire/modules/LanguageSupport/LanguageParser.php';
  }
  $parser = new LanguageParser($translator, $file);
  $textdomain = $translator->filenameToTextdomain($file);
  $count = 0;
  foreach ($parser->getUntranslated() as $hash => $text) {
    if (!isset($map[$text]) || $map[$text] === '') continue;
    $translator->setTranslationFromHash($textdomain, $hash, $map[$text]);
    $count++;
  }
  if ($count) $translator->saveTextdomain($textdomain);
  echo "$count  $relative\n";
  return $count;
}

$language = wire('languages')->getDefault();
$language->of(false);
$map = ba_lv_map();
$total = 0;
$files = array(
  'wire/core/AdminThemeFramework.php',
  'wire/modules/AdminTheme/AdminThemeUikit/AdminThemeUikit.module',
  'wire/modules/Process/ProcessPageEdit/ProcessPageEdit.module',
  'wire/modules/Process/ProcessPageList/ProcessPageList.module',
  'wire/modules/Process/ProcessPageAdd/ProcessPageAdd.module',
  'wire/modules/Process/ProcessPageSearch/ProcessPageSearch.module',
  'wire/modules/Process/ProcessRecentPages/ProcessRecentPages.module',
  'wire/modules/Process/ProcessLogin/ProcessLogin.module',
  'wire/modules/Process/ProcessProfile/ProcessProfile.module',
  'wire/modules/Process/ProcessTemplate/ProcessTemplate.module',
  'wire/modules/Process/ProcessField/ProcessField.module',
  'wire/modules/Process/ProcessUser/ProcessUser.module',
  'wire/modules/Process/ProcessModule/ProcessModule.module',
  'wire/modules/LanguageSupport/ProcessLanguage.module',
);
foreach ($files as $file) $total += ba_translate_admin($language, $file, $map);

$translator = $language->translator;
$translator->setCurrentLanguage($language);
$menuFiles = array(
  'wire/core/AdminThemeFramework.php',
  'wire/templates-admin/default.php',
);
$menuCount = 0;
$menuDomain = '';
foreach ($menuFiles as $menuFile) {
  $translator->addFileToTranslate($menuFile, false, false);
  $menuDomain = $translator->filenameToTextdomain(wire('config')->paths->root . str_replace('/', DIRECTORY_SEPARATOR, $menuFile));
  $translator->loadTextdomain($menuDomain);
  foreach ($map as $english => $latvian) {
    $translator->setTranslation($menuDomain, $english, $latvian);
    $menuCount++;
  }
  $translator->saveTextdomain($menuDomain);
}
echo "$menuCount  menu labels\n";
$total += $menuCount;

$language->save();
$sample = $translator->getTranslation($menuDomain, 'Pages');
echo "Latvian admin phrases: $total\n";
echo "Pages => $sample\n";
