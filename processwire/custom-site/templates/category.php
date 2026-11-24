<?php namespace ProcessWire;

$settings = ba_settings();
$home = ba_home();
$shop = $page->parent;
$baTitle = $page->title . ' — BasicAir';
$baCurrent = 'product-category';
$baDescription = $page->summary;
$image = ba_picture($page, 'hero_image', 'hero_path');
$products = wire('pages')->find("template=product, category_ref=$page, sort=sort");

ob_start();
echo ba_banner($page->title, $image, array(
  array($settings->nav_home, ba_url($home)),
  array($shop->title, ba_url($shop)),
  array($page->title, ''),
));
?>
<section class="flat-spacing-2">
    <div class="container">
        <?php if (count(ba_paragraphs($page->body))): ?>
        <div class="row justify-center mb_40">
            <div class="col-lg-10">
                <div class="heading-section spacing-2 text-center">
                    <?php foreach (ba_paragraphs($page->body) as $paragraph): ?>
                    <p class="text-body-1 text_secondary mb_16"><?= ba_e($paragraph) ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <div class="tf-grid-layout tf-col-2 lg-col-4">
            <?php $index = 0; foreach ($products as $product):
              echo ba_product_card($product, ($index * 0.05) . 's');
              $index++;
            endforeach; ?>
        </div>
        <?php if ($page->coming_soon): ?>
        <p class="text-body-default text_secondary text-center mt_30"><?= ba_e($page->coming_soon) ?></p>
        <?php endif; ?>
    </div>
</section>
<?php
$baBody = ob_get_clean();
include __DIR__ . '/_layout.php';
