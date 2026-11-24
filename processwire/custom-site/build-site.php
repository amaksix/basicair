<?php namespace ProcessWire;

/**
 * Creates the BasicAir fields, templates and pages, then fills them
 * from the existing content JSON. Safe to run again.
 */

if (!isset($wire) || !$wire instanceof ProcessWire) {
  echo "Boot ProcessWire before including build-site.php\n";
  exit(1);
}

set_time_limit(0);
ini_set('memory_limit', '512M');
error_reporting(E_ALL & ~E_NOTICE);

$repo = dirname(__DIR__, 2);
$contentDir = $repo . DIRECTORY_SEPARATOR . 'content';
$locales = array('lv', 'en', 'ru');

function ba_out($message) {
  echo $message . "\n";
}

function ba_install($name) {
  $modules = wire('modules');
  $modules->refresh();
  if (!$modules->isInstalled($name)) {
    $modules->install($name);
    ba_out("Installed $name");
  }
}

function ba_lang($code) {
  $languages = wire('languages');
  if ($code === 'lv') return $languages->getDefault();
  $language = $languages->get($code);
  return ($language && $language->id) ? $language : null;
}

function ba_set($page, $field, $language, $value) {
  if ($value === null) $value = '';
  if (is_bool($value)) $value = $value ? 1 : 0;
  $page->of(false);
  // Language fields keep each translation on the field value itself.
  // Writing title1017 (and the same pattern) never reaches the database.
  $page->setLanguageValue($language, $field, $value);
}

function ba_repair_languages() {
  $pageNames = wire('modules')->get('LanguageSupportPageNames');
  if (!$pageNames) return;
  foreach (wire('languages') as $language) {
    if (!$language->id || $language->isDefault()) continue;
    $pageNames->languageAdded($language);
  }
}

function ba_join_paragraphs($value) {
  if (!is_array($value)) return trim((string) $value);
  $parts = array();
  foreach ($value as $item) {
    if (is_array($item)) {
      if (isset($item['paragraph'])) $item = $item['paragraph'];
      elseif (isset($item['item'])) $item = $item['item'];
      else $item = '';
    }
    $item = trim((string) $item);
    if ($item !== '') $parts[] = $item;
  }
  return implode("\n\n", $parts);
}

function ba_join_lines($value) {
  if (!is_array($value)) return trim((string) $value);
  $parts = array();
  foreach ($value as $item) {
    if (is_array($item)) {
      if (isset($item['item'])) $item = $item['item'];
      elseif (isset($item['line'])) $item = $item['line'];
      else $item = '';
    }
    $item = trim((string) $item);
    if ($item !== '') $parts[] = $item;
  }
  return implode("\n", $parts);
}

function ba_load_file($contentDir, $locale, $relative) {
  $path = $contentDir . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
  if (!is_file($path)) return array();
  $data = json_decode(file_get_contents($path), true);
  return is_array($data) ? $data : array();
}

function ba_load_dir($contentDir, $dir) {
  $bundled = array();
  foreach (array('lv', 'en', 'ru') as $locale) {
    $path = $contentDir . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . $dir;
    if (!is_dir($path)) continue;
    foreach (glob($path . DIRECTORY_SEPARATOR . '*.json') as $file) {
      $data = json_decode(file_get_contents($file), true);
      if (!is_array($data)) continue;
      $slug = isset($data['slug']) && $data['slug'] !== '' ? $data['slug'] : basename($file, '.json');
      if (!isset($bundled[$slug])) $bundled[$slug] = array();
      $bundled[$slug][$locale] = $data;
    }
  }
  return $bundled;
}

function ba_field($name, $type, $label, $extra = array()) {
  $fields = wire('fields');
  $field = $fields->get($name);
  if (!$field) {
    $field = new Field();
    $field->type = wire('modules')->get($type);
    $field->name = $name;
  }
  $field->label = $label;
  foreach ($extra as $key => $value) {
    $field->$key = $value;
  }
  $field->save();
  return $field;
}

function ba_repeater($name, $label, $innerNames) {
  $field = ba_field($name, 'FieldtypeRepeater', $label);
  $template = wire('templates')->get('repeater_' . $name);
  if (!$template) {
    $fieldtype = wire('modules')->get('FieldtypeRepeater');
    $template = $fieldtype->_getRepeaterTemplate($field);
  }
  if (!$template) {
    throw new \Exception("Repeater template missing for $name");
  }
  $group = $template->fieldgroup;
  foreach ($innerNames as $innerName) {
    $inner = wire('fields')->get($innerName);
    if ($inner && !$group->has($inner)) $group->add($inner);
  }
  $group->save();
  return $field;
}

function ba_template($name, $label, $fieldNames) {
  $templates = wire('templates');
  $fields = wire('fields');
  $template = $templates->get($name);
  if (!$template) {
    $group = new Fieldgroup();
    $group->name = $name;
    $group->add($fields->get('title'));
    $group->save();
    $template = new Template();
    $template->name = $name;
    $template->fieldgroup = $group;
  }
  $template->label = $label;
  $template->save();
  $group = $template->fieldgroup;
  if (!$group->has('title')) $group->add($fields->get('title'));
  foreach ($fieldNames as $fieldName) {
    $field = $fields->get($fieldName);
    if (!$field) {
      ba_out("Missing field $fieldName on template $name");
      continue;
    }
    if (!$group->has($field)) $group->add($field);
  }
  $group->save();
  return $template;
}

function ba_activate($page) {
  $page->of(false);
  foreach (wire('languages') as $language) {
    if ($language->isDefault()) continue;
    $page->set('status' . $language->id, 1);
  }
}

function ba_names($page, $slug) {
  $page->of(false);
  $page->name = $slug;
  foreach (wire('languages') as $language) {
    if ($language->isDefault()) continue;
    $page->set('name' . $language->id, $slug);
  }
}

function ba_ensure($parent, $template, $slug, $title) {
  $page = $parent->child("name=$slug, include=all");
  if (!$page || !$page->id) {
    $page = new Page();
    $page->template = $template;
    $page->parent = $parent;
    $page->name = $slug;
    $page->title = $title ?: $slug;
    $page->save();
  }
  $page->of(false);
  ba_names($page, $slug);
  ba_activate($page);
  if ($title) ba_set($page, 'title', ba_lang('lv'), $title);
  return $page;
}

function ba_abs($repo, $webPath) {
  if (!$webPath || strpos($webPath, 'http') === 0) return '';
  $abs = $repo . str_replace('/', DIRECTORY_SEPARATOR, $webPath);
  return is_file($abs) ? $abs : '';
}

