<?php namespace ProcessWire;

$settings = ba_settings();
$home = ba_home();
$contact = wire('pages')->get('template=contact');
$baTitle = $page->title . ' — BasicAir';
$baCurrent = 'product';
$baDescription = $page->summary;
$gallery = array();
foreach ($page->gallery as $image) {
  if ($image->url) $gallery[] = $image->url;
}
if (!count($gallery)) {
  $main = ba_picture($page, 'card_image', 'image_path');
  if ($main) $gallery[] = $main;
}

ob_start();
?>
<section class="flat-spacing-2">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <div class="tf-product-media-wrap sticky-top" data-product-gallery>
                    <?php if (count($gallery)): ?>
                    <div class="item">
                        <img class="lazyload w-100 radius-4 product-main-image" id="product-main-image" src="<?= ba_e($gallery[0]) ?>" alt="<?= ba_e($page->title) ?>">
                    </div>
                    <?php if (count($gallery) > 1): ?>
                    <div class="product-gallery-thumbs mt_20 d-flex flex-row flex-wrap align-items-start gap-8" style="width: 100%;">
                        <?php foreach ($gallery as $index => $image): ?>
                        <button type="button" class="product-gallery-thumb border-0 p-0 bg-transparent<?= $index === 0 ? ' is-active' : '' ?>" data-gallery-src="<?= ba_e($image) ?>" style="cursor: pointer; width: 80px;">
                            <img class="radius-4 w-100" src="<?= ba_e($image) ?>" alt="<?= ba_e($page->title) ?>"<?= $index === 0 ? ' style="outline: 2px solid currentColor; outline-offset: 2px;"' : '' ?>>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <?php else: ?>
                    <div class="item product-image-placeholder d-flex align-items-center justify-content-center radius-4 bg-surface" style="min-height: 360px;">
                        <span class="text_secondary text-body-default"><?= ba_e($page->title) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="tf-product-info-wrap tf-product-info-list">
                    <h3 class="mb_12"><?= ba_e($page->title) ?></h3>
                    <?php if ($page->summary): ?>
                    <p class="text_secondary mb_20" style="white-space: pre-line;"><?= ba_e($page->summary) ?></p>
                    <?php endif; ?>
                    <?php if ($page->price): ?>
                    <p class="text-title mb_8"><?= ba_e($page->price) ?></p>
                    <?php if ($page->price_note): ?><p class="text_secondary mb_20"><?= ba_e($page->price_note) ?></p><?php endif; ?>
                    <?php endif; ?>
                    <?php if (count(ba_lines($page->highlights))): ?>
                    <ul class="list-text type-disc gap-6 mb_24">
                        <?php foreach (ba_lines($page->highlights) as $line): ?>
                        <li class="text-body-default text_secondary mb_8"><?= ba_e($line) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                    <?php foreach (ba_paragraphs($page->body) as $paragraph): ?>
                    <p class="text-body-default text_secondary mb_16"><?= ba_e($paragraph) ?></p>
                    <?php endforeach; ?>
                    <a href="<?= ba_e(ba_url($contact)) ?>" class="tf-btn btn-onsurface"><?= ba_e($page->price ? $settings->label_order : $settings->label_request_quote) ?> <i class="icon-arrow-up-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php foreach ($page->sections as $section): ?>
<section class="flat-spacing-2 pt-0">
    <div class="container">
        <div class="heading-section mb_24"><h4 class="border-bottom pb_10"><?= ba_e($section->heading) ?></h4></div>
        <div class="row"><div class="col-lg-10">
            <?php foreach (ba_paragraphs($section->body) as $paragraph): ?>
            <p class="text-body-default text_secondary mb_16"><?= ba_e($paragraph) ?></p>
            <?php endforeach; ?>
            <?php if (count(ba_lines($section->highlights))): ?>
            <ul class="list-text type-disc gap-6 mb_16">
                <?php foreach (ba_lines($section->highlights) as $line): ?>
                <li class="text-body-default text_secondary mb_8"><?= ba_e($line) ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
            <?php foreach (ba_paragraphs($section->closing) as $paragraph): ?>
            <p class="text-body-default text_secondary mb_16"><?= ba_e($paragraph) ?></p>
            <?php endforeach; ?>
        </div></div>
    </div>
</section>
<?php endforeach; ?>

<?php if (count(ba_lines($page->benefits))): ?>
<section class="flat-spacing-2 pt-0">
    <div class="container">
        <div class="heading-section mb_24"><h4 class="border-bottom pb_10"><?= ba_e($page->benefits_title ?: $settings->label_benefits) ?></h4></div>
        <div class="row"><div class="col-lg-10"><ul class="list-text type-disc gap-6 mb-0">
            <?php foreach (ba_lines($page->benefits) as $line): ?>
            <li class="text-body-default text_secondary mb_8"><?= ba_e($line) ?></li>
            <?php endforeach; ?>
        </ul></div></div>
    </div>
</section>
<?php endif; ?>

<?php
$specBlocks = array(
  array($page->specs_title ?: $settings->label_technical_specs, $page->specs),
  array($page->device_title ?: $settings->label_device_specs, $page->device_specs),
);
foreach ($specBlocks as $block):
  if (!count($block[1])) continue;
?>
<section class="flat-spacing-2 pt-0">
    <div class="container">
        <div class="heading-section mb_24"><h4 class="border-bottom pb_10"><?= ba_e($block[0]) ?></h4></div>
        <div class="row"><div class="col-lg-10"><ul class="list-text type-disc gap-6 mb-0">
            <?php foreach ($block[1] as $spec): ?>
            <li class="text-body-default mb_8"><span class="text-title"><?= ba_e($spec->spec_label) ?>:</span> <span class="text_secondary"><?= ba_e($spec->spec_value) ?></span></li>
            <?php endforeach; ?>
        </ul></div></div>
    </div>
</section>
<?php endforeach; ?>

<?php
$headers = ba_lines($page->table_headers);
if (count($headers) && count($page->table_rows)):
?>
<section class="flat-spacing-2 pt-0">
    <div class="container">
        <div class="heading-section mb_24"><h4 class="border-bottom pb_10"><?= ba_e($page->table_title ?: $settings->label_performance) ?></h4></div>
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead><tr><?php foreach ($headers as $header): ?><th scope="col" class="text-title"><?= ba_e($header) ?></th><?php endforeach; ?></tr></thead>
                <tbody>
                    <?php foreach ($page->table_rows as $row): ?>
                    <tr><?php foreach (ba_lines($row->cell_text) as $cell): ?><td class="text-body-default text_secondary"><?= ba_e($cell) ?></td><?php endforeach; ?></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
$related = $page->category_ref->id
  ? wire('pages')->find("template=product, category_ref={$page->category_ref->id}, id!=$page->id, sort=sort, limit=4")
  : wire('pages')->find("template=product, featured=1, id!=$page->id, limit=4");
if (count($related)):
?>
<section class="flat-spacing-2 pt-0">
    <div class="container">
        <div class="heading-section"><h3><?= ba_e($settings->label_related) ?></h3></div>
        <div class="tf-grid-layout tf-col-2 lg-col-4">
            <?php $index = 0; foreach ($related as $product):
              echo ba_product_card($product, ($index * 0.1) . 's');
              $index++;
            endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
<?php
$baBody = ob_get_clean();
include __DIR__ . '/_layout.php';
