<?php namespace ProcessWire;

/** @var string $baTitle */
/** @var string $baBody */
/** @var string $baCurrent */
/** @var string $baDescription */

$settings = ba_settings();
$lang = ba_lang();
$home = ba_home();
$shop = wire('pages')->get('template=shop');
$industries = wire('pages')->get('template=industries');
$services = wire('pages')->get('template=services');
$news = wire('pages')->get('template=news');
$contact = wire('pages')->get('template=contact');
$categories = $shop->id ? $shop->children('sort=sort') : array();
$industryLinks = $industries->id ? wire('pages')->find('template=industry, parent_market=0, sort=sort') : array();
$serviceLinks = $services->id ? $services->children('sort=sort') : array();
$title = isset($baTitle) && $baTitle !== '' ? $baTitle : ($settings->id ? $settings->site_name : 'BasicAir');
$description = isset($baDescription) ? $baDescription : '';
$current = isset($baCurrent) ? $baCurrent : '';
$body = isset($baBody) ? $baBody : '';

function ba_item_class($on) {
  return $on ? ' active' : '';
}
?>
<!DOCTYPE html>
<html lang="<?= ba_e($lang) ?>">
<head>
    <meta charset="utf-8">
    <title><?= ba_e($title) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <?php if ($description): ?><meta name="description" content="<?= ba_e($description) ?>"><?php endif; ?>
    <link rel="stylesheet" href="/fonts/fonts.css">
    <link rel="stylesheet" href="/fonts/font-icons.css">
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/swiper-bundle.min.css">
    <link rel="stylesheet" href="/css/animate.css">
    <link rel="stylesheet" href="/css/styles.css">
    <?php if ($current === 'index'): ?>
    <link rel="stylesheet" href="/css/jquery.fancybox.min.css">
    <?php endif; ?>
    <link rel="shortcut icon" href="/images/logo/favicon.png">
    <link rel="apple-touch-icon-precomposed" href="/images/logo/favicon.png">