function ba_image($repo, $page, $field, $webPath, $pathField = '') {
  $page->of(false);
  if (!$page->id) $page->save();
  if ($pathField && $page->hasField($pathField)) $page->set($pathField, (string) $webPath);
  if (!$page->hasField($field)) return;
  $abs = ba_abs($repo, $webPath);
  try {
    $page->$field->removeAll();
    if ($abs) $page->$field->add($abs);
  } catch (\Exception $exception) {
    ba_out('Image skipped ' . $webPath . ' (' . $exception->getMessage() . ')');
  }
}

function ba_wipe($page, $field) {
  if (!$page->hasField($field)) return;
  $page->of(false);
  foreach ($page->$field as $item) {
    $item->delete();
  }
  $page->save($field);
}

function ba_row($page, $field) {
  $page->of(false);
  if (!$page->id) $page->save();
  $row = $page->$field->getNew();
  $row->of(false);
  if (!$row->id) $row->save();
  ba_activate($row);
  return $row;
}

function ba_indexed($bundle, $key) {
  $max = 0;
  foreach ($bundle as $data) {
    $count = (isset($data[$key]) && is_array($data[$key])) ? count($data[$key]) : 0;
    if ($count > $max) $max = $count;
  }
  return $max;
}

ba_out('Languages');
ba_install('LanguageSupport');
ba_install('LanguageSupportFields');
ba_install('LanguageSupportPageNames');
ba_install('FieldtypeRepeater');

$default = wire('languages')->getDefault();
$default->of(false);
$default->title = 'Latviešu';
$default->save();

if (!ba_lang('en')) {
  $english = wire('languages')->add('en');
  $english->of(false);
  $english->title = 'English';
  $english->save();
} else {
  $english = ba_lang('en');
  $english->of(false);
  $english->title = 'English';
  $english->save();
}

if (!ba_lang('ru')) {
  $russian = wire('languages')->add('ru');
  $russian->of(false);
  $russian->title = 'Русский';
  $russian->save();
} else {
  $russian = ba_lang('ru');
  $russian->of(false);
  $russian->title = 'Русский';
  $russian->save();
}

ba_repair_languages();

$titleField = wire('fields')->get('title');
$titleType = wire('modules')->get('FieldtypePageTitleLanguage');
if ($titleType && $titleField->type->className() !== 'FieldtypePageTitleLanguage') {
  try {
    $titleField->type = $titleType;
    $titleField->save();
    ba_out('Title field is now multilingual');
  } catch (\Exception $exception) {
    ba_out('Title field conversion: ' . $exception->getMessage());
  }
}

ba_out('Fields');
$text = 'FieldtypeTextLanguage';
$area = 'FieldtypeTextareaLanguage';
$plain = 'FieldtypeText';

$labels = array(
  'site_name' => 'Site name',
  'site_tagline' => 'Tagline',
  'nav_home' => 'Navigation: Home',
  'nav_products' => 'Navigation: Products',
  'nav_industries' => 'Navigation: Industries',
  'nav_services' => 'Navigation: Services',
  'nav_news' => 'Navigation: Announcements',
  'nav_contact' => 'Navigation: Contact',
  'label_request_quote' => 'Label: Request quote',
  'label_view_all' => 'Label: View all categories',
  'label_technical_specs' => 'Label: Technical specs',
  'label_device_specs' => 'Label: Device specs',
  'label_benefits' => 'Label: Benefits',
  'label_performance' => 'Label: Performance table',
  'label_related' => 'Label: Related products',
  'label_order' => 'Label: Order',
  'label_learn_more' => 'Label: Learn more',
  'label_quick_view' => 'Label: Quick view',
  'label_shop' => 'Label: Products',
  'label_need_help' => 'Label: Need help',
  'phone' => 'Phone',
  'email' => 'Email',
  'copyright' => 'Copyright',
  'newsletter_heading' => 'Newsletter heading',
  'newsletter_placeholder' => 'Newsletter placeholder',
  'subscribe_button' => 'Subscribe button',
  'facebook' => 'Facebook URL',
  'twitter' => 'X URL',
  'instagram' => 'Instagram URL',
  'telegram' => 'Telegram URL',
  'heading' => 'Heading',
  'button_text' => 'Button text',
  'button_url' => 'Button URL',
  'caption' => 'Caption',
  'announce_text' => 'Text',
  'highlight' => 'Highlight',
  'link_label' => 'Link label',
  'link_url' => 'Link URL',
  'spec_label' => 'Label',
  'spec_value' => 'Value',
  'vision_heading' => 'Vision heading',
  'featured_heading' => 'Featured heading',
  'featured_subheading' => 'Featured subheading',
  'gallery_heading' => 'Gallery heading',
  'gallery_subheading' => 'Gallery subheading',
  'partners_heading' => 'Partners heading',
  'headline' => 'Hero title',
  'main_heading' => 'Main heading',
  'learn_more' => 'Read more label',
  'recommended_heading' => 'Recommended products heading',
  'empty_state' => 'Empty state',
  'coming_soon' => 'Coming soon note',
  'cta_text' => 'Button text',
  'price' => 'Price',
  'price_note' => 'Price note',
  'benefits_title' => 'Benefits title',
  'specs_title' => 'Specs title',
  'device_title' => 'Device specs title',
  'table_title' => 'Table title',
  'market_label' => 'Market / category',
  'form_heading' => 'Form heading',
  'ci_heading' => 'Contact heading',
  'ci_email_label' => 'Email label',
  'ci_email' => 'Email',
  'ci_phone_label' => 'Phone label',
  'ci_phone' => 'Phone',
  'co_heading' => 'Company heading',
  'co_name' => 'Company name',
  'co_reg_label' => 'Registration number label',
  'co_reg' => 'Registration number',
  'co_vat_label' => 'VAT label',
  'co_vat' => 'VAT number',
  'bk_heading' => 'Bank heading',
  'bk_name' => 'Bank name',
  'bk_swift_label' => 'SWIFT label',
  'bk_swift' => 'SWIFT',
  'bk_account_label' => 'Account label',
  'bk_account' => 'Account',
  'fl_legal_entity' => 'Legal entity label',
  'fl_legal_hint' => 'Legal entity hint',
  'fl_company_name' => 'Company name label',
  'fl_reg_number' => 'Registration number label',
  'fl_vat_number' => 'VAT label',
  'fl_name' => 'Name label',
  'fl_name_hint' => 'Name hint',
  'fl_email' => 'Email label',
  'fl_email_hint' => 'Email hint',
  'fl_message' => 'Message label',
  'fl_message_placeholder' => 'Message placeholder',
  'fl_submit' => 'Submit label',
  'slide_class' => 'Slide class',
);
foreach ($labels as $name => $label) {
  $type = ($name === 'slide_class') ? $plain : $text;
  ba_field($name, $type, $label);
}

