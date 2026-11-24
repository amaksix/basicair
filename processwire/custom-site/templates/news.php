<?php namespace ProcessWire;

$settings = ba_settings();
$home = ba_home();
$baTitle = $page->title;
$baCurrent = 'news';
$baDescription = $page->summary;
$image = ba_picture($page, 'hero_image', 'hero_path');
$articles = wire('pages')->find('template=article, sort=-article_date');

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
        <?php if (!count($articles)): ?>
        <p class="text-center text_secondary"><?= ba_e($page->empty_state) ?></p>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($articles as $article):
              $card = ba_picture($article, 'card_image', 'image_path');
            ?>
            <div class="col-md-6 col-lg-4">
                <a href="<?= ba_e(ba_url($article)) ?>" class="service-card hover-img wow fadeInUp">
                    <div class="image img-style">
                        <?php if ($card): ?><img class="lazyload" src="<?= ba_e($card) ?>" data-src="<?= ba_e($card) ?>" alt="<?= ba_e($article->title) ?>"><?php endif; ?>
                    </div>
                    <div class="service-card-content">
                        <p class="text_secondary mb_8"><?= ba_e(ba_date($article->article_date)) ?><?php if ($article->market_label): ?> · <?= ba_e($article->market_label) ?><?php endif; ?></p>
                        <h3 class="mb_12"><?= ba_e($article->title) ?></h3>
                        <p class="text-body-1 text_secondary mb_16"><?= ba_e($article->summary) ?></p>
                        <span class="tf-btn btn-onsurface"><?= ba_e($page->learn_more ?: $settings->label_learn_more) ?> <i class="icon-arrow-up-right"></i></span>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php
$baBody = ob_get_clean();
include __DIR__ . '/_layout.php';
