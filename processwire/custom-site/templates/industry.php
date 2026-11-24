<?php namespace ProcessWire;

$settings = ba_settings();
$home = ba_home();
$industries = wire('pages')->get('template=industries');
$parentMarket = $page->parent_market;
$children = wire('pages')->find("template=industry, parent_market=$page, sort=sort");
$baTitle = $page->title . ' — BasicAir';
$baCurrent = 'industry-market';
$baDescription = $page->summary;
$image = ba_picture($page, 'hero_image', 'hero_path');
if (!$image) $image = ba_picture($industries, 'hero_image', 'hero_path');

$crumbs = array(
  array($settings->nav_home, ba_url($home)),
  array($industries->headline ?: $industries->title, ba_url($industries)),
);
if ($parentMarket->id) $crumbs[] = array($parentMarket->title, ba_url($parentMarket));
$crumbs[] = array($page->title, '');

ob_start();
echo ba_banner($page->title, $image, $crumbs);
?>
<section class="flat-spacing-2">
    <div class="container">
        <div class="row justify-center mb_40">
            <div class="col-lg-8">
                <div class="heading-section text-center spacing-2">
                    <h1 class="wow fadeInUp"><?= ba_e($page->title) ?></h1>
                    <?php if ($page->summary): ?><p class="text-body-1 text_secondary wow fadeInUp"><?= ba_e($page->summary) ?></p><?php endif; ?>
                    <?php foreach (ba_paragraphs($page->body) as $paragraph): ?>
                    <p class="text-body-1 text_secondary mb_16"><?= ba_e($paragraph) ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php if (count($children)): ?>
        <div class="row g-4">
            <?php $index = 0; foreach ($children as $child):
              $card = ba_picture($child, 'card_image', 'image_path');
            ?>
            <div class="col-sm-6 col-lg-4">
                <div class="collection-position hover-img style-4 wow fadeInUp" data-wow-delay="<?= ba_e(($index * 0.05) . 's') ?>">
                    <a href="<?= ba_e(ba_url($child)) ?>" class="img-style">
                        <?php if ($card): ?><img class="lazyload" src="<?= ba_e($card) ?>" data-src="<?= ba_e($card) ?>" alt="<?= ba_e($child->title) ?>"><?php endif; ?>
                    </a>
                    <div class="content cls-content">
                        <h4><a href="<?= ba_e(ba_url($child)) ?>" class="link"><?= ba_e($child->title) ?></a></h4>
                    </div>
                </div>
            </div>
            <?php $index++; endforeach; ?>
        </div>
        <?php else: ?>
        <div class="row justify-center">
            <div class="col-lg-8 text-center">
                <a href="<?= ba_e(ba_url(wire('pages')->get('template=contact'))) ?>" class="tf-btn btn-onsurface"><?= ba_e($page->cta_text ?: $settings->label_learn_more) ?> <i class="icon-arrow-up-right"></i></a>
            </div>
        </div>
        <?php endif; ?>

        <?php if (count($page->recommended)): ?>
        <div class="heading-section text-center mt_60">
            <h3><?= ba_e($industries->recommended_heading ?: $settings->label_related) ?></h3>
        </div>
        <div class="tf-grid-layout tf-col-2 lg-col-4">
            <?php $index = 0; foreach ($page->recommended as $product):
              echo ba_product_card($product, ($index * 0.05) . 's');
              $index++;
            endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php
$baBody = ob_get_clean();
include __DIR__ . '/_layout.php';