$areas = array(
  'address' => 'Address',
  'subheading' => 'Subheading',
  'vision_body' => 'Vision text',
  'summary' => 'Summary',
  'body' => 'Text',
  'highlights' => 'List (one item per line)',
  'closing' => 'Closing text',
  'bullets_intro' => 'List introduction',
  'benefits' => 'Benefits (one per line)',
  'table_headers' => 'Table headers (one per line)',
  'cell_text' => 'Cells (one per line)',
  'keywords' => 'Keywords',
  'form_intro' => 'Form introduction',
  'la_heading' => 'Legal address heading',
  'la_address' => 'Legal address',
  'of_heading' => 'Office heading',
  'of_address' => 'Office address',
);
foreach ($areas as $name => $label) {
  $type = (strpos($name, 'heading') !== false) ? $text : $area;
  ba_field($name, $type, $label);
}

ba_field('card_image', 'FieldtypeImage', 'Card image', array('maxFiles' => 1, 'extensions' => 'jpg jpeg png gif webp'));
ba_field('hover_image', 'FieldtypeImage', 'Hover image', array('maxFiles' => 1, 'extensions' => 'jpg jpeg png gif webp'));
ba_field('hero_image', 'FieldtypeImage', 'Hero image', array('maxFiles' => 1, 'extensions' => 'jpg jpeg png gif webp'));
ba_field('slide_image', 'FieldtypeImage', 'Image', array('maxFiles' => 1, 'extensions' => 'jpg jpeg png gif webp svg'));
ba_field('gallery', 'FieldtypeImage', 'Gallery', array('maxFiles' => 0, 'extensions' => 'jpg jpeg png gif webp'));
ba_field('image_path', $plain, 'Current image path');
ba_field('hover_path', $plain, 'Current hover image path');
ba_field('hero_path', $plain, 'Current hero image path');
ba_field('featured', 'FieldtypeCheckbox', 'Featured on the homepage');
ba_field('article_date', 'FieldtypeDatetime', 'Date', array(
  'dateInputFormat' => 'Y-m-d',
  'dateOutputFormat' => 'j.m.Y',
  'timeInputFormat' => '',
));

ba_repeater('hero_slides', 'Hero slides', array('heading', 'subheading', 'button_text', 'button_url', 'slide_class', 'slide_image', 'image_path'));
ba_repeater('home_categories', 'Homepage categories', array('heading', 'button_url', 'slide_image', 'image_path'));
ba_repeater('sectors', 'Sectors', array('heading', 'slide_image', 'image_path'));
ba_repeater('gallery_items', 'Gallery', array('caption', 'button_url', 'slide_image', 'image_path'));
ba_repeater('partner_logos', 'Partner logos', array('caption', 'slide_image', 'image_path'));
ba_repeater('announcements', 'Top bar announcements', array('announce_text', 'highlight'));
ba_repeater('footer_info', 'Footer information links', array('link_label', 'link_url'));
ba_repeater('footer_service', 'Footer service links', array('link_label', 'link_url'));
ba_repeater('specs', 'Technical specs', array('spec_label', 'spec_value'));
ba_repeater('device_specs', 'Device specs', array('spec_label', 'spec_value'));
ba_repeater('sections', 'Content sections', array('heading', 'body', 'highlights', 'closing'));
ba_repeater('table_rows', 'Table rows', array('cell_text'));

ba_out('Templates');
ba_template('home', 'Home', array(
  'hero_slides', 'home_categories', 'vision_heading', 'vision_body', 'sectors',
  'featured_heading', 'featured_subheading', 'gallery_heading', 'gallery_subheading', 'gallery_items',
  'partners_heading', 'partner_logos',
));
ba_template('settings', 'Site settings', array(
  'site_name', 'site_tagline', 'announcements',
  'nav_home', 'nav_products', 'nav_industries', 'nav_services', 'nav_news', 'nav_contact',
  'label_request_quote', 'label_view_all', 'label_technical_specs', 'label_device_specs', 'label_benefits',
  'label_performance', 'label_related', 'label_order', 'label_learn_more', 'label_quick_view', 'label_shop', 'label_need_help',
  'phone', 'email', 'address', 'copyright', 'newsletter_heading', 'newsletter_placeholder', 'subscribe_button',
  'facebook', 'twitter', 'instagram', 'telegram', 'footer_info', 'footer_service',
));
ba_template('shop', 'Shop', array('headline', 'summary', 'hero_image', 'hero_path'));
ba_template('category', 'Product category', array('summary', 'body', 'coming_soon', 'card_image', 'image_path', 'hero_image', 'hero_path'));
ba_template('products', 'Products', array());
ba_template('industries', 'Industries', array('headline', 'summary', 'main_heading', 'recommended_heading', 'hero_image', 'hero_path'));
ba_template('services', 'Services', array('headline', 'summary', 'main_heading', 'learn_more', 'hero_image', 'hero_path'));
ba_template('service', 'Service', array('headline', 'summary', 'body', 'bullets_intro', 'highlights', 'closing', 'cta_text', 'card_image', 'image_path', 'hero_image', 'hero_path'));
ba_template('news', 'Announcements', array('headline', 'summary', 'main_heading', 'learn_more', 'empty_state', 'hero_image', 'hero_path'));
ba_template('article', 'Announcement', array('summary', 'body', 'article_date', 'market_label', 'card_image', 'image_path', 'hero_image', 'hero_path'));
ba_template('industry', 'Industry', array('summary', 'body', 'cta_text', 'card_image', 'image_path', 'hero_image', 'hero_path'));
ba_template('contact', 'Contact', array(
  'headline', 'hero_image', 'hero_path', 'form_heading', 'form_intro',
  'fl_legal_entity', 'fl_legal_hint', 'fl_company_name', 'fl_reg_number', 'fl_vat_number',
  'fl_name', 'fl_name_hint', 'fl_email', 'fl_email_hint', 'fl_message', 'fl_message_placeholder', 'fl_submit',
  'ci_heading', 'ci_email_label', 'ci_email', 'ci_phone_label', 'ci_phone',
  'co_heading', 'co_name', 'co_reg_label', 'co_reg', 'co_vat_label', 'co_vat',
  'la_heading', 'la_address', 'of_heading', 'of_address',
  'bk_heading', 'bk_name', 'bk_swift_label', 'bk_swift', 'bk_account_label', 'bk_account',
));

