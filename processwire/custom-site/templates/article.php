<?php namespace ProcessWire;

$settings = ba_settings();
$home = ba_home();
$news = $page->parent;
$baTitle = $page->title . ' — BasicAir';
$baCurrent = 'article';
$baDescription = $page->summary;
$image = ba_picture($page, 'hero_image', 'hero_path');
if (!$image) $image = ba_picture($news, 'hero_image', 'hero_path');

ob_start();
echo ba_banner($page->title, $image, array(
  array($settings->nav_home, ba_url($home)),
  array($news->headline ?: $news->title, ba_url($news)),
  array($page->title, ''),
));
?>
<section class="flat-spacing-2">
    <div class="container">
        <div class="row justify-center">
            <div class="col-lg-8">
                <div class="heading-section spacing-2">
                    <?php if ($page->market_label || $page->article_date): ?>
                    <p class="text-body-1 text_secondary mb_12">
                        <?php if ($page->market_label): ?><span><?= ba_e($page->market_label) ?></span><?php endif; ?>
                        <?php if ($page->market_label && $page->article_date): ?> · <?php endif; ?>
                        <?php if ($page->article_date): ?><span><?= ba_e(ba_date($page->article_date)) ?></span><?php endif; ?>
                    </p>
                    <?php endif; ?>
                    <h1><?= ba_e($page->title) ?></h1>
                    <?php if ($page->summary): ?><p class="text-body-1 text_secondary mb_16"><?= ba_e($page->summary) ?></p><?php endif; ?>
                    <?php foreach (ba_paragraphs($page->body) as $paragraph): ?>
                    <p class="text-body-1 text_secondary mb_16"><?= ba_e($paragraph) ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
$baBody = ob_get_clean();
include __DIR__ . '/_layout.php';
