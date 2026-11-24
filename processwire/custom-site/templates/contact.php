<?php namespace ProcessWire;

$settings = ba_settings();
$home = ba_home();
$baTitle = $page->title;
$baCurrent = 'contact';
$baDescription = $page->form_intro;
$image = ba_picture($page, 'hero_image', 'hero_path');

ob_start();
echo ba_banner($page->headline ?: $page->title, $image, array(
  array($settings->nav_home, ba_url($home)),
  array($page->headline ?: $page->title, ''),
));
?>
<section class="flat-spacing">
    <div class="container">
        <div class="contact-us-content">
            <div class="row">
                <div class="col-lg-6 mb-lg-30">
                    <div class="row contact-info-grid">
                        <div class="col-md-6 mb_28">
                            <h4 class="mb_20"><?= ba_e($page->ci_heading) ?></h4>
                            <div class="mb_16">
                                <h6 class="mb_4"><?= ba_e($page->ci_email_label) ?></h6>
                                <p class="text-body-default"><a href="mailto:<?= ba_e($page->ci_email) ?>"><?= ba_e($page->ci_email) ?></a></p>
                            </div>
                            <div>
                                <h6 class="mb_4"><?= ba_e($page->ci_phone_label) ?></h6>
                                <p class="text-body-default"><a href="tel:<?= ba_e(str_replace(' ', '', $page->ci_phone)) ?>"><?= ba_e($page->ci_phone) ?></a></p>
                            </div>
                        </div>
                        <div class="col-md-6 mb_28">
                            <h4 class="mb_20"><?= ba_e($page->co_heading) ?></h4>
                            <p class="text-body-default mb_8"><?= ba_e($page->co_name) ?></p>
                            <p class="text-body-default mb_4"><?= ba_e($page->co_reg_label) ?> <?= ba_e($page->co_reg) ?></p>
                            <p class="text-body-default"><?= ba_e($page->co_vat_label) ?> <?= ba_e($page->co_vat) ?></p>
                        </div>
                        <div class="col-md-6 mb_28">
                            <h4 class="mb_20"><?= ba_e($page->la_heading) ?></h4>
                            <p class="text-body-default"><?= ba_nl2br($page->la_address) ?></p>
                        </div>
                        <div class="col-md-6 mb_28">
                            <h4 class="mb_20"><?= ba_e($page->of_heading) ?></h4>
                            <p class="text-body-default"><?= ba_nl2br($page->of_address) ?></p>
                        </div>
                        <div class="col-md-6">
                            <h4 class="mb_20"><?= ba_e($page->bk_heading) ?></h4>
                            <p class="text-body-default mb_8"><?= ba_e($page->bk_name) ?></p>
                            <p class="text-body-default mb_4"><?= ba_e($page->bk_swift_label) ?> <?= ba_e($page->bk_swift) ?></p>
                            <p class="text-body-default"><?= ba_e($page->bk_account_label) ?> <?= ba_e($page->bk_account) ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 offset-lg-1">
                    <h4 class="mb_7"><?= ba_e($page->form_heading) ?></h4>
                    <p class="text_secondary mb_24"><?= ba_e($page->form_intro) ?></p>
                    <form id="contactform" action="/contact/contact-process.php" method="post" class="form-leave-comment form-contact">
                        <div class="wrap">
                            <div class="form-field mb_8">
                                <div class="tf-cart-checkbox align-items-start">
                                    <div class="tf-checkbox-wrapp">
                                        <input type="checkbox" id="is_legal_entity" name="is_legal_entity" value="1">
                                        <div><i class="icon-check"></i></div>
                                    </div>
                                    <label for="is_legal_entity">
                                        <span class="d-block text-title"><?= ba_e($page->fl_legal_entity) ?></span>
                                        <span class="d-block text_secondary field-hint"><?= ba_e($page->fl_legal_hint) ?></span>
                                    </label>
                                </div>
                            </div>
                            <div id="company-fields" class="company-fields" hidden>
                                <fieldset>
                                    <label class="field-label" for="company_name"><?= ba_e($page->fl_company_name) ?></label>
                                    <input type="text" name="company_name" id="company_name">
                                </fieldset>
                                <div class="cols">
                                    <fieldset>
                                        <label class="field-label" for="company_reg_number"><?= ba_e($page->fl_reg_number) ?></label>
                                        <input type="text" name="company_reg_number" id="company_reg_number">
                                    </fieldset>
                                    <fieldset>
                                        <label class="field-label" for="company_vat_number"><?= ba_e($page->fl_vat_number) ?></label>
                                        <input type="text" name="company_vat_number" id="company_vat_number">
                                    </fieldset>
                                </div>
                            </div>
                            <fieldset>
                                <label class="field-label" for="name"><?= ba_e($page->fl_name) ?></label>
                                <span class="field-hint"><?= ba_e($page->fl_name_hint) ?></span>
                                <input type="text" name="name" id="name" required>
                            </fieldset>
                            <fieldset>
                                <label class="field-label" for="email"><?= ba_e($page->fl_email) ?></label>
                                <span class="field-hint"><?= ba_e($page->fl_email_hint) ?></span>
                                <input type="email" name="email" id="email" required>
                            </fieldset>
                            <fieldset>
                                <label class="field-label" for="message"><?= ba_e($page->fl_message) ?></label>
                                <textarea name="message" id="message" rows="4" placeholder="<?= ba_e($page->fl_message_placeholder) ?>" required></textarea>
                            </fieldset>
                        </div>
                        <div class="button-submit send-wrap">
                            <button class="tf-btn btn-onsurface" type="submit"><?= ba_e($page->fl_submit) ?> <i class="icon-arrow-up-right"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
$baBody = ob_get_clean();
include __DIR__ . '/_layout.php';