$categoryTemplate = wire('templates')->get('category');
$productTemplate = wire('templates')->get('product');
if (!$productTemplate) {
  ba_template('product', 'Product', array('summary'));
  $productTemplate = wire('templates')->get('product');
}
$industryTemplate = wire('templates')->get('industry');

ba_field('category_ref', 'FieldtypePage', 'Category', array(
  'derefAsPage' => 1,
  'inputfield' => 'InputfieldPageListSelect',
  'template_ids' => array($categoryTemplate->id),
));
ba_field('parent_market', 'FieldtypePage', 'Parent industry', array(
  'derefAsPage' => 1,
  'inputfield' => 'InputfieldPageListSelect',
  'template_ids' => array($industryTemplate->id),
));
ba_field('recommended', 'FieldtypePage', 'Recommended products', array(
  'derefAsPage' => 0,
  'inputfield' => 'InputfieldAsmSelect',
  'template_ids' => array($productTemplate->id),
));

ba_template('product', 'Product', array(
  'summary', 'body', 'price', 'price_note', 'highlights', 'benefits_title', 'benefits',
  'specs_title', 'specs', 'device_title', 'device_specs', 'sections',
  'table_title', 'table_headers', 'table_rows', 'category_ref', 'featured',
  'card_image', 'image_path', 'hover_image', 'hover_path', 'gallery', 'keywords',
));
ba_template('industry', 'Industry', array('summary', 'body', 'cta_text', 'card_image', 'image_path', 'hero_image', 'hero_path', 'parent_market', 'recommended'));

wire('pages')->uncacheAll();
$home = wire('pages')->get('/');
$home->of(false);
ba_activate($home);

ba_out('Pages');
$homeData = array();
foreach ($locales as $locale) $homeData[$locale] = ba_load_file($contentDir, $locale, 'pages/home.json');
foreach ($locales as $locale) {
  $language = ba_lang($locale);
  $data = $homeData[$locale];
  if ($language && isset($data['title'])) ba_set($home, 'title', $language, $data['title']);
}
$home->save();

$containers = array(
  'shop' => array('template' => 'shop', 'file' => 'pages/shop.json', 'titleKey' => 'heading'),
  'products' => array('template' => 'products', 'file' => '', 'fallback' => 'Products'),
  'industries' => array('template' => 'industries', 'file' => 'pages/industries.json', 'titleKey' => 'hero_title'),
  'services' => array('template' => 'services', 'file' => 'pages/services.json', 'titleKey' => 'hero_title'),
  'zinojumi' => array('template' => 'news', 'file' => 'pages/news.json', 'titleKey' => 'hero_title'),
  'contact' => array('template' => 'contact', 'file' => 'pages/contact.json', 'titleKey' => 'hero_title'),
);

$containerPages = array();
foreach ($containers as $slug => $info) {
  $bundled = array();
  if ($info['file']) {
    foreach ($locales as $locale) $bundled[$locale] = ba_load_file($contentDir, $locale, $info['file']);
  }
  $lvTitle = isset($bundled['lv']['title']) ? $bundled['lv']['title'] : (isset($info['fallback']) ? $info['fallback'] : $slug);
  $page = ba_ensure($home, $info['template'], $slug, $lvTitle);
  foreach ($locales as $locale) {
    $language = ba_lang($locale);
    $data = isset($bundled[$locale]) ? $bundled[$locale] : array();
    if (!$language || !$data) continue;
    if (isset($data['title'])) ba_set($page, 'title', $language, $data['title']);
    $headline = '';
    if (isset($data['hero_title'])) $headline = $data['hero_title'];
    elseif (isset($data['heading'])) $headline = $data['heading'];
    if ($headline !== '' && $page->hasField('headline')) ba_set($page, 'headline', $language, $headline);
    $summary = '';
    if (isset($data['main_intro'])) $summary = $data['main_intro'];
    elseif (isset($data['description'])) $summary = $data['description'];
    if ($summary !== '' && $page->hasField('summary')) ba_set($page, 'summary', $language, $summary);
    if (isset($data['main_heading']) && $page->hasField('main_heading')) ba_set($page, 'main_heading', $language, $data['main_heading']);
    if (isset($data['learn_more']) && $page->hasField('learn_more')) ba_set($page, 'learn_more', $language, $data['learn_more']);
    if (isset($data['read_more']) && $page->hasField('learn_more')) ba_set($page, 'learn_more', $language, $data['read_more']);
    if (isset($data['recommended_heading']) && $page->hasField('recommended_heading')) ba_set($page, 'recommended_heading', $language, $data['recommended_heading']);
    if (isset($data['empty_state']) && $page->hasField('empty_state')) ba_set($page, 'empty_state', $language, $data['empty_state']);
    if (!empty($data['hero_image'])) ba_image($repo, $page, 'hero_image', $data['hero_image'], 'hero_path');
  }
  $page->save();
  $containerPages[$slug] = $page;
  ba_out("Page /$slug/");
}

