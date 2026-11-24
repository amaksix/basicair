<?php namespace ProcessWire;

$settings = ba_settings();
$home = ba_home();
$baTitle = $page->title;
$baCurrent = 'services';
$baDescription = $page->summary;
$image = ba_picture($page, 'hero_image', 'hero_path');

ob_start();
echo ba_banner($page->headline ?: $page->title, $image, array(
  array($settings->nav_home, ba_url($home)),
  array($page->headline ?: $page->title, ''),
));
?>
<section class="flat-spacing-2">
    <div class="container">
        <div class="row justify-center">
            <div class="col-lg-8">
                <div class="heading-section text-center spacing-2">
                    <h1 class="wow fadeInUp"><?= ba_e($page->main_heading ?: $page->title) ?></h1>
                    <?php if ($page->summary): ?><p class="text-body-1 text_secondary"><?= ba_e($page->summary) ?></p><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="row g-4">
            <?php foreach ($page->children('sort=sort') as $service):
              $card = ba_picture($service, 'card_image', 'image_path');
            ?>
            <div class="col-md-6">
                <a href="<?= ba_e(ba_url($service)) ?>" class="service-card hover-img wow fadeInUp">
                    <div class="image img-style">
                        <?php if ($card): ?><img class="lazyload" src="<?= ba_e($card) ?>" data-src="<?= ba_e($card) ?>" alt="<?= ba_e($service->title) ?>"><?php endif; ?>
                    </div>
                    <div class="service-card-content">
                        <h3 class="mb_12"><?= ba_e($service->title) ?></h3>
                        <p class="text-body-1 text_secondary mb_16"><?= ba_e($service->summary) ?></p>
                        <span class="tf-btn btn-onsurface"><?= ba_e($page->learn_more ?: $settings->label_learn_more) ?> <i class="icon-arrow-up-right"></i></span>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php
$baBody = ob_get_clean();
include __DIR__ . '/_layout.php';
