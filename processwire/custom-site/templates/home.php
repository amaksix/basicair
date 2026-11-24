<?php namespace ProcessWire;

$settings = ba_settings();
$baTitle = $page->title;
$baCurrent = 'index';
$baDescription = $settings->site_tagline;

ob_start();
?>
<div class="tf-slideshow style-default slider-nav-sw slider-effect-fade efect-2">
    <div dir="ltr" class="swiper tf-sw-slideshow" data-preview="1.428" data-tablet="1.2" data-mobile="1" data-centered="true" data-space="20" data-space-mb="0" data-loop="true" data-auto-play="true" data-pagination="1" data-pagination-md="1" data-pagination-lg="1">
        <div class="swiper-wrapper">
            <?php foreach ($page->hero_slides as $slide):
              $image = ba_picture($slide, 'slide_image', 'image_path');
            ?>
            <div class="swiper-slide">
                <div class="wrap-slider <?= ba_e($slide->slide_class ?: 'slide-1') ?>">
                    <div class="img-style">
                        <img class="lazyload" data-src="<?= ba_e($image) ?>" src="<?= ba_e($image) ?>" alt="<?= ba_e($slide->heading) ?>">
                    </div>
                    <div class="box-content">
                        <div class="box-title">
                            <h1 class="text-white fade-item fade-item-1"><?= ba_e($slide->heading) ?></h1>
                            <p class="text-body-1 text-white fade-item fade-item-2"><?= ba_e($slide->subheading) ?></p>
                        </div>
                        <a href="<?= ba_e($slide->button_url) ?>" class="tf-btn btn-white fade-item fade-item-3"><?= ba_e($slide->button_text) ?> <i class="icon-arrow-up-right"></i></a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="sw-button swiper-button-next navigation-next-slider"></div>
        <div class="sw-button swiper-button-prev navigation-prev-slider"></div>
        <div class="sw-dots sw-pagination-slider type-dot-line justify-content-center"></div>
    </div>
</div>

<section class="flat-spacing-2">
    <div class="container-full">
        <div class="row">
            <div class="col-12">
                <div class="text-center flat-spacing pt-0 line-bottom-container">
                    <div class="wrap-cls-img">
                        <?php foreach ($page->home_categories as $category):
                          $image = ba_picture($category, 'slide_image', 'image_path');
                        ?>
                        <div class="cls-img-item hover-img">
                            <div class="image img-style">
                                <img data-src="<?= ba_e($image) ?>" src="<?= ba_e($image) ?>" alt="<?= ba_e($category->heading) ?>">
                            </div>
                            <h3><a class="link" href="<?= ba_e($category->button_url) ?>"><?= ba_e($category->heading) ?></a></h3>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="<?= ba_e(ba_url(wire('pages')->get('template=shop'))) ?>" class="btn-line"><span><?= ba_e($settings->label_view_all) ?></span></a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($page->vision_heading): ?>
<section class="flat-spacing-2 pt-0 vision-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="heading-section text-center spacing-2">
                    <h3 class="wow fadeInUp"><?= ba_e($page->vision_heading) ?></h3>
                </div>
                <div class="vision-content wow fadeInUp" data-wow-delay="0.1s">
                    <?php foreach (ba_paragraphs($page->vision_body) as $paragraph): ?>
                    <p class="text-body-1 text_secondary"><?= ba_e($paragraph) ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php if (count($page->sectors)): ?>
            <div class="col-12">
                <div dir="ltr" class="swiper tf-sw-iconbox" data-preview="5" data-tablet="3" data-mobile-sm="2" data-mobile="1" data-space-lg="30" data-space-md="30" data-space="15" data-pagination="1" data-pagination-sm="2" data-pagination-md="3" data-pagination-lg="5">
                    <div class="swiper-wrapper">
                        <?php foreach ($page->sectors as $sector): ?>
                        <div class="swiper-slide">
                            <div class="tf-box-icon">
                                <div class="icon">
                                    <img src="<?= ba_e(ba_picture($sector, 'slide_image', 'image_path')) ?>" alt="" width="60" height="60" loading="lazy">
                                </div>
                                <div class="content"><h5 class="title"><?= ba_e($sector->heading) ?></h5></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="sw-pagination-iconbox sw-dots type-circle d-flex justify-content-center"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="flat-spacing-5 pt-0">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="heading-section text-center">
                    <h3 class="wow fadeInUp"><?= ba_e($page->featured_heading) ?></h3>
                    <p class="text-body-default text_secondary wow fadeInUp" data-wow-delay="0.1s"><?= ba_e($page->featured_subheading) ?></p>
                </div>
                <div class="tf-grid-layout tf-col-2 lg-col-4">
                    <?php
                    $featured = wire('pages')->find('template=product, featured=1, sort=sort');
                    $index = 0;
                    foreach ($featured as $product):
                      echo ba_product_card($product, ($index * 0.1) . 's');
                      $index++;
                    endforeach;
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (count($page->gallery_items)): ?>
<section class="flat-spacing-2 pt-0">
    <div class="container">
        <div class="heading-section text-center">
            <h3 class="wow fadeInUp"><?= ba_e($page->gallery_heading) ?></h3>
            <?php if ($page->gallery_subheading): ?>
            <p class="text-body-default text_secondary wow fadeInUp" data-wow-delay="0.1s"><?= ba_e($page->gallery_subheading) ?></p>
            <?php endif; ?>
        </div>
        <div class="swiper tf-sw-shop-gallery" data-preview="4" data-tablet="3" data-mobile="2" data-space-lg="10" data-space-md="10" data-space="8" data-pagination="2" data-pagination-md="3" data-pagination-lg="1">
            <div class="swiper-wrapper">
                <?php $index = 0; foreach ($page->gallery_items as $item):
                  $image = ba_picture($item, 'slide_image', 'image_path');
                ?>
                <div class="swiper-slide">
                    <div class="gallery-item hover-overlay hover-img wow fadeInUp" data-wow-delay="<?= ba_e(($index * 0.1) . 's') ?>">
                        <div class="img-style">
                            <img class="lazyload img-hover" data-src="<?= ba_e($image) ?>" src="<?= ba_e($image) ?>" alt="<?= ba_e($item->caption ?: $page->gallery_heading) ?>">
                        </div>
                        <a href="<?= ba_e($image) ?>" data-fancybox="home-gallery" data-caption="<?= ba_e($item->caption ?: $page->gallery_heading) ?>" class="box-icon hover-tooltip">
                            <span class="icon icon-eye"></span>
                            <span class="tooltip"><?= ba_e($settings->label_quick_view) ?></span>
                        </a>
                    </div>
                </div>
                <?php $index++; endforeach; ?>
            </div>
            <div class="sw-pagination-gallery sw-dots type-circle justify-content-center"></div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (count($page->partner_logos)): ?>
<section class="flat-spacing-3 pt-0 partners-section">
    <div class="container">
        <div class="heading-section text-center">
            <h3 class="wow fadeInUp"><?= ba_e($page->partners_heading) ?></h3>
        </div>
        <div class="partners-logos wow fadeInUp" data-wow-delay="0.1s">
            <?php foreach ($page->partner_logos as $logo): ?>
            <div class="partners-logo" aria-label="<?= ba_e($logo->caption) ?>">
                <img src="<?= ba_e(ba_picture($logo, 'slide_image', 'image_path')) ?>" alt="<?= ba_e($logo->caption) ?>" loading="lazy" width="160" height="64">
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
<?php
$baBody = ob_get_clean();
include __DIR__ . '/_layout.php';