$settings = ba_ensure($home, 'settings', 'settings', 'Site settings');
$settings->addStatus(Page::statusHidden);
$settings->addStatus(Page::statusUnpublished);
$settingsFiles = array();
foreach ($locales as $locale) $settingsFiles[$locale] = ba_load_file($contentDir, $locale, 'settings.json');
$settingsMap = array(
  'site_name' => 'site_name',
  'site_tagline' => 'site_tagline',
);
foreach ($locales as $locale) {
  $language = ba_lang($locale);
  $data = $settingsFiles[$locale];
  if (!$language || !$data) continue;
  foreach ($settingsMap as $field => $key) {
    if (isset($data[$key])) ba_set($settings, $field, $language, $data[$key]);
  }
  $nav = isset($data['navigation']) ? $data['navigation'] : array();
  foreach (array('home', 'products', 'industries', 'services', 'news', 'contact') as $key) {
    if (isset($nav[$key])) ba_set($settings, 'nav_' . $key, $language, $nav[$key]);
  }
  $footer = isset($data['footer']) ? $data['footer'] : array();
  foreach (array('phone', 'email', 'address', 'copyright', 'newsletter_heading', 'newsletter_placeholder', 'subscribe_button') as $key) {
    if (isset($footer[$key])) ba_set($settings, $key, $language, $footer[$key]);
  }
  $social = isset($data['social']) ? $data['social'] : array();
  foreach (array('facebook', 'twitter', 'instagram', 'telegram') as $key) {
    if (isset($social[$key])) ba_set($settings, $key, $language, $social[$key]);
  }
  $labelMap = array(
    'request_quote' => 'label_request_quote',
    'view_all_categories' => 'label_view_all',
    'technical_specs' => 'label_technical_specs',
    'device_specs' => 'label_device_specs',
    'benefits' => 'label_benefits',
    'performance_table' => 'label_performance',
    'related_products' => 'label_related',
    'order' => 'label_order',
    'learn_more' => 'label_learn_more',
    'quick_view' => 'label_quick_view',
    'shop' => 'label_shop',
    'need_help' => 'label_need_help',
  );
  $labelsData = isset($data['labels']) ? $data['labels'] : array();
  foreach ($labelMap as $key => $field) {
    if (isset($labelsData[$key])) ba_set($settings, $field, $language, $labelsData[$key]);
  }
}
$settings->save();
ba_wipe($settings, 'announcements');
ba_wipe($settings, 'footer_info');
ba_wipe($settings, 'footer_service');
$announcementCount = ba_indexed($settingsFiles, 'topbar_announcements');
for ($i = 0; $i < $announcementCount; $i++) {
  $row = ba_row($settings, 'announcements');
  foreach ($locales as $locale) {
    $language = ba_lang($locale);
    $item = isset($settingsFiles[$locale]['topbar_announcements'][$i]) ? $settingsFiles[$locale]['topbar_announcements'][$i] : array();
    ba_set($row, 'announce_text', $language, isset($item['text']) ? $item['text'] : '');
    ba_set($row, 'highlight', $language, isset($item['highlight']) ? $item['highlight'] : '');
  }
  $row->save();
}
foreach (array('info_links' => 'footer_info', 'service_links' => 'footer_service') as $jsonKey => $field) {
  $count = 0;
  foreach ($settingsFiles as $data) {
    $footer = isset($data['footer'][$jsonKey]) ? $data['footer'][$jsonKey] : array();
    if (count($footer) > $count) $count = count($footer);
  }
  for ($i = 0; $i < $count; $i++) {
    $row = ba_row($settings, $field);
    foreach ($locales as $locale) {
      $language = ba_lang($locale);
      $links = isset($settingsFiles[$locale]['footer'][$jsonKey]) ? $settingsFiles[$locale]['footer'][$jsonKey] : array();
      $item = isset($links[$i]) ? $links[$i] : array();
      ba_set($row, 'link_label', $language, isset($item['label']) ? $item['label'] : '');
      ba_set($row, 'link_url', $language, isset($item['url']) ? $item['url'] : '');
    }
    $row->save();
  }
}
$settings->save();
ba_out('Settings');

ba_out('Homepage');
foreach (array('hero_slides', 'home_categories', 'sectors', 'gallery_items', 'partner_logos') as $field) {
  ba_wipe($home, $field);
}
$home->of(false);
foreach ($locales as $locale) {
  $language = ba_lang($locale);
  $data = $homeData[$locale];
  $vision = isset($data['vision']) ? $data['vision'] : array();
  $featured = isset($data['featured_section']) ? $data['featured_section'] : array();
  $gallery = isset($data['gallery']) ? $data['gallery'] : array();
  $partners = isset($data['partners']) ? $data['partners'] : array();
  ba_set($home, 'vision_heading', $language, isset($vision['heading']) ? $vision['heading'] : '');
  ba_set($home, 'vision_body', $language, ba_join_paragraphs(isset($vision['paragraphs']) ? $vision['paragraphs'] : array()));
  ba_set($home, 'featured_heading', $language, isset($featured['heading']) ? $featured['heading'] : '');
  ba_set($home, 'featured_subheading', $language, isset($featured['subheading']) ? $featured['subheading'] : '');
  ba_set($home, 'gallery_heading', $language, isset($gallery['heading']) ? $gallery['heading'] : '');
  ba_set($home, 'gallery_subheading', $language, isset($gallery['subheading']) ? $gallery['subheading'] : '');
  ba_set($home, 'partners_heading', $language, isset($partners['heading']) ? $partners['heading'] : '');
}
$slideCount = ba_indexed($homeData, 'hero_slides');
for ($i = 0; $i < $slideCount; $i++) {
  $row = ba_row($home, 'hero_slides');
  foreach ($locales as $locale) {
    $language = ba_lang($locale);
    $item = isset($homeData[$locale]['hero_slides'][$i]) ? $homeData[$locale]['hero_slides'][$i] : array();
    ba_set($row, 'heading', $language, isset($item['heading']) ? $item['heading'] : '');
    ba_set($row, 'subheading', $language, isset($item['subheading']) ? $item['subheading'] : '');
    ba_set($row, 'button_text', $language, isset($item['button_text']) ? $item['button_text'] : '');
    ba_set($row, 'button_url', $language, isset($item['button_url']) ? $item['button_url'] : '');
    if ($locale === 'lv') {
      $row->slide_class = isset($item['slide_class']) ? $item['slide_class'] : 'slide-1';
      ba_image($repo, $row, 'slide_image', isset($item['image']) ? $item['image'] : '', 'image_path');
    }
  }
  $row->save();
}
$categoryCount = ba_indexed($homeData, 'categories');
for ($i = 0; $i < $categoryCount; $i++) {
  $row = ba_row($home, 'home_categories');
  foreach ($locales as $locale) {
    $language = ba_lang($locale);
    $item = isset($homeData[$locale]['categories'][$i]) ? $homeData[$locale]['categories'][$i] : array();
    ba_set($row, 'heading', $language, isset($item['title']) ? $item['title'] : '');
    ba_set($row, 'button_url', $language, isset($item['url']) ? $item['url'] : '');
    if ($locale === 'lv') ba_image($repo, $row, 'slide_image', isset($item['image']) ? $item['image'] : '', 'image_path');
  }
  $row->save();
}
$sectorCount = 0;
foreach ($homeData as $data) {
  $count = isset($data['vision']['sectors']) ? count($data['vision']['sectors']) : 0;
  if ($count > $sectorCount) $sectorCount = $count;
}
for ($i = 0; $i < $sectorCount; $i++) {
  $row = ba_row($home, 'sectors');
  foreach ($locales as $locale) {
    $language = ba_lang($locale);
    $item = isset($homeData[$locale]['vision']['sectors'][$i]) ? $homeData[$locale]['vision']['sectors'][$i] : array();
    ba_set($row, 'heading', $language, isset($item['title']) ? $item['title'] : '');
    if ($locale === 'lv') ba_image($repo, $row, 'slide_image', isset($item['image']) ? $item['image'] : '', 'image_path');
  }
  $row->save();
}
$galleryCount = 0;
foreach ($homeData as $data) {
  $count = isset($data['gallery']['items']) ? count($data['gallery']['items']) : 0;
  if ($count > $galleryCount) $galleryCount = $count;
}
for ($i = 0; $i < $galleryCount; $i++) {
  $row = ba_row($home, 'gallery_items');
  foreach ($locales as $locale) {
    $language = ba_lang($locale);
    $item = isset($homeData[$locale]['gallery']['items'][$i]) ? $homeData[$locale]['gallery']['items'][$i] : array();
    ba_set($row, 'caption', $language, isset($item['alt']) ? $item['alt'] : '');
    ba_set($row, 'button_url', $language, isset($item['url']) ? $item['url'] : '');
    if ($locale === 'lv') ba_image($repo, $row, 'slide_image', isset($item['image']) ? $item['image'] : '', 'image_path');
  }
  $row->save();
}
$logoCount = 0;
foreach ($homeData as $data) {
  $count = isset($data['partners']['logos']) ? count($data['partners']['logos']) : 0;
  if ($count > $logoCount) $logoCount = $count;
}
for ($i = 0; $i < $logoCount; $i++) {
  $row = ba_row($home, 'partner_logos');
  foreach ($locales as $locale) {
    $language = ba_lang($locale);
    $item = isset($homeData[$locale]['partners']['logos'][$i]) ? $homeData[$locale]['partners']['logos'][$i] : array();
    ba_set($row, 'caption', $language, isset($item['alt']) ? $item['alt'] : '');
    if ($locale === 'lv') ba_image($repo, $row, 'slide_image', isset($item['image']) ? $item['image'] : '', 'image_path');
  }
  $row->save();
}
$home->save();

