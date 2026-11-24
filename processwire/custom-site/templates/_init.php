<?php namespace ProcessWire;

/**
 * Shared helpers for the public site. Loaded before every template.
 */

function ba_e($value) {
  return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function ba_lang() {
  $language = wire('user')->language;
  $name = $language ? $language->name : 'lv';
  return $name === 'default' ? 'lv' : $name;
}

function ba_settings() {
  return wire('pages')->get('template=settings, include=all');
}

function ba_home() {
  return wire('pages')->get('/');
}

function ba_paragraphs($text) {
  $text = trim((string) $text);
  if ($text === '') return array();
  $parts = preg_split("/\n\s*\n/", $text);
  $clean = array();
  foreach ($parts as $part) {
    $part = trim($part);
    if ($part !== '') $clean[] = $part;
  }
  return $clean;
}

function ba_lines($text) {
  $lines = preg_split("/\r\n|\n|\r/", (string) $text);
  $clean = array();
  foreach ($lines as $line) {
    $line = trim($line);
    if ($line !== '') $clean[] = $line;
  }
  return $clean;
}

function ba_picture($page, $imageField, $pathField = 'image_path') {
  if (!$page || !$page->id) return '';
  $image = $page->get($imageField);
  if ($image instanceof Pageimage && $image->url) return $image->url;
  if ($image instanceof Pageimages && count($image)) return $image->first()->url;
  if ($pathField) return (string) $page->get($pathField);
  return '';
}

function ba_url($page) {
  if (!$page || !$page->id) return '#';
  return $page->localUrl(wire('user')->language);
}

function ba_date($value) {
  if (!$value) return '';
  $timestamp = is_numeric($value) ? (int) $value : strtotime((string) $value);
  if (!$timestamp) return '';
  return date('j.m.Y', $timestamp);
}

function ba_nl2br($value) {
  return nl2br(ba_e($value), false);
}

function ba_active(array $templates) {
  return in_array(wire('page')->template->name, $templates, true);
}

function ba_language_select($current) {
  $page = wire('page');
  $options = array('lv' => 'Latviešu', 'en' => 'English', 'ru' => 'Русский');
  $active = ba_lang();
  $attrs = ' data-current-page="' . ba_e($current) . '"';
  if ($current === 'product') $attrs .= ' data-product-slug="' . ba_e($page->name) . '"';
  if ($current === 'service') $attrs .= ' data-service-slug="' . ba_e($page->name) . '"';
  if ($current === 'product-category') $attrs .= ' data-category-slug="' . ba_e($page->name) . '"';
  if ($current === 'article') $attrs .= ' data-article-slug="' . ba_e($page->name) . '"';
  if ($current === 'industry-market') $attrs .= ' data-market-slug="' . ba_e($page->name) . '"';

  $html = '<select class="image-select center style-default type-languages"' . $attrs . '>';
  foreach ($options as $code => $label) {
    $selected = $code === $active ? ' selected' : '';
    $html .= '<option value="' . ba_e($code) . '"' . $selected . '>' . ba_e($label) . '</option>';
  }
  $html .= '</select>';
  return $html;
}

function ba_banner($title, $image, array $crumbs) {
  $html = '<div class="page-title relative">';
  $html .= '<div class="paralaximg" data-parallax="scroll" data-image-src="' . ba_e($image) . '"></div>';
  $html .= '<div class="content"><div class="container"><div class="row"><div class="col-12">';
  $html .= '<h3 class="title">' . ba_e($title) . '</h3><ul class="breadcrumb">';
  foreach ($crumbs as $crumb) {
    $label = ba_e($crumb[0]);
    if (!empty($crumb[1])) {
      $html .= '<li><a href="' . ba_e($crumb[1]) . '">' . $label . '</a></li>';
    } else {
      $html .= '<li>' . $label . '</li>';
    }
  }
  $html .= '</ul></div></div></div></div></div>';
  return $html;
}

function ba_product_card($product, $delay = '0s') {
  $settings = ba_settings();
  $image = ba_picture($product, 'card_image', 'image_path');
  $hover = ba_picture($product, 'hover_image', 'hover_path');
  $url = ba_url($product);
  $contact = ba_url(wire('pages')->get('template=contact'));
  $quote = $settings->id ? $settings->label_request_quote : '';

  $html = '<div class="card-product style-1 wow fadeInUp" data-wow-delay="' . ba_e($delay) . '">';
  $html .= '<div class="card-product-wrapper"><a href="' . ba_e($url) . '" class="image-wrap">';
  $html .= '<img class="lazyload img-product" src="' . ba_e($image) . '" data-src="' . ba_e($image) . '" alt="' . ba_e($product->title) . '">';
  if ($hover) {
    $html .= '<img class="lazyload img-hover" src="' . ba_e($hover) . '" data-src="' . ba_e($hover) . '" alt="' . ba_e($product->title) . '">';
  }
  $html .= '</a><div class="list-btn-main"><a href="' . ba_e($contact) . '" class="btn-main-product">' . ba_e($quote) . '</a></div></div>';
  $html .= '<div class="card-product-info"><a href="' . ba_e($url) . '" class="text-title title link">' . ba_e($product->title) . '</a></div></div>';
  return $html;
}
