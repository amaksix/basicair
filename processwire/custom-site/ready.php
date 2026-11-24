<?php namespace ProcessWire;

/**
 * Latvian labels for the admin, and a control to switch that language.
 * Latvian is the site default, so choosing it shows the Latvian admin.
 * English and Russian keep the original English admin labels and change
 * which content language is being edited.
 */

if (!$config->admin) return;

require __DIR__ . '/admin-lv.php';

$choices = array(
  'lv' => 'Latviešu',
  'en' => 'English',
  'ru' => 'Русский',
);

$posted = $input->post->text('ba_admin_lang');
if ($posted && isset($choices[$posted]) && $user->isLoggedin() && $session->CSRF->validate()) {
  $language = $posted === 'lv' ? $languages->getDefault() : $languages->get($posted);
  if ($language && $language->id) {
    $user->setAndSave('language', $language);
    $adminTheme = $modules->get('AdminThemeUikit');
    if ($adminTheme) {
      $session->removeFor($adminTheme, 'prnav');
      $session->removeFor($adminTheme, 'sidenav');
    }
    $session->redirect($page->url);
  }
}

$active = $user->language && $user->language->id && !$user->language->isDefault() ? $user->language->name : 'lv';
if ($active === 'lv') __(true, 'translations', ba_lv_map());

if (!$user->isLoggedin()) return;

$adminTheme = $modules->get('AdminThemeUikit');
$stamp = $active . ':lv4';
if ($session->get('baAdminLangCache') !== $stamp) {
  if ($adminTheme) {
    $session->removeFor($adminTheme, 'prnav');
    $session->removeFor($adminTheme, 'sidenav');
  }
  $session->set('baAdminLangCache', $stamp);
}

$label = $active === 'ru' ? 'Интерфейс' : ($active === 'en' ? 'Interface' : 'Saskarne');
$options = '';
foreach ($choices as $code => $title) {
  $selected = $code === $active ? ' selected' : '';
  $options .= '<option value="' . $sanitizer->entities($code) . '"' . $selected . '>' . $sanitizer->entities($title) . '</option>';
}

$markup = '<form method="post" id="ba-admin-lang-form" action="' . $sanitizer->entities($page->url) . '" class="uk-form-horizontal" style="display:flex;justify-content:flex-end;align-items:center;gap:8px;margin:0;padding:8px 20px;">'
  . $session->CSRF->renderInput()
  . '<label for="ba-admin-lang" style="margin:0;font-size:13px;">' . $sanitizer->entities($label) . '</label>'
  . '<select id="ba-admin-lang" name="ba_admin_lang" class="uk-select uk-form-small" style="width:auto;min-width:9em;" onchange="this.form.submit()">'
  . $options
  . '</select></form>';

if ($adminTheme) $adminTheme->addExtraMarkup('masthead', $markup);
