<?php namespace ProcessWire;

$settings = ba_settings();
$home = ba_home();
$services = $page->parent;
$baTitle = ($page->headline ?: $page->title) . ' — BasicAir';
$baCurrent = 'service';
$baDescription = $page->summary;
$image = ba_picture($page, 'hero_image', 'hero_path');

ob_start();
echo ba_banner($page->title, $image, array(
  array($settings->nav_home, ba_url($home)),
  array($services->headline ?: $services->title, ba_url($services)),
  array($page->title, ''),
));
?>
<section class="flat-spacing-2">
    <div class="container">
        <div class="row justify-center">
            <div class="col-lg-8">
                <div class="heading-section spacing-2">
                    <h1 class="wow fadeInUp"><?= ba_e($page->headline ?: $page->title) ?></h1>
                    <?php foreach (ba_paragraphs($page->body) as $paragraph): ?>
                    <p class="text-body-1 text_secondary mb_16"><?= ba_e($paragraph) ?></p>
                    <?php endforeach; ?>
                    <?php if ($page->bullets_intro): ?><p class="text-body-1 text_secondary mb_16"><?= ba_e($page->bullets_intro) ?></p><?php endif; ?>
                    <?php if (count(ba_lines($page->highlights))): ?>
                    <ul class="list-text type-disc text-body-1 text_secondary mb_16">
                        <?php foreach (ba_lines($page->highlights) as $line): ?><li><?= ba_e($line) ?></li><?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                    <?php foreach (ba_paragraphs($page->closing) as $paragraph): ?>
                    <p class="text-body-1 text_secondary mb_16"><?= ba_e($paragraph) ?></p>
                    <?php endforeach; ?>
                    <div class="mt_30">
                        <a href="<?= ba_e(ba_url(wire('pages')->get('template=contact'))) ?>" class="tf-btn btn-onsurface"><?= ba_e($page->cta_text) ?> <i class="icon-arrow-up-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
$baBody = ob_get_clean();
include __DIR__ . '/_layout.php';
