<?php namespace ProcessWire;

$settings = ba_settings();
$home = ba_home();
$baTitle = $page->title;
$baCurrent = 'shop';
$baDescription = $page->summary;
$image = ba_picture($page, 'hero_image', 'hero_path');
if (!$image) $image = '/images/page-title/page-title-3.jpg';

ob_start();
echo ba_banner($page->headline ?: $page->title, $image, array(
  array($settings->nav_home, ba_url($home)),
  array($page->headline ?: $page->title, ''),
));
?>
<section class="flat-spacing-2">
    <div class="container">
        <?php if ($page->summary): ?>
        <p class="text-body-default text_secondary text-center mb_30"><?= ba_e($page->summary) ?></p>
        <?php endif; ?>
        <div class="row g-4 category-cards">
            <?php $index = 0; foreach ($page->children('sort=sort') as $category):
              $card = ba_picture($category, 'card_image', 'image_path');
            ?>
            <div class="col-md-6 col-lg-3">
                <a href="<?= ba_e(ba_url($category)) ?>" class="service-card hover-img wow fadeInUp" data-wow-delay="<?= ba_e(($index * 0.05) . 's') ?>">
                    <div class="image img-style">
                        <?php if ($card): ?><img class="lazyload" src="<?= ba_e($card) ?>" data-src="<?= ba_e($card) ?>" alt="<?= ba_e($category->title) ?>"><?php endif; ?>
                    </div>
                    <div class="service-card-content">
                        <h3 class="mb_12"><?= ba_e($category->title) ?></h3>
                        <p class="text-body-1 text_secondary mb_16"><?= ba_e($category->summary) ?></p>
                        <span class="tf-btn btn-onsurface"><?= ba_e($category->title) ?> <i class="icon-arrow-up-right"></i></span>
                    </div>
                </a>
            </div>
            <?php $index++; endforeach; ?>
        </div>
    </div>
</section>
<?php
$baBody = ob_get_clean();
include __DIR__ . '/_layout.php';
