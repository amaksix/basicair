<?php namespace ProcessWire;

$settings = ba_settings();
$home = ba_home();
$baTitle = $page->title;
$baCurrent = 'industries';
$baDescription = $page->summary;
$image = ba_picture($page, 'hero_image', 'hero_path');
$markets = wire('pages')->find('template=industry, parent_market=0, sort=sort');

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
                    <?php if ($page->summary): ?><p class="text-body-1 text_secondary wow fadeInUp"><?= ba_e($page->summary) ?></p><?php endif; ?>
                </div>
            </div>
        </div>
        <div class="row g-4 justify-center">
            <?php $index = 0; foreach ($markets as $market):
              $card = ba_picture($market, 'card_image', 'image_path');
            ?>
            <div class="col-md-6 col-lg-5">
                <div class="collection-position hover-img style-4 wow fadeInUp" data-wow-delay="<?= ba_e(($index * 0.1) . 's') ?>">
                    <a href="<?= ba_e(ba_url($market)) ?>" class="img-style">
                        <?php if ($card): ?><img class="lazyload" src="<?= ba_e($card) ?>" data-src="<?= ba_e($card) ?>" alt="<?= ba_e($market->title) ?>"><?php endif; ?>
                    </a>
                    <div class="content cls-content">
                        <h4><a href="<?= ba_e(ba_url($market)) ?>" class="link"><?= ba_e($market->title) ?></a></h4>
                        <?php if ($market->summary): ?><h6><?= ba_e($market->summary) ?></h6><?php endif; ?>
                    </div>
                </div>
            </div>
            <?php $index++; endforeach; ?>
        </div>
    </div>
</section>
<?php
$baBody = ob_get_clean();
include __DIR__ . '/_layout.php';