</head>
<body class="preload-wrapper">
    <button id="scroll-top">
        <svg width="24" height="25" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M3 11.9175L12 2.91748L21 11.9175H16.5V20.1675C16.5 20.3664 16.421 20.5572 16.2803 20.6978C16.1397 20.8385 15.9489 20.9175 15.75 20.9175H8.25C8.05109 20.9175 7.86032 20.8385 7.71967 20.6978C7.57902 20.5572 7.5 20.3664 7.5 20.1675V11.9175H3Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </button>
    <div class="preload preload-container"><div class="preload-logo"><div class="spinner"></div></div></div>
    <div id="wrapper">
        <div class="tf-topbar">
            <div class="container-full">
                <div class="row">
                    <div class="col-xl-4">
                        <div class="topbar-left d-none d-xl-flex"><?= ba_language_select($current) ?></div>
                    </div>
                    <div class="col-xl-4">
                        <div class="wrapper-slider-topbar">
                            <div class="swiper tf-sw-top_bar" data-preview="1" data-space="0" data-loop="true" data-speed="2000" data-auto-play="true" data-delay="2000">
                                <div class="swiper-wrapper">
                                    <?php foreach ($settings->announcements as $item): ?>
                                    <div class="swiper-slide">
                                        <p class="text-caption-1 text_white"><?= ba_e($item->announce_text) ?><?php if ($item->highlight): ?><span class="text_primary"><?= ba_e($item->highlight) ?></span><?php endif; ?></p>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="navigation-topbar nav-next-topbar"><span class="icon icon-left"></span></div>
                            <div class="navigation-topbar nav-prev-topbar"><span class="icon icon-right"></span></div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="topbar-right justify-content-end d-none d-xl-flex">
                            <a href="<?= ba_e(ba_url($services)) ?>" class="text_white text-caption-1 link"><?= ba_e($settings->nav_services) ?></a>
                            <a href="<?= ba_e(ba_url($contact)) ?>" class="text_white text-caption-1 link"><?= ba_e($settings->nav_contact) ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <header id="header" class="header-default">
            <div class="main-header">
                <div class="container-full">
                    <div class="row wrapper-header align-items-center">
                        <div class="col-xl-5 d-none d-xl-block">
                            <nav class="box-navigation text-center">
                                <ul class="box-nav-ul justify-content-start">
                                    <li class="menu-item">
                                        <a href="<?= ba_e(ba_url($home)) ?>" class="item-link<?= ba_item_class($current === 'index') ?>"><?= ba_e($settings->nav_home) ?></a>
                                    </li>
                                    <li class="menu-item position-relative">
                                        <a href="<?= ba_e(ba_url($shop)) ?>" class="item-link<?= ba_item_class(in_array($current, array('shop', 'product', 'product-category'), true)) ?>"><?= ba_e($settings->nav_products) ?><?php if (count($categories)): ?><i class="icon icon-down"></i><?php endif; ?></a>
                                        <?php if (count($categories)): ?>
                                        <div class="sub-menu submenu-default">
                                            <ul class="menu-list">
                                                <?php foreach ($categories as $category): ?>
                                                <li><a href="<?= ba_e(ba_url($category)) ?>" class="menu-link-text<?= $page->id === $category->id ? ' active' : '' ?>"><?= ba_e($category->title) ?></a></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                        <?php endif; ?>
                                    </li>
                                    <li class="menu-item position-relative">
                                        <a href="<?= ba_e(ba_url($industries)) ?>" class="item-link<?= ba_item_class(in_array($current, array('industries', 'industry-market'), true)) ?>"><?= ba_e($settings->nav_industries) ?><?php if (count($industryLinks)): ?><i class="icon icon-down"></i><?php endif; ?></a>
                                        <?php if (count($industryLinks)): ?>
                                        <div class="sub-menu submenu-default">
                                            <ul class="menu-list">
                                                <?php foreach ($industryLinks as $market): ?>
                                                <li><a href="<?= ba_e(ba_url($market)) ?>" class="menu-link-text<?= $page->id === $market->id ? ' active' : '' ?>"><?= ba_e($market->title) ?></a></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                        <?php endif; ?>
                                    </li>
                                    <li class="menu-item position-relative">
                                        <a href="<?= ba_e(ba_url($services)) ?>" class="item-link<?= ba_item_class(in_array($current, array('services', 'service'), true)) ?>"><?= ba_e($settings->nav_services) ?><?php if (count($serviceLinks)): ?><i class="icon icon-down"></i><?php endif; ?></a>
                                        <?php if (count($serviceLinks)): ?>
                                        <div class="sub-menu submenu-default">
                                            <ul class="menu-list">
                                                <?php foreach ($serviceLinks as $service): ?>
                                                <li><a href="<?= ba_e(ba_url($service)) ?>" class="menu-link-text<?= $page->id === $service->id ? ' active' : '' ?>"><?= ba_e($service->title) ?></a></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                        <?php endif; ?>
                                    </li>
                                    <li class="menu-item">
                                        <a href="<?= ba_e(ba_url($news)) ?>" class="item-link<?= ba_item_class(in_array($current, array('news', 'article'), true)) ?>"><?= ba_e($settings->nav_news) ?></a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="<?= ba_e(ba_url($contact)) ?>" class="item-link<?= ba_item_class($current === 'contact') ?>"><?= ba_e($settings->nav_contact) ?></a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                        <div class="col-md-4 col-2 d-xl-none">
                            <a href="#mobileMenu" class="mobile-menu" data-bs-toggle="offcanvas" aria-controls="mobileMenu">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#000000" viewBox="0 0 256 256"><path d="M224,128a8,8,0,0,1-8,8H40a8,8,0,0,1,0-16H216A8,8,0,0,1,224,128ZM40,72H216a8,8,0,0,0,0-16H40a8,8,0,0,0,0,16ZM216,184H40a8,8,0,0,0,0,16H216a8,8,0,0,0,0-16Z"></path></svg>
                            </a>
                        </div>
                        <div class="col-xl-2 col-md-4 col-8 text-center">
                            <a href="<?= ba_e(ba_url($home)) ?>" class="logo-header">
                                <img src="/images/logo/BasicAirLogo-black.webp" alt="<?= ba_e($settings->site_name) ?>" class="logo">
                            </a>
                        </div>
                        <div class="col-xl-5 col-md-4 col-2">
                            <ul class="nav-icon">
                                <li class="nav-language d-none d-xl-flex"><div class="tf-languages"><?= ba_language_select($current) ?></div></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <?= $body ?>

        <footer id="footer" class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="footer-body">
                            <div class="footer-left">
                                <div class="footer-infor flex-grow-1">
                                    <a href="<?= ba_e(ba_url($home)) ?>" class="footer-logo">
                                        <img src="/images/logo/BasicAirLogo-white.webp" alt="<?= ba_e($settings->site_name) ?>">
                                    </a>
                                    <div class="footer-menu">
                                        <div class="footer-col-block">
                                            <h5 class="footer-heading text_white footer-heading-mobile">Information</h5>
                                            <div class="tf-collapse-content">
                                                <ul class="footer-menu-list">
                                                    <?php foreach ($settings->footer_info as $link): ?>
                                                    <li class="text-body-default"><a href="<?= ba_e($link->link_url) ?>" class="link footer-menu_item"><?= ba_e($link->link_label) ?></a></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="footer-col-block">
                                            <h5 class="footer-heading text_white footer-heading-mobile">Services</h5>
                                            <div class="tf-collapse-content">
                                                <ul class="footer-menu-list">
                                                    <?php foreach ($settings->footer_service as $link): ?>
                                                    <li class="text-body-default"><a href="<?= ba_e($link->link_url) ?>" class="link footer-menu_item"><?= ba_e($link->link_label) ?></a></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="footer-phone-number">
                                        <h4 class="text_white number"><?= ba_e($settings->phone) ?></h4>
                                        <h4 class="text_white mail"><?= ba_e($settings->email) ?></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="footer-col-block footer-newsletter">
                                <h3 class="footer-heading footer-heading-mobile text_white"><?= ba_e($settings->newsletter_heading) ?></h3>
                                <div class="tf-collapse-content">
                                    <form class="form-newsletter subscribe-form" method="post" action="#">
                                        <div class="subscribe-content">
                                            <fieldset class="email">
                                                <input type="email" class="subscribe-email" placeholder="<?= ba_e($settings->newsletter_placeholder) ?>">
                                            </fieldset>
                                            <div class="button-submit">
                                                <button class="subscribe-button text-body-default" type="button"><?= ba_e($settings->subscribe_button) ?><i class="icon-arrow-up-right"></i></button>
                                            </div>
                                        </div>
                                    </form>
                                    <ul class="tf-social-icon type-2">
                                        <?php if ($settings->facebook): ?><li><a href="<?= ba_e($settings->facebook) ?>" class="social-facebook"><i class="icon icon-facebook"></i></a></li><?php endif; ?>
                                        <?php if ($settings->twitter): ?><li><a href="<?= ba_e($settings->twitter) ?>" class="social-twiter"><i class="icon icon-x"></i></a></li><?php endif; ?>
                                        <?php if ($settings->instagram): ?><li><a href="<?= ba_e($settings->instagram) ?>" class="social-instagram"><i class="icon icon-instagram"></i></a></li><?php endif; ?>
                                        <?php if ($settings->telegram): ?><li><a href="<?= ba_e($settings->telegram) ?>" class="social-amazon"><i class="icon icon-telegram"></i></a></li><?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <div class="container">
                    <div class="row">
                        <div class="col-12">
                            <div class="footer-bottom-wrap">
                                <div class="left">
                                    <p class="text-body-default text_white">© <?= date('Y') ?> <?= ba_e($settings->site_name) ?>. <?= ba_e($settings->copyright) ?></p>
                                </div>
                                <div class="center"><?= ba_language_select($current) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </footer>

        <div class="tf-toolbar-bottom">
            <div class="toolbar-item">
                <a href="<?= ba_e(ba_url($shop)) ?>">
                    <div class="toolbar-icon"><span class="icon icon-squaresfour"></span></div>
                    <div class="toolbar-label"><?= ba_e($settings->label_shop) ?></div>
                </a>
            </div>
            <div class="toolbar-item">
                <a href="<?= ba_e(ba_url($contact)) ?>">
                    <div class="toolbar-icon"><span class="icon icon-lifebuoy"></span></div>
                    <div class="toolbar-label"><?= ba_e($settings->nav_contact) ?></div>
                </a>
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-start canvas-mb" id="mobileMenu">
        <span class="icon-close icon-close-popup" data-bs-dismiss="offcanvas" aria-label="Close"></span>
        <div class="mb-canvas-content">
            <div class="mb-body">
                <ul class="nav-ul-mb" id="wrapper-menu-navigation">
                    <li class="nav-mb-item<?= ba_item_class($current === 'index') ?>"><a href="<?= ba_e(ba_url($home)) ?>" class="mb-menu-link"><span><?= ba_e($settings->nav_home) ?></span></a></li>
                    <li class="nav-mb-item<?= ba_item_class(in_array($current, array('shop', 'product', 'product-category'), true)) ?>">
                        <a href="#dropdown-menu-products" class="collapsed mb-menu-link" data-bs-toggle="collapse"><span><?= ba_e($settings->nav_products) ?></span><span class="btn-open-sub"></span></a>
                        <div id="dropdown-menu-products" class="collapse">
                            <ul class="sub-nav-menu">
                                <li><a href="<?= ba_e(ba_url($shop)) ?>" class="sub-nav-link"><?= ba_e($settings->nav_products) ?></a></li>
                                <?php foreach ($categories as $category): ?>
                                <li><a href="<?= ba_e(ba_url($category)) ?>" class="sub-nav-link"><?= ba_e($category->title) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-mb-item<?= ba_item_class(in_array($current, array('industries', 'industry-market'), true)) ?>">
                        <a href="#dropdown-menu-industries" class="collapsed mb-menu-link" data-bs-toggle="collapse"><span><?= ba_e($settings->nav_industries) ?></span><span class="btn-open-sub"></span></a>
                        <div id="dropdown-menu-industries" class="collapse">
                            <ul class="sub-nav-menu">
                                <li><a href="<?= ba_e(ba_url($industries)) ?>" class="sub-nav-link"><?= ba_e($settings->nav_industries) ?></a></li>
                                <?php foreach ($industryLinks as $market): ?>
                                <li><a href="<?= ba_e(ba_url($market)) ?>" class="sub-nav-link"><?= ba_e($market->title) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-mb-item<?= ba_item_class(in_array($current, array('services', 'service'), true)) ?>">
                        <a href="#dropdown-menu-services" class="collapsed mb-menu-link" data-bs-toggle="collapse"><span><?= ba_e($settings->nav_services) ?></span><span class="btn-open-sub"></span></a>
                        <div id="dropdown-menu-services" class="collapse">
                            <ul class="sub-nav-menu">
                                <li><a href="<?= ba_e(ba_url($services)) ?>" class="sub-nav-link"><?= ba_e($settings->nav_services) ?></a></li>
                                <?php foreach ($serviceLinks as $service): ?>
                                <li><a href="<?= ba_e(ba_url($service)) ?>" class="sub-nav-link"><?= ba_e($service->title) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-mb-item<?= ba_item_class(in_array($current, array('news', 'article'), true)) ?>"><a href="<?= ba_e(ba_url($news)) ?>" class="mb-menu-link"><span><?= ba_e($settings->nav_news) ?></span></a></li>
                    <li class="nav-mb-item<?= ba_item_class($current === 'contact') ?>"><a href="<?= ba_e(ba_url($contact)) ?>" class="mb-menu-link"><span><?= ba_e($settings->nav_contact) ?></span></a></li>
                </ul>
                <div class="mb-other-content">
                    <div class="mb-notice"><a href="<?= ba_e(ba_url($contact)) ?>" class="text-need"><?= ba_e($settings->label_need_help) ?></a></div>
                    <ul class="mb-info">
                        <li><?= ba_e($settings->address) ?></li>
                        <li>Email: <b><?= ba_e($settings->email) ?></b></li>
                        <li>Phone: <b><?= ba_e($settings->phone) ?></b></li>
                    </ul>
                </div>
            </div>
            <div class="mb-bottom"><div class="bottom-bar-language"><?= ba_language_select($current) ?></div></div>
        </div>
    </div>

    <script>window.__PATH_PREFIX__ = "/";</script>
    <script src="/js/bootstrap.min.js"></script>
    <script src="/js/jquery.min.js"></script>
    <script src="/js/swiper-bundle.min.js"></script>
    <script src="/js/carousel.js"></script>
    <script src="/js/bootstrap-select.min.js"></script>
    <script src="/js/lazysize.min.js"></script>
    <script src="/js/wow.min.js"></script>
    <script src="/js/multiple-modal.js"></script>
    <script src="/js/parallax.min.js"></script>
    <script src="/js/simpleParallaxVanilla.umd.js"></script>
    <script src="/js/main.js"></script>
    <script src="/js/cms-language.js"></script>
    <?php if ($current === 'index'): ?><script src="/js/jquery.fancybox.js"></script><?php endif; ?>
    <?php if ($current === 'contact'): ?><script src="/js/jquery-validate.js"></script><?php endif; ?>
</body>
</html>