ba_out('Categories');
$shop = $containerPages['shop'];
$categoryPages = array();
foreach (ba_load_dir($contentDir, 'product-categories') as $slug => $bundle) {
  $lv = isset($bundle['lv']) ? $bundle['lv'] : reset($bundle);
  $page = ba_ensure($shop, 'category', $slug, isset($lv['title']) ? $lv['title'] : $slug);
  foreach ($locales as $locale) {
    $language = ba_lang($locale);
    $data = isset($bundle[$locale]) ? $bundle[$locale] : array();
    if (!$language || !$data) continue;
    ba_set($page, 'title', $language, isset($data['title']) ? $data['title'] : '');
    ba_set($page, 'summary', $language, isset($data['summary']) ? $data['summary'] : '');
    ba_set($page, 'body', $language, ba_join_paragraphs(isset($data['paragraphs']) ? $data['paragraphs'] : array()));
    ba_set($page, 'coming_soon', $language, isset($data['coming_soon']) ? $data['coming_soon'] : '');
    if ($locale === 'lv') {
      ba_image($repo, $page, 'card_image', isset($data['image']) ? $data['image'] : '', 'image_path');
      ba_image($repo, $page, 'hero_image', isset($data['hero_image']) ? $data['hero_image'] : '', 'hero_path');
      $page->sort = isset($data['order']) ? (int) $data['order'] : 0;
    }
  }
  $page->save();
  $categoryPages[$slug] = $page;
  ba_out("  $slug");
}

ba_out('Products');
$productParent = $containerPages['products'];
$productPages = array();
foreach (ba_load_dir($contentDir, 'products') as $slug => $bundle) {
  $lv = isset($bundle['lv']) ? $bundle['lv'] : reset($bundle);
  $page = ba_ensure($productParent, 'product', $slug, isset($lv['title']) ? $lv['title'] : $slug);
  ba_wipe($page, 'specs');
  ba_wipe($page, 'device_specs');
  ba_wipe($page, 'sections');
  ba_wipe($page, 'table_rows');
  foreach ($locales as $locale) {
    $language = ba_lang($locale);
    $data = isset($bundle[$locale]) ? $bundle[$locale] : array();
    if (!$language || !$data) continue;
    ba_set($page, 'title', $language, isset($data['title']) ? $data['title'] : '');
    ba_set($page, 'summary', $language, isset($data['description']) ? $data['description'] : '');
    $paragraphs = isset($data['paragraphs']) ? $data['paragraphs'] : array();
    if (!$paragraphs && !empty($data['body'])) $paragraphs = array($data['body']);
    ba_set($page, 'body', $language, ba_join_paragraphs($paragraphs));
    ba_set($page, 'price', $language, isset($data['price']) ? $data['price'] : '');
    ba_set($page, 'price_note', $language, isset($data['price_note']) ? $data['price_note'] : '');
    ba_set($page, 'highlights', $language, ba_join_lines(isset($data['highlights']) ? $data['highlights'] : array()));
    ba_set($page, 'benefits_title', $language, isset($data['benefits_title']) ? $data['benefits_title'] : '');
    ba_set($page, 'benefits', $language, ba_join_lines(isset($data['benefits']) ? $data['benefits'] : array()));
    ba_set($page, 'specs_title', $language, isset($data['specs_title']) ? $data['specs_title'] : '');
    ba_set($page, 'device_title', $language, isset($data['device_specs_title']) ? $data['device_specs_title'] : '');
    $table = isset($data['spec_table']) ? $data['spec_table'] : array();
    ba_set($page, 'table_title', $language, isset($table['title']) ? $table['title'] : '');
    ba_set($page, 'table_headers', $language, ba_join_lines(isset($table['headers']) ? $table['headers'] : array()));
    ba_set($page, 'keywords', $language, ba_join_lines(isset($data['keywords']) ? $data['keywords'] : array()));
    if ($locale === 'lv') {
      $page->featured = !empty($data['featured']) ? 1 : 0;
      $page->sort = isset($data['order']) ? (int) $data['order'] : 0;
      $categorySlug = isset($data['category']) ? $data['category'] : '';
      if ($categorySlug && isset($categoryPages[$categorySlug])) $page->category_ref = $categoryPages[$categorySlug];
      ba_image($repo, $page, 'card_image', isset($data['image']) ? $data['image'] : '', 'image_path');
      ba_image($repo, $page, 'hover_image', isset($data['image_hover']) ? $data['image_hover'] : '', 'hover_path');
      $page->gallery->removeAll();
      if (!empty($data['gallery']) && is_array($data['gallery'])) {
        foreach ($data['gallery'] as $image) {
          $abs = ba_abs($repo, isset($image['image']) ? $image['image'] : '');
          if ($abs) {
            try { $page->gallery->add($abs); } catch (\Exception $exception) {}
          }
        }
      }
    }
  }
  $page->save();
  $specCount = ba_indexed($bundle, 'specs');
  for ($i = 0; $i < $specCount; $i++) {
    $row = ba_row($page, 'specs');
    foreach ($locales as $locale) {
      $language = ba_lang($locale);
      $item = isset($bundle[$locale]['specs'][$i]) ? $bundle[$locale]['specs'][$i] : array();
      ba_set($row, 'spec_label', $language, isset($item['label']) ? $item['label'] : '');
      ba_set($row, 'spec_value', $language, isset($item['value']) ? $item['value'] : '');
    }
    $row->save();
  }
  $deviceCount = ba_indexed($bundle, 'device_specs');
  for ($i = 0; $i < $deviceCount; $i++) {
    $row = ba_row($page, 'device_specs');
    foreach ($locales as $locale) {
      $language = ba_lang($locale);
      $item = isset($bundle[$locale]['device_specs'][$i]) ? $bundle[$locale]['device_specs'][$i] : array();
      ba_set($row, 'spec_label', $language, isset($item['label']) ? $item['label'] : '');
      ba_set($row, 'spec_value', $language, isset($item['value']) ? $item['value'] : '');
    }
    $row->save();
  }
  $sectionCount = ba_indexed($bundle, 'sections');
  for ($i = 0; $i < $sectionCount; $i++) {
    $row = ba_row($page, 'sections');
    foreach ($locales as $locale) {
      $language = ba_lang($locale);
      $item = isset($bundle[$locale]['sections'][$i]) ? $bundle[$locale]['sections'][$i] : array();
      ba_set($row, 'heading', $language, isset($item['title']) ? $item['title'] : '');
      ba_set($row, 'body', $language, ba_join_paragraphs(isset($item['paragraphs']) ? $item['paragraphs'] : array()));
      ba_set($row, 'highlights', $language, ba_join_lines(isset($item['items']) ? $item['items'] : array()));
      ba_set($row, 'closing', $language, ba_join_paragraphs(isset($item['closing_paragraphs']) ? $item['closing_paragraphs'] : array()));
    }
    $row->save();
  }
  $rowCount = 0;
  foreach ($bundle as $data) {
    $count = isset($data['spec_table']['rows']) ? count($data['spec_table']['rows']) : 0;
    if ($count > $rowCount) $rowCount = $count;
  }
  for ($i = 0; $i < $rowCount; $i++) {
    $row = ba_row($page, 'table_rows');
    foreach ($locales as $locale) {
      $language = ba_lang($locale);
      $item = isset($bundle[$locale]['spec_table']['rows'][$i]) ? $bundle[$locale]['spec_table']['rows'][$i] : array();
      $cells = isset($item['cells']) ? $item['cells'] : $item;
      ba_set($row, 'cell_text', $language, ba_join_lines($cells));
    }
    $row->save();
  }
  $page->save();
  $productPages[$slug] = $page;
  ba_out("  $slug");
}

ba_out('Services');
$serviceParent = $containerPages['services'];
foreach (ba_load_dir($contentDir, 'services') as $slug => $bundle) {
  $lv = isset($bundle['lv']) ? $bundle['lv'] : reset($bundle);
  $page = ba_ensure($serviceParent, 'service', $slug, isset($lv['title']) ? $lv['title'] : $slug);
  foreach ($locales as $locale) {
    $language = ba_lang($locale);
    $data = isset($bundle[$locale]) ? $bundle[$locale] : array();
    if (!$language || !$data) continue;
    ba_set($page, 'title', $language, isset($data['title']) ? $data['title'] : '');
    ba_set($page, 'headline', $language, isset($data['heading']) ? $data['heading'] : '');
    ba_set($page, 'summary', $language, isset($data['summary']) ? $data['summary'] : '');
    ba_set($page, 'body', $language, ba_join_paragraphs(isset($data['paragraphs']) ? $data['paragraphs'] : array()));
    ba_set($page, 'bullets_intro', $language, isset($data['bullets_intro']) ? $data['bullets_intro'] : '');
    ba_set($page, 'highlights', $language, ba_join_lines(isset($data['bullets']) ? $data['bullets'] : array()));
    ba_set($page, 'closing', $language, ba_join_paragraphs(isset($data['closing_paragraphs']) ? $data['closing_paragraphs'] : array()));
    ba_set($page, 'cta_text', $language, isset($data['cta_text']) ? $data['cta_text'] : '');
    if ($locale === 'lv') {
      $page->sort = isset($data['order']) ? (int) $data['order'] : 0;
      ba_image($repo, $page, 'card_image', isset($data['image']) ? $data['image'] : '', 'image_path');
      ba_image($repo, $page, 'hero_image', isset($data['hero_image']) ? $data['hero_image'] : '', 'hero_path');
    }
  }
  $page->save();
  ba_out("  $slug");
}

ba_out('Announcements');
$newsParent = $containerPages['zinojumi'];
foreach (ba_load_dir($contentDir, 'articles') as $slug => $bundle) {
  $lv = isset($bundle['lv']) ? $bundle['lv'] : reset($bundle);
  $page = ba_ensure($newsParent, 'article', $slug, isset($lv['title']) ? $lv['title'] : $slug);
  foreach ($locales as $locale) {
    $language = ba_lang($locale);
    $data = isset($bundle[$locale]) ? $bundle[$locale] : array();
    if (!$language || !$data) continue;
    ba_set($page, 'title', $language, isset($data['title']) ? $data['title'] : '');
    ba_set($page, 'summary', $language, isset($data['summary']) ? $data['summary'] : '');
    ba_set($page, 'body', $language, ba_join_paragraphs(isset($data['paragraphs']) ? $data['paragraphs'] : array()));
    ba_set($page, 'market_label', $language, isset($data['market']) ? $data['market'] : '');
    if ($locale === 'lv') {
      if (!empty($data['date'])) $page->article_date = strtotime($data['date']);
      $page->sort = isset($data['order']) ? (int) $data['order'] : 0;
      ba_image($repo, $page, 'card_image', isset($data['image']) ? $data['image'] : '', 'image_path');
      ba_image($repo, $page, 'hero_image', isset($data['hero_image']) ? $data['hero_image'] : '', 'hero_path');
    }
  }
  $page->save();
  ba_out("  $slug");
}

ba_out('Industries');
$industryParent = $containerPages['industries'];
$industryPages = array();
$industryBundles = ba_load_dir($contentDir, 'industry-markets');
foreach ($industryBundles as $slug => $bundle) {
  $lv = isset($bundle['lv']) ? $bundle['lv'] : reset($bundle);
  $page = ba_ensure($industryParent, 'industry', $slug, isset($lv['title']) ? $lv['title'] : $slug);
  foreach ($locales as $locale) {
    $language = ba_lang($locale);
    $data = isset($bundle[$locale]) ? $bundle[$locale] : array();
    if (!$language || !$data) continue;
    ba_set($page, 'title', $language, isset($data['title']) ? $data['title'] : '');
    ba_set($page, 'summary', $language, isset($data['summary']) ? $data['summary'] : '');
    ba_set($page, 'body', $language, ba_join_paragraphs(isset($data['paragraphs']) ? $data['paragraphs'] : array()));
    ba_set($page, 'cta_text', $language, isset($data['cta_text']) ? $data['cta_text'] : '');
    if ($locale === 'lv') {
      $page->sort = isset($data['order']) ? (int) $data['order'] : 0;
      ba_image($repo, $page, 'card_image', isset($data['image']) ? $data['image'] : '', 'image_path');
      ba_image($repo, $page, 'hero_image', isset($data['hero_image']) ? $data['hero_image'] : '', 'hero_path');
    }
  }
  $page->save();
  $industryPages[$slug] = $page;
}
foreach ($industryBundles as $slug => $bundle) {
  $page = $industryPages[$slug];
  $page->of(false);
  $lv = isset($bundle['lv']) ? $bundle['lv'] : array();
  $parentSlug = isset($lv['parent']) ? $lv['parent'] : '';
  $page->parent_market = ($parentSlug && isset($industryPages[$parentSlug])) ? $industryPages[$parentSlug] : null;
  $recommended = array();
  if (!empty($lv['recommended_products']) && is_array($lv['recommended_products'])) {
    foreach ($lv['recommended_products'] as $productSlug) {
      if (isset($productPages[$productSlug])) $recommended[] = $productPages[$productSlug];
    }
  }
  $page->recommended->removeAll();
  foreach ($recommended as $product) $page->recommended->add($product);
  $page->save();
  ba_out("  $slug");
}

ba_out('Contact');
$contact = $containerPages['contact'];
$contactFiles = array();
foreach ($locales as $locale) $contactFiles[$locale] = ba_load_file($contentDir, $locale, 'pages/contact.json');
foreach ($locales as $locale) {
  $language = ba_lang($locale);
  $data = $contactFiles[$locale];
  if (!$language || !$data) continue;
  ba_set($contact, 'form_heading', $language, isset($data['form_heading']) ? $data['form_heading'] : '');
  ba_set($contact, 'form_intro', $language, isset($data['form_intro']) ? $data['form_intro'] : '');
  $form = isset($data['form_labels']) ? $data['form_labels'] : array();
  $formMap = array(
    'legal_entity' => 'fl_legal_entity',
    'legal_entity_hint' => 'fl_legal_hint',
    'company_name' => 'fl_company_name',
    'reg_number' => 'fl_reg_number',
    'vat_number' => 'fl_vat_number',
    'name' => 'fl_name',
    'name_hint' => 'fl_name_hint',
    'email' => 'fl_email',
    'email_hint' => 'fl_email_hint',
    'message' => 'fl_message',
    'message_placeholder' => 'fl_message_placeholder',
    'submit' => 'fl_submit',
  );
  foreach ($formMap as $key => $field) {
    if (isset($form[$key])) ba_set($contact, $field, $language, $form[$key]);
  }
  $info = isset($data['contact_info']) ? $data['contact_info'] : array();
  foreach (array('heading' => 'ci_heading', 'email_label' => 'ci_email_label', 'email' => 'ci_email', 'phone_label' => 'ci_phone_label', 'phone' => 'ci_phone') as $key => $field) {
    if (isset($info[$key])) ba_set($contact, $field, $language, $info[$key]);
  }
  $company = isset($data['company']) ? $data['company'] : array();
  foreach (array('heading' => 'co_heading', 'name' => 'co_name', 'reg_number_label' => 'co_reg_label', 'reg_number' => 'co_reg', 'vat_number_label' => 'co_vat_label', 'vat_number' => 'co_vat') as $key => $field) {
    if (isset($company[$key])) ba_set($contact, $field, $language, $company[$key]);
  }
  $legal = isset($data['legal_address']) ? $data['legal_address'] : array();
  if (isset($legal['heading'])) ba_set($contact, 'la_heading', $language, $legal['heading']);
  if (isset($legal['address'])) ba_set($contact, 'la_address', $language, $legal['address']);
  $office = isset($data['office']) ? $data['office'] : array();
  if (isset($office['heading'])) ba_set($contact, 'of_heading', $language, $office['heading']);
  if (isset($office['address'])) ba_set($contact, 'of_address', $language, $office['address']);
  $bank = isset($data['bank']) ? $data['bank'] : array();
  foreach (array('heading' => 'bk_heading', 'name' => 'bk_name', 'swift_label' => 'bk_swift_label', 'swift' => 'bk_swift', 'account_label' => 'bk_account_label', 'account' => 'bk_account') as $key => $field) {
    if (isset($bank[$key])) ba_set($contact, $field, $language, $bank[$key]);
  }
}
$contact->save();

$database = wire('database');
foreach (wire('languages') as $language) {
  if (!$language->id || $language->isDefault()) continue;
  $column = 'name' . (int) $language->id;
  $database->exec("UPDATE pages SET `$column`=`name` WHERE `$column` IS NULL OR `$column`=''");
}

$home = wire('pages')->get('/');
$home->of(false);
$home->name = 'lv';
ba_activate($home);
$home->save();

$names = array();
foreach (array('en' => 'en', 'ru' => 'ru') as $code => $segment) {
  $language = ba_lang($code);
  if ($language) $names[] = '`name' . (int) $language->id . '`=' . $database->quote($segment);
}
if ($names) $database->exec('UPDATE pages SET ' . implode(', ', $names) . ' WHERE id=1');

$languageNames = wire('modules')->getModuleConfigData('LanguageSupportPageNames');
$languageNames['useHomeSegment'] = 1;
wire('modules')->saveConfig('LanguageSupportPageNames', $languageNames);

ba_out('BasicAir content is ready.');
