<?php
$ts = !empty($themeSettings) ? $themeSettings : array();

if (!function_exists('wv_render_rich_html')) {
    function wv_render_rich_html($text, $allowBreaks = true) {
        if ($text === null || $text === '') {
            return '';
        }
        $allowed = '<a><b><strong><i><em><u><br><p><span><small><font><strike><s><mark><sup><sub><ul><ol><li><div><h1><h2><h3><h4><h5><h6>';
        $clean = strip_tags((string)$text, $allowed);
        if ($allowBreaks && strpos($clean, '<br') === false && strpos($clean, '<p') === false && strpos($clean, '<div') === false) {
            $clean = nl2br($clean);
        }
        return $clean;
    }
}
if (!function_exists('wv_render_rich_title')) {
    function wv_render_rich_title($title) {
        if ($title === null || $title === '') {
            return '';
        }
        $allowed = '<b><strong><i><em><u><br><span><small><font><mark><sup><sub>';
        $clean = strip_tags((string)$title, $allowed);
        if (strpos($clean, '<br') === false) {
            $clean = nl2br($clean);
        }
        return $clean;
    }
}

$topHeaderEnabled = isset($ts['top_header_enabled']) ? (int)$ts['top_header_enabled'] : 0;
$stickyHeaderEnabled = !isset($ts['sticky_header_enabled']) || !empty($ts['sticky_header_enabled']);
$stickyHeaderMobileEnabled = !empty($ts['sticky_header_mobile_enabled']);
$brandName = !empty($ts['brand_site_title']) ? $ts['brand_site_title'] : (!empty($ts['brand_name']) ? $ts['brand_name'] : (!empty($siteName) ? $siteName : 'WHITE VILLAS & LODGES'));
$brandSubtitle = !empty($ts['brand_tagline']) ? $ts['brand_tagline'] : (!empty($ts['brand_subtitle']) ? $ts['brand_subtitle'] : 'HOTEL & SUITES • RESORT RESIDENCES');
$logoDisplayMode = !empty($ts['logo_display_mode']) ? $ts['logo_display_mode'] : 'both';
$logoImage = !empty($ts['brand_logo_image']) ? $ts['brand_logo_image'] : (!empty($frontLogo) ? $frontLogo : '');
$phone = !empty($ts['brand_phone']) ? $ts['brand_phone'] : (!empty($ts['site_phone']) ? $ts['site_phone'] : (!empty($contact) ? $contact : '+92 339 1101156'));
$email = !empty($ts['brand_email']) ? $ts['brand_email'] : (!empty($ts['site_email']) ? $ts['site_email'] : (!empty($siteEmail) ? $siteEmail : 'info@whitevillas.com.pk'));
$address = !empty($ts['brand_address']) ? $ts['brand_address'] : (!empty($ts['site_address']) ? $ts['site_address'] : (!empty($siteAddress) ? $siteAddress : '49-AA F/F Sector D Commercial Bahria Town Lahore, PAKISTAN'));
$footerAbout = !empty($ts['footer_about_text']) ? $ts['footer_about_text'] : 'White Villas provides premier real estate solutions, luxury holiday villas, and verified investment opportunities with guaranteed transparency and high rental yields.';
$footerCol2Title = !empty($ts['footer_col2_title']) ? $ts['footer_col2_title'] : 'Quick Links';
$footerCol3Title = !empty($ts['footer_col3_title']) ? $ts['footer_col3_title'] : 'Information';
$footerCol4Title = !empty($ts['footer_col4_title']) ? $ts['footer_col4_title'] : 'Contact Office';
$footerCopyright = !empty($ts['footer_copyright_text']) ? $ts['footer_copyright_text'] : ('&copy; ' . date('Y') . ' <strong class="text-white">' . h($brandName) . '</strong>. All Rights Reserved.');
$footerCreditName = !empty($ts['footer_credit_name']) ? $ts['footer_credit_name'] : 'Al-Riaz Tech';
$footerCreditUrl = !empty($ts['footer_credit_url']) ? $ts['footer_credit_url'] : 'https://alriaztech.com';

$isThemeHomePage = (isset($this->params['controller']) && strtolower($this->params['controller']) === 'fronts') || (isset($page) && $page === 'home') || (empty($this->params['controller']) && empty($this->params['action']));

$bookNowText = !empty($ts['header_book_btn_text']) ? $ts['header_book_btn_text'] : 'BOOK NOW';
$configuredBookNowLink = !empty($ts['header_book_btn_link']) ? trim((string)$ts['header_book_btn_link']) : '';

if ($isThemeHomePage) {
    // On Homepage: Always go to / scroll to #contact-form section
    $bookNowLink = '#contact-form';
    $bookNowTarget = '_self';
} else {
    // On Other Pages: Use backend linked URL (or fallback to homepage contact form)
    if ($configuredBookNowLink !== '' && $configuredBookNowLink !== '#contact-form') {
        $bookNowLink = $configuredBookNowLink;
        if (strpos($bookNowLink, 'http://') === 0 || strpos($bookNowLink, 'https://') === 0 || strpos($bookNowLink, '/') === 0 || strpos($bookNowLink, '#') === 0) {
            // Valid relative or absolute URL
        } else {
            $bookNowLink = $this->Html->url('/' . ltrim($bookNowLink, '/'));
        }
        $bookNowTarget = !empty($ts['header_book_btn_new_tab']) ? '_blank' : '_self';
    } else {
        $bookNowLink = $this->Html->url('/#contact-form');
        $bookNowTarget = '_self';
    }
}
// Ticker Bar Variables
$tickerEnabled = !isset($ts['ticker_enabled']) || !empty($ts['ticker_enabled']);
$tickerBadgeText = !empty($ts['ticker_badge_text']) ? $ts['ticker_badge_text'] : '🔥 LATEST UPDATES';
$tickerBadgeBg = !empty($ts['ticker_badge_bg']) ? $ts['ticker_badge_bg'] : '#d11242';
$tickerBadgeColor = !empty($ts['ticker_badge_color']) ? $ts['ticker_badge_color'] : '#ffffff';
$tickerBgColor = !empty($ts['ticker_bg_color']) ? $ts['ticker_bg_color'] : '#07152a';
$tickerTextColor = !empty($ts['ticker_text_color']) ? $ts['ticker_text_color'] : '#ffffff';
$tickerSpeed = !empty($ts['ticker_speed']) ? (int)$ts['ticker_speed'] : 30;
$tickerLink = !empty($ts['ticker_link']) ? $ts['ticker_link'] : '';
$tickerShowOnAllPages = !empty($ts['ticker_show_on_all_pages']);
$tickerMobileHideBadge = !isset($ts['ticker_mobile_hide_badge']) || !empty($ts['ticker_mobile_hide_badge']);
$tickerMobileEnabled = !isset($ts['ticker_mobile_enabled']) || !empty($ts['ticker_mobile_enabled']);
$tickerRawText = !empty($ts['ticker_text']) ? $ts['ticker_text'] : '📢 Special Launch Offer: 10% Discount on Luxury 2 & 3 Bed Apartments! 🔥 | ⚡ Limited inventory available with flexible 36-month installment plans 🌴 | 💎 Book your site tour today and get exclusive VIP benefits 🏡 | ✨ Call +92339 1101156 for instant consultation!';

$tickerItems = preg_split('/[\r\n|]+/', $tickerRawText);
$formattedTickerItems = array();
foreach ($tickerItems as $tItem) {
    $tTrim = trim($tItem);
    if (!empty($tTrim)) {
        $formattedTickerItems[] = '<span class="r-ticker-item">' . $tTrim . '</span>';
    }
}
$renderedTickerText = implode(' <span class="r-ticker-sep mx-3" style="color: #7cf9ff; opacity: 0.85;">⚡</span> ', $formattedTickerItems);

$themeBaseUrl = $this->Html->url('/theme_pro/');

$ogLogo = !empty($frontLogo) ? $frontLogo : (!empty($ts['brand_logo_image']) ? $ts['brand_logo_image'] : '');
if (empty($ogLogo)) {
    $ogLogo = 'organisation-logo-20260511101817-recovered.png';
}
if (strpos($ogLogo, 'http://') === 0 || strpos($ogLogo, 'https://') === 0) {
    $ogImageUrl = $ogLogo;
} else {
    $ogImageUrl = Router::url('/img/' . ltrim($ogLogo, '/'), true);
}
$ogSiteName = !empty($brandName) ? $brandName : (!empty($siteName) ? $siteName : 'White Villas');
$ogPageTitle = !empty($title_for_layout) ? ($title_for_layout . ' — ' . $ogSiteName) : (!empty($siteTitle) ? $siteTitle : ($ogSiteName . ' — Real Estate | Investment Opportunity'));
$ogPageDesc = !empty($siteDescription) ? $siteDescription : 'White Villas — Premier Real Estate, Luxury Lodges & Verified Investment Opportunities.';
$ogPageUrl = Router::url(null, true);
$siteFaviconFile = !empty($siteFavicon) ? $siteFavicon : '';
?>
<!DOCTYPE html>
<html lang="en-US" class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
    <title><?php echo !empty($title_for_layout) ? h($title_for_layout . ' — ' . $brandName) : h($brandName . ' — Real Estate | Investment Opportunity'); ?></title>

    <!-- Favicon (Browser Tab Icon) -->
    <?php if (!empty($siteFaviconFile)): ?>
    <link rel="icon" type="image/x-icon" href="<?php echo $this->Html->url('/img/' . $siteFaviconFile); ?>">
    <link rel="shortcut icon" href="<?php echo $this->Html->url('/img/' . $siteFaviconFile); ?>">
    <link rel="apple-touch-icon" href="<?php echo $this->Html->url('/img/' . $siteFaviconFile); ?>">
    <?php else: ?>
    <link rel="icon" type="image/x-icon" href="<?php echo $this->Html->url('/favicon.ico'); ?>">
    <?php endif; ?>

    <!-- Social Media Sharing & Open Graph Meta Tags (WhatsApp, Facebook, Twitter, LinkedIn) -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?php echo h($ogSiteName); ?>">
    <meta property="og:title" content="<?php echo h($ogPageTitle); ?>">
    <meta property="og:description" content="<?php echo h($ogPageDesc); ?>">
    <meta property="og:image" content="<?php echo h($ogImageUrl); ?>">
    <meta property="og:image:secure_url" content="<?php echo h($ogImageUrl); ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="<?php echo h($ogSiteName); ?>">
    <meta property="og:url" content="<?php echo h($ogPageUrl); ?>">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo h($ogPageTitle); ?>">
    <meta name="twitter:description" content="<?php echo h($ogPageDesc); ?>">
    <meta name="twitter:image" content="<?php echo h($ogImageUrl); ?>">
    
    <!-- Google Fonts Matching Ramada Costa Del Sol Exactly -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=League+Gothic&family=League+Spartan:wght@300;400;500;600;700;800;900&family=Montserrat:wght@300;400;500;600;700;800&family=Raleway:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="<?php echo $this->Html->url('/design1000/assets/js/jquery.js'); ?>"></script>

    <!-- Theme Core CSS -->
    <link rel="stylesheet" href="<?php echo $themeBaseUrl; ?>css/ramada-core.css">
    <link rel="stylesheet" href="<?php echo $themeBaseUrl; ?>css/al-riaz-pro.css">

    <?php echo $this->fetch('meta'); ?>
    <?php echo $this->fetch('css'); ?>

    <style>
        :root {
            --wv-primary: #d11242;
            --wv-dark: #07152a;
            --wv-heading: #666666;
            --wv-border: #e8e8e8;
            --wv-site-width: 1300px;
        }
        body {
            font-family: 'Raleway', 'League Spartan', sans-serif;
            color: #333333;
            overflow-x: hidden;
            background-color: #ffffff;
            font-size: 15px;
            line-height: 1.6;
        }
        h1, h2, h3, h4, h5, h6, .vc_custom_heading {
            font-family: 'League Spartan', sans-serif;
            letter-spacing: 0.03em;
            color: #666666;
        }

        /* Container matching Ramada 1300px global width */
        .r-container {
            width: 100%;
            max-width: 1300px;
            margin-left: auto;
            margin-right: auto;
            padding-left: 20px;
            padding-right: 20px;
        }
        .r-container-wide {
            width: 100%;
            padding-left: 8%;
            padding-right: 8%;
        }
        @media (max-width: 991px) {
            .r-container-wide {
                padding-left: 5%;
                padding-right: 5%;
            }
        }
        @media (max-width: 575px) {
            .r-container-wide {
                padding-left: 15px;
                padding-right: 15px;
            }
        }

        /* Top Notification Bar */
        .wv-top-bar {
            background-color: #07152a;
            color: #d1d5db;
            font-size: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            position: relative;
            z-index: 1060;
        }
        .wv-top-bar a {
            color: #ffffff;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .wv-top-bar a:hover {
            color: #d11242;
        }

        /* Seamless Full-Width Background Video & Embed Wrapper */
        .hero-embed-wrapper {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100vw;
            height: 100vh;
            min-width: 177.77vh; /* 16:9 aspect ratio */
            min-height: 56.25vw; /* 16:9 aspect ratio */
            transform: translate(-50%, -50%);
            pointer-events: none;
            overflow: hidden;
        }
        .hero-embed-wrapper iframe {
            width: 100%;
            height: 100%;
            border: 0;
            pointer-events: none;
        }

        /* =========================================================================
           TRANSPARENT HEADER OVER VIDEO (Placed safely below Top Bar if active)
           ========================================================================= */
        .r-site-header {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            background: linear-gradient(180deg, rgba(0, 0, 0, 0.45) 0%, rgba(0, 0, 0, 0) 100%);
            border-bottom: none;
            transition: top 0.25s ease, background 0.35s ease, padding 0.35s ease, box-shadow 0.35s ease;
            z-index: 1050;
            padding: 24px 0;
        }
        .has-top-bar .r-site-header {
            top: 38px;
        }
        .r-site-header.scrolled {
            position: fixed !important;
            top: 0 !important;
            background: #ffffff !important;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 14px 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border-bottom: 1px solid rgba(0,0,0,0.06);
        }

        @media (max-width: 767.98px) {
            .has-top-bar .r-site-header {
                top: 0 !important;
            }
            <?php if (!empty($stickyHeaderMobileEnabled)): ?>
            .r-site-header.scrolled {
                display: block !important;
                padding: 8px 0 !important;
                background: #ffffff !important;
                box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1) !important;
            }
            .r-site-header.scrolled .r-brand-logo-text {
                font-size: 16px !important;
                color: #d11242 !important;
                text-shadow: none !important;
            }
            .r-site-header.scrolled .r-brand-sub-text {
                font-size: 7px !important;
                color: #64748b !important;
                text-shadow: none !important;
            }
            .r-site-header.scrolled .btn-r-menu {
                padding: 6px 12px !important;
                font-size: 11px !important;
            }
            <?php else: ?>
            .r-site-header.scrolled {
                display: none !important;
            }
            <?php endif; ?>
            .btn-r-book {
                display: none !important;
            }
            .wv-footer {
                display: none !important;
            }
            .btn-hero-audio, #heroAudioToggle {
                display: none !important;
            }
            #header_video {
                height: auto !important;
                aspect-ratio: 16 / 9 !important;
                min-height: 220px !important;
                max-height: 56.25vw !important;
            }
            #header_video .hero-embed-wrapper {
                width: 100% !important;
                height: 100% !important;
                min-width: 100% !important;
                min-height: 100% !important;
                top: 0 !important;
                left: 0 !important;
                transform: none !important;
            }
            #header_video .hero-embed-wrapper iframe {
                width: 100% !important;
                height: 100% !important;
            }
            .r-site-header {
                padding: 10px 0;
            }
            .r-brand-logo-text {
                font-size: 18px !important;
                letter-spacing: 0.06em !important;
            }
            .r-brand-sub-text {
                font-size: 8px !important;
                letter-spacing: 0.16em !important;
            }
        }

        /* Default Header Logo: Always White on transparent/video */
        .r-brand-logo-text {
            font-family: 'League Spartan', sans-serif;
            font-weight: 700;
            font-size: clamp(26px, 3.5vw, 38px);
            color: #ffffff !important;
            letter-spacing: 0.14em;
            line-height: 1;
            text-transform: uppercase;
            text-shadow: 0 2px 10px rgba(0,0,0,0.5);
            transition: color 0.3s ease;
        }
        .r-brand-sub-text {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.32em;
            color: #ffffff !important;
            text-transform: uppercase;
            display: block;
            margin-top: 4px;
            text-shadow: 0 1px 5px rgba(0,0,0,0.5);
            opacity: 0.95;
            transition: color 0.3s ease;
        }

        /* Sticky Header Logo: Same color as Book Now Button (#d11242) */
        .r-site-header.scrolled .r-brand-logo-text {
            color: #d11242 !important;
            text-shadow: none !important;
        }
        .r-site-header.scrolled .r-brand-sub-text {
            color: #64748b !important;
            text-shadow: none !important;
            opacity: 1;
        }

        /* Inner Pages: Header is ALWAYS Sticky with Solid White Background & Red Logo */
        .r-inner-page-body .r-site-header {
            position: sticky !important;
            top: 0 !important;
            background: #ffffff !important;
            padding: 14px 0 !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08) !important;
            border-bottom: 1px solid rgba(0,0,0,0.06) !important;
            z-index: 1050;
        }
        .r-inner-page-body .r-site-header .r-brand-logo-text {
            color: #d11242 !important;
            text-shadow: none !important;
        }
        .r-inner-page-body .r-site-header .r-brand-sub-text {
            color: #64748b !important;
            text-shadow: none !important;
            opacity: 1;
        }
        @media (max-width: 767.98px) {
            .r-inner-page-body .r-site-header {
                display: block !important;
                position: sticky !important;
                top: 0 !important;
                padding: 10px 0 !important;
            }
            .r-inner-page-body .r-site-header .r-brand-logo-text {
                font-size: 16px !important;
            }
            .r-inner-page-body .r-site-header .btn-r-menu {
                padding: 6px 12px !important;
                font-size: 11px !important;
            }
        }

        /* Red Solid Menu Button */
        .btn-r-menu {
            background-color: #d11242 !important;
            color: #ffffff !important;
            border: none;
            border-radius: 2px;
            padding: 8px 18px;
            font-weight: 700;
            font-size: 13px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            transition: all 0.25s ease;
        }
        .btn-r-menu:hover {
            background-color: #b00d35 !important;
            transform: translateY(-1px);
        }

        /* Red Solid Book Now Button */
        .btn-r-book {
            background-color: #d11242 !important;
            color: #ffffff !important;
            border: none;
            border-radius: 2px;
            padding: 9px 24px;
            font-weight: 700;
            font-size: 13px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            transition: all 0.25s ease;
        }
        .btn-r-book:hover {
            background-color: #b00d35 !important;
            color: #ffffff !important;
            transform: translateY(-1px);
        }

        /* Generic Nectar Buttons */
        .nectar-button {
            display: inline-block;
            background-color: #d11242 !important;
            color: #ffffff !important;
            font-weight: 700;
            font-size: 13px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 14px 34px !important;
            min-width: 180px;
            text-align: center;
            text-decoration: none;
            border: 2px solid #d11242;
            border-radius: 2px;
            transition: all 0.3s ease;
        }
        .nectar-button:hover {
            background-color: #b00d35 !important;
            border-color: #b00d35 !important;
            color: #ffffff !important;
            transform: translateY(-2px);
        }
        .nectar-button.see-through-white {
            background-color: transparent !important;
            border: 2px solid #ffffff !important;
            color: #ffffff !important;
        }
        .nectar-button.see-through-white:hover {
            background-color: #ffffff !important;
            color: #d11242 !important;
        }
        .nectar-button.btn-outline-theme {
            background-color: #ffffff !important;
            color: #d11242 !important;
            border: 2px solid #d11242 !important;
        }
        .nectar-button.btn-outline-theme:hover {
            background-color: #d11242 !important;
            color: #ffffff !important;
        }

        /* Offcanvas Drawer - Elevated above Header & Top Bar with Clean Header Placement */
        .offcanvas-wv {
            background: #ffffff !important;
            color: #07152a !important;
            width: 340px;
            box-shadow: 6px 0 30px rgba(0, 0, 0, 0.18);
            z-index: 1085 !important;
        }
        .offcanvas-backdrop {
            z-index: 1080 !important;
        }
        .offcanvas-backdrop.show {
            opacity: 0.35 !important;
            background-color: #000000 !important;
        }
        .offcanvas-wv .offcanvas-header {
            border-bottom: 1px solid #f1f5f9 !important;
            padding: 24px 20px 18px 20px !important;
        }
        .has-top-bar .offcanvas-wv .offcanvas-header {
            padding-top: 28px !important;
        }
        .offcanvas-wv .offcanvas-title {
            color: #d11242 !important;
            font-family: 'League Spartan', sans-serif;
            font-weight: 800;
            letter-spacing: 0.05em;
            font-size: 19px;
            margin: 0;
            display: flex;
            align-items: center;
        }
        .offcanvas-wv .btn-close {
            filter: none !important;
            opacity: 0.75;
            font-size: 14px;
        }
        .offcanvas-wv .btn-close:hover {
            opacity: 1;
        }
        .offcanvas-wv .nav-link {
            color: #d11242 !important;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            padding: 13px 18px;
            border-bottom: 1px solid #f8fafc;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s ease;
            border-radius: 4px;
        }
        .offcanvas-wv .nav-link i {
            color: #d11242 !important;
            font-size: 16px;
            width: 20px;
            text-align: center;
            transition: all 0.2s ease;
        }
        .offcanvas-wv .nav-link:hover,
        .offcanvas-wv .nav-link.active {
            color: #ffffff !important;
            background-color: #d11242 !important;
        }
        .offcanvas-wv .nav-link:hover i,
        .offcanvas-wv .nav-link.active i {
            color: #ffffff !important;
        }

        /* =========================================================================
           FLASH NEWS & UPDATE TICKER BAR (EMOJI-READY LUXURY MARQUEE)
           ========================================================================= */
        .r-ticker-bar {
            font-family: 'League Spartan', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji", sans-serif;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            border-bottom: 2px solid #d11242;
            position: relative;
            z-index: 20;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }
        .r-ticker-pulse {
            width: 8px;
            height: 8px;
            background-color: #ffffff;
            border-radius: 50%;
            display: inline-block;
            animation: rTickerPulseAnim 1.5s infinite;
        }
        @keyframes rTickerPulseAnim {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(255, 255, 255, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
        }
        .r-ticker-track {
            display: inline-flex;
            white-space: nowrap;
            animation: rTickerMarqueeAnim linear infinite;
        }
        .r-ticker-content-wrapper:hover .r-ticker-track {
            animation-play-state: paused;
        }
        .r-ticker-text {
            display: inline-flex;
            align-items: center;
            padding-right: 60px;
            font-size: 13.5px;
            font-weight: 600;
            letter-spacing: 0.02em;
        }
        .r-ticker-text a {
            color: inherit;
            text-decoration: underline;
        }
        .r-ticker-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        @keyframes rTickerMarqueeAnim {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        @media (max-width: 767.98px) {
            .r-ticker-badge {
                font-size: 10px !important;
                padding: 4px 10px !important;
                margin-right: 8px !important;
            }
            .r-ticker-text {
                font-size: 12px !important;
            }
        }

        /* =========================================================================
           EXACT RAMADA PROPERTIES FOR SALE COMPONENT (media_1787503891568.png)
           ========================================================================= */
        .r-property-stage-row {
            margin-bottom: 160px;
            position: relative;
        }
        .r-resort-counter {
            font-family: 'League Spartan', sans-serif;
            font-size: 15px;
            color: #999999;
            font-weight: 400;
            letter-spacing: 0.15em;
            margin-bottom: 22px;
        }
        .r-resort-main-title {
            font-family: 'League Spartan', sans-serif;
            font-size: clamp(42px, 5.2vw, 68px);
            color: #666666;
            line-height: 1.05;
            font-weight: 400;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 25px;
        }
        .r-resort-main-desc {
            font-size: 16px;
            color: #666666;
            line-height: 1.75;
            max-width: 440px;
        }

        /* Full-Width Right Bleed Property Showcase */
        .r-showcase-split-wrap {
            width: 100%;
            overflow-x: clip;
        }
        .r-showcase-left-col {
            padding-left: clamp(24px, 8vw, 120px);
            padding-right: clamp(20px, 4vw, 60px);
            z-index: 10;
        }
        .r-showcase-right-col {
            padding-left: 0 !important;
            padding-right: 0 !important;
            margin-right: 0 !important;
        }

        /* Right Side Image Box - EXACT RAMADA TALL PREVIEW (720px) Full Right Fill */
        .r-resort-img-container {
            position: relative;
            width: 100%;
            height: 720px;
            overflow: hidden;
            background-color: #f4f4f4;
            border-radius: 0;
        }
        .r-resort-hero-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
        }

        /* Overlapping Floating White Specification Card (Matching Screenshot 2) */
        .r-resort-floating-card {
            position: absolute;
            bottom: 35px;
            left: -110px;
            width: 440px;
            max-width: 95%;
            background: #ffffff;
            box-shadow: 0 16px 45px rgba(0, 0, 0, 0.12);
            padding: 30px 28px 24px 28px;
            z-index: 20;
            border-radius: 0;
            border: 1px solid rgba(0,0,0,0.06);
        }
        @media (max-width: 1199px) {
            .r-resort-floating-card {
                left: -60px;
                width: 400px;
                padding: 24px 20px 20px;
            }
        }
        @media (max-width: 991px) {
            .r-showcase-left-col {
                padding-left: 20px;
                padding-right: 20px;
                margin-bottom: 25px;
            }
            .r-showcase-right-col {
                padding-left: 15px !important;
                padding-right: 15px !important;
            }
            .r-property-stage-row {
                margin-bottom: 90px;
            }
            .r-resort-img-container {
                height: 440px;
            }
            .r-resort-floating-card {
                position: relative;
                left: 0;
                bottom: 0;
                width: 100%;
                margin-top: -50px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            }
        }
        .r-card-status-badge {
            font-family: 'League Spartan', sans-serif;
            font-size: 28px;
            font-weight: 800;
            color: #198754;
            line-height: 1.1;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .r-card-price-sub {
            font-family: 'League Spartan', sans-serif;
            font-size: 15px;
            color: #444444;
            margin-bottom: 16px;
            font-weight: 600;
            letter-spacing: 0.02em;
        }
        .r-card-spec-label {
            font-family: 'League Spartan', sans-serif;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.08em;
            color: #888888;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .r-card-spec-box {
            border: 1px solid #e2e8f0;
            padding: 8px 12px;
            margin-bottom: 14px;
            background: #ffffff;
        }
        .r-card-spec-box table {
            width: 100%;
            margin-bottom: 0;
        }
        .r-card-spec-box td {
            padding: 2px 6px;
            font-size: 13px;
            color: #555555;
            vertical-align: top;
            border: none;
            line-height: 1.6;
        }

        /* Luxury Footer High-Contrast Dark Styling */
        .wv-footer {
            background-color: #07152a;
            color: #cbd5e1;
            padding: 70px 0 30px;
            font-size: 14px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }
        .wv-footer-title {
            font-family: 'League Spartan', sans-serif;
            font-size: 17px;
            font-weight: 700;
            letter-spacing: 0.12em;
            color: #ffffff !important;
            text-transform: uppercase;
            margin-bottom: 20px;
        }
        .wv-footer-desc {
            color: #94a3b8 !important;
            line-height: 1.75;
            font-size: 13.5px;
        }
        .wv-footer-subtitle {
            color: #cbd5e1 !important;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            margin-bottom: 15px;
        }
        .wv-footer a {
            color: #cbd5e1 !important;
            text-decoration: none;
            transition: all 0.2s ease;
            font-size: 14px;
        }
        .wv-footer a:hover {
            color: #ff0a46 !important;
        }
        .wv-footer-divider {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
            margin-bottom: 24px;
            padding-bottom: 35px;
        }
        .wv-footer-bottom {
            color: #94a3b8 !important;
            font-size: 13px;
        }
        .wv-footer-bottom a {
            color: #ffffff !important;
            font-weight: 700;
        }
        .wv-footer-bottom a:hover {
            color: #ff0a46 !important;
        }

        /* =========================================================================
           OFFCANVAS NAVIGATION DRAWER - MATCH HOME THEME EXACTLY
           ========================================================================= */
        .offcanvas-wv {
            width: 320px !important;
            background: #ffffff !important;
            border-right: 1px solid #e2e8f0 !important;
            box-shadow: 0 20px 40px rgba(7, 21, 42, 0.15) !important;
        }
        .offcanvas-wv .offcanvas-header {
            padding: 20px 24px !important;
            border-bottom: 1px solid #f1f5f9 !important;
        }
        .offcanvas-wv .offcanvas-title {
            font-family: 'League Spartan', sans-serif !important;
            font-size: 19px !important;
            font-weight: 800 !important;
            letter-spacing: 0.08em !important;
            color: #d11242 !important;
            text-transform: uppercase !important;
        }
        .offcanvas-wv .offcanvas-body {
            padding: 20px 18px !important;
        }
        .wv-offcanvas-menu {
            margin-bottom: 0;
            padding-left: 0;
            list-style: none;
        }
        .wv-offcanvas-menu .nav-item {
            margin-bottom: 5px;
        }
        .wv-offcanvas-menu .nav-link {
            display: flex !important;
            align-items: center !important;
            gap: 14px !important;
            padding: 12px 18px !important;
            font-family: 'League Spartan', sans-serif !important;
            font-size: 14px !important;
            font-weight: 700 !important;
            letter-spacing: 0.08em !important;
            text-transform: uppercase !important;
            color: #07152a !important;
            border-radius: 6px !important;
            transition: all 0.2s ease !important;
            text-decoration: none !important;
        }
        .wv-offcanvas-menu .nav-link i {
            color: #d11242 !important;
            font-size: 16px !important;
            width: 20px !important;
            text-align: center !important;
            transition: all 0.2s ease !important;
        }
        .wv-offcanvas-menu .nav-link:hover {
            background-color: #fff1f4 !important;
            color: #d11242 !important;
            transform: translateX(4px) !important;
        }
        .wv-offcanvas-menu .nav-link:hover i {
            color: #d11242 !important;
        }
        .wv-offcanvas-menu .nav-link.active {
            background-color: #d11242 !important;
            color: #ffffff !important;
            font-weight: 800 !important;
            box-shadow: 0 6px 18px rgba(209, 18, 66, 0.32) !important;
        }
        .wv-offcanvas-menu .nav-link.active i {
            color: #ffffff !important;
        }
        .wv-offcanvas-client-btn {
            background-color: #d11242 !important;
            color: #ffffff !important;
            font-family: 'League Spartan', sans-serif !important;
            font-size: 13px !important;
            font-weight: 800 !important;
            letter-spacing: 0.08em !important;
            text-transform: uppercase !important;
            padding: 12px 18px !important;
            border-radius: 4px !important;
            box-shadow: 0 4px 12px rgba(209, 18, 66, 0.25) !important;
            transition: all 0.2s ease !important;
            text-align: center !important;
            text-decoration: none !important;
            display: block !important;
        }
        .wv-offcanvas-client-btn:hover {
            background-color: #b00d35 !important;
            color: #ffffff !important;
            transform: translateY(-1px) !important;
        }
        .wv-offcanvas-call-btn {
            border: 2px solid #d11242 !important;
            color: #d11242 !important;
            font-family: 'League Spartan', sans-serif !important;
            font-size: 13px !important;
            font-weight: 800 !important;
            letter-spacing: 0.08em !important;
            text-transform: uppercase !important;
            padding: 11px 18px !important;
            border-radius: 4px !important;
            background: transparent !important;
            transition: all 0.2s ease !important;
            text-align: center !important;
            text-decoration: none !important;
            display: block !important;
        }
        .wv-offcanvas-call-btn:hover {
            background-color: #d11242 !important;
            color: #ffffff !important;
        }

        /* =========================================================================
           INNER PAGES HARMONIZATION (APPLIES ONLY WHEN REAL ESTATE THEME IS ACTIVE)
           ========================================================================= */
        .r-inner-page-body {
            background-color: #f8fafc;
        }
        .r-inner-page-body .r-inner-page-wrapper {
            background-color: #f8fafc;
            min-height: 70vh;
            padding: 30px 0 60px;
        }
        .r-inner-page-body h1,
        .r-inner-page-body h2,
        .r-inner-page-body h3,
        .r-inner-page-body h4,
        .r-inner-page-body h5,
        .r-inner-page-body h6,
        .r-inner-page-body .page-title,
        .r-inner-page-body .page-header {
            font-family: 'League Spartan', 'Montserrat', sans-serif !important;
            letter-spacing: 0.04em !important;
            text-transform: uppercase;
            color: #07152a;
        }
        .r-inner-page-body .wv-list-hero,
        .r-inner-page-body .wv-directory-hero,
        .r-inner-page-body .wv-about-hero,
        .r-inner-page-body .wv-contact-hero,
        .r-inner-page-body .wv-public-hero,
        .r-inner-page-body .wv-detail-hero {
            background: linear-gradient(135deg, #07152a 0%, #11223e 60%, #4a061b 130%) !important;
            border-radius: 12px;
            box-shadow: 0 16px 36px rgba(7, 21, 42, 0.14);
            border-top: 3px solid #d11242;
        }
        .r-inner-page-body .wv-list-hero h1,
        .r-inner-page-body .wv-directory-hero h1,
        .r-inner-page-body .wv-about-hero h1,
        .r-inner-page-body .wv-contact-hero h1,
        .r-inner-page-body .wv-public-hero h1,
        .r-inner-page-body .wv-detail-hero h1 {
            font-family: 'League Spartan', sans-serif !important;
            font-size: clamp(26px, 3.5vw, 36px) !important;
            font-weight: 800 !important;
            letter-spacing: 0.06em !important;
            color: #ffffff !important;
        }
        .r-inner-page-body .wv-search-card,
        .r-inner-page-body .wv-property-card,
        .r-inner-page-body .wv-directory-card,
        .r-inner-page-body .wv-agent-card,
        .r-inner-page-body .wv-plan-card,
        .r-inner-page-body .wv-about-card,
        .r-inner-page-body .wv-about-values,
        .r-inner-page-body .wv-about-story,
        .r-inner-page-body .wv-contact-card,
        .r-inner-page-body .panel-default,
        .r-inner-page-body .card {
            border: 1px solid #e2e8f0 !important;
            border-radius: 10px !important;
            box-shadow: 0 10px 30px rgba(7, 21, 42, 0.05) !important;
            background: #ffffff;
            color: #334155;
        }

        /* Explicit High-Contrast Dark Cards (Contact Details, Timings, Connect with Us) */
        .r-inner-page-body .wv-contact-info,
        .r-inner-page-body .wv-hours-card,
        .r-inner-page-body .wv-about-contact,
        .r-inner-page-body .wv-about-stat {
            background: linear-gradient(135deg, #07152a 0%, #101f36 100%) !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            box-shadow: 0 16px 36px rgba(7, 21, 42, 0.22) !important;
            color: #ffffff !important;
        }
        .r-inner-page-body .wv-contact-info h3,
        .r-inner-page-body .wv-hours-card h3,
        .r-inner-page-body .wv-about-contact h3,
        .r-inner-page-body .wv-about-stat strong {
            color: #ffffff !important;
            font-family: 'League Spartan', sans-serif !important;
            font-weight: 800 !important;
            letter-spacing: 0.06em !important;
            text-transform: uppercase !important;
        }
        .r-inner-page-body .wv-contact-row strong,
        .r-inner-page-body .wv-about-contact-row strong {
            color: #7cf9ff !important;
            font-size: 11px !important;
            font-weight: 900 !important;
            letter-spacing: 0.4px !important;
            text-transform: uppercase !important;
        }
        .r-inner-page-body .wv-contact-row span,
        .r-inner-page-body .wv-contact-row a,
        .r-inner-page-body .wv-about-contact-row a,
        .r-inner-page-body .wv-about-contact-row span {
            color: #ffffff !important;
            font-size: 13.5px !important;
            font-weight: 700 !important;
        }
        .r-inner-page-body .wv-contact-icon,
        .r-inner-page-body .wv-about-contact-row i {
            background: #d11242 !important;
            color: #ffffff !important;
            border-radius: 8px !important;
        }
        .r-inner-page-body .wv-hours-day {
            color: #e2e8f0 !important;
        }
        .r-inner-page-body .wv-hours-time {
            background: #ffffff !important;
            color: #07152a !important;
            font-weight: 800 !important;
        }
        .r-inner-page-body .wv-hours-row.is-today {
            background: #fff1f4 !important;
            border-color: rgba(209, 18, 66, 0.3) !important;
        }
        .r-inner-page-body .wv-hours-row.is-today .wv-hours-day {
            color: #07152a !important;
            font-weight: 800 !important;
        }
        .r-inner-page-body .wv-hours-row.is-closed .wv-hours-time {
            background: #ffffff !important;
            color: #d11242 !important;
        }

        /* High-Contrast Readability on White Cards (Text, Headings & Forms) */
        .r-inner-page-body .wv-contact-form label,
        .r-inner-page-body .wv-search-card label,
        .r-inner-page-body .form-group label {
            color: #334155 !important;
            font-weight: 700 !important;
        }
        .r-inner-page-body .wv-contact-card-head h2,
        .r-inner-page-body .wv-about-card-head h2,
        .r-inner-page-body .wv-about-values h3,
        .r-inner-page-body .wv-about-feature h3 {
            color: #07152a !important;
            font-family: 'League Spartan', sans-serif !important;
            font-weight: 800 !important;
        }
        .r-inner-page-body .wv-contact-card-head p,
        .r-inner-page-body .wv-about-card-head p,
        .r-inner-page-body .wv-about-story p,
        .r-inner-page-body .wv-about-feature p,
        .r-inner-page-body .wv-about-values li {
            color: #475569 !important;
        }
        .r-inner-page-body .wv-directory-badge,
        .r-inner-page-body .wv-agent-badge,
        .r-inner-page-body .badge-primary,
        .r-inner-page-body .badge-danger {
            background-color: #d11242 !important;
            color: #ffffff !important;
            font-family: 'League Spartan', sans-serif !important;
            font-weight: 700 !important;
            letter-spacing: 0.06em !important;
            text-transform: uppercase !important;
        }
        .r-inner-page-body .btn-primary,
        .r-inner-page-body .btn-danger,
        .r-inner-page-body .wv-btn-primary,
        .r-inner-page-body .wv-contact-submit,
        .r-inner-page-body .wv-search-submit,
        .r-inner-page-body .wv-plan-btn,
        .r-inner-page-body .wv-directory-action {
            background-color: #d11242 !important;
            border-color: #d11242 !important;
            color: #ffffff !important;
            font-family: 'League Spartan', sans-serif !important;
            font-weight: 800 !important;
            letter-spacing: 0.06em !important;
            text-transform: uppercase !important;
            border-radius: 4px !important;
            transition: all 0.2s ease !important;
        }
        .r-inner-page-body .btn-primary:hover,
        .r-inner-page-body .btn-danger:hover,
        .r-inner-page-body .wv-btn-primary:hover,
        .r-inner-page-body .wv-contact-submit:hover,
        .r-inner-page-body .wv-search-submit:hover,
        .r-inner-page-body .wv-plan-btn:hover,
        .r-inner-page-body .wv-directory-action:hover {
            background-color: #b00d35 !important;
            border-color: #b00d35 !important;
            color: #ffffff !important;
            transform: translateY(-1px);
        }
        .r-inner-page-body .pagination .active a,
        .r-inner-page-body .pagination .active span,
        .r-inner-page-body .page-item.active .page-link {
            background-color: #d11242 !important;
            border-color: #d11242 !important;
            color: #ffffff !important;
            font-weight: 700 !important;
        }
        .r-inner-page-body .pagination a,
        .r-inner-page-body .page-link {
            color: #07152a;
            font-family: 'League Spartan', sans-serif;
            font-weight: 700;
        }
        .r-inner-page-body .pagination a:hover,
        .r-inner-page-body .page-link:hover {
            color: #d11242;
        }
    </style>
</head>
<body class="<?php echo !empty($topHeaderEnabled) ? 'has-top-bar' : ''; ?> <?php echo empty($isThemeHomePage) ? 'r-inner-page-body' : ''; ?>">

    <!-- =========================================================================
         1. Optional Top Notification Bar (If explicitly enabled in settings)
         ========================================================================= -->
    <?php if (!empty($topHeaderEnabled)): ?>
    <div class="wv-top-bar py-2 d-none d-md-block" id="topNotificationBar">
        <div class="container-fluid px-4">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-4">
                    <span><i class="fa fa-phone text-danger me-1"></i> <a href="tel:<?php echo h($phone); ?>"><?php echo h($phone); ?></a></span>
                    <span><i class="fa fa-envelope text-danger me-1"></i> <a href="mailto:<?php echo h($email); ?>"><?php echo h($email); ?></a></span>
                    <span><i class="fa fa-map-marker text-danger me-1"></i> <?php echo h($address); ?></span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <a href="<?php echo $this->Html->url('/Properties'); ?>"><i class="fa fa-building-o"></i> Properties</a>
                    <a href="<?php echo $this->Html->url('/paymentplans'); ?>"><i class="fa fa-calculator"></i> Payment Plans</a>
                    <a href="<?php echo $this->Html->url('/clients/login'); ?>" class="btn btn-sm text-white px-3" style="background-color: #d11242; border: none; font-size: 11px; font-weight: 700; border-radius: 3px;">
                        <i class="fa fa-user-circle"></i> CLIENT PORTAL
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- =========================================================================
         2. Transparent Overlay Header (media_1787505064142.png)
         ========================================================================= -->
    <header class="r-site-header" id="mainHeader">
        <div class="container-fluid px-3 px-md-4 px-lg-5">
            <div class="d-flex justify-content-between align-items-center">
                <!-- Left: Red MENU Button -->
                <div class="d-flex align-items-center">
                    <button class="btn btn-r-menu"
                            type="button"
                            data-bs-toggle="offcanvas"
                            data-bs-target="#offcanvasNav"
                            aria-controls="offcanvasNav">
                        <i class="fa fa-bars"></i>
                        <span>MENU</span>
                    </button>
                </div>

                <!-- Center: Brand Logo / Title & Subtitle Display -->
                <div class="text-center">
                    <a class="text-decoration-none d-inline-flex flex-column align-items-center" href="<?php echo $this->Html->url('/'); ?>">
                        <?php if ($logoDisplayMode === 'logo_only' && !empty($logoImage)): ?>
                            <img src="<?php echo $this->Html->url($logoImage); ?>" alt="<?php echo h($brandName); ?>" class="r-brand-logo-img" style="max-height: 52px; width: auto; object-fit: contain;">
                        <?php elseif ($logoDisplayMode === 'text_only' || empty($logoImage)): ?>
                            <span class="r-brand-logo-text"><?php echo h($brandName); ?></span>
                            <?php if (!empty($brandSubtitle)): ?>
                                <span class="r-brand-sub-text"><?php echo h($brandSubtitle); ?></span>
                            <?php endif; ?>
                        <?php else: // 'both' ?>
                            <img src="<?php echo $this->Html->url($logoImage); ?>" alt="<?php echo h($brandName); ?>" class="r-brand-logo-img mb-1" style="max-height: 44px; width: auto; object-fit: contain;">
                            <span class="r-brand-logo-text" style="font-size: 19px; line-height: 1.1;"><?php echo h($brandName); ?></span>
                            <?php if (!empty($brandSubtitle)): ?>
                                <span class="r-brand-sub-text" style="font-size: 9px;"><?php echo h($brandSubtitle); ?></span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </a>
                </div>

                <!-- Right: Red BOOK NOW Button -->
                <div>
                    <a href="<?php echo h($bookNowLink); ?>" class="btn-r-book" target="<?php echo h($bookNowTarget); ?>">
                        <span><?php echo h($bookNowText); ?></span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- =========================================================================
         3. Offcanvas Navigation Drawer
         ========================================================================= -->
    <div class="offcanvas offcanvas-start offcanvas-wv p-3" tabindex="-1" id="offcanvasNav" aria-labelledby="offcanvasNavLabel">
        <div class="offcanvas-header border-bottom pb-3 mb-3" style="border-color: #f1f5f9 !important;">
            <h5 class="offcanvas-title fw-bold text-uppercase" id="offcanvasNavLabel" style="letter-spacing: 1px; color: #d11242;">
                <i class="fa fa-building text-danger me-2"></i> <?php echo h($brandName); ?>
            </h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <?php
            $cCtrl = strtolower(isset($this->params['controller']) ? $this->params['controller'] : '');
            $cAct = strtolower(isset($this->params['action']) ? $this->params['action'] : '');
            $cUrl = isset($this->params->url) ? strtolower($this->params->url) : '';
            $isNavHome = $isThemeHomePage;
            $isNavCheckBill = ($cCtrl === 'bills' || strpos($cUrl, 'check_online') !== false || strpos($cUrl, 'online-bill') !== false || strpos($cUrl, 'check-online') !== false);
            $isNavProperties = ($cCtrl === 'properties');
            $isNavPaymentPlans = ($cCtrl === 'paymentplans');
            $isNavAgencies = ($cCtrl === 'agencies');
            $isNavAgents = ($cCtrl === 'agents');
            $isNavAbout = ($cCtrl === 'contents' || strpos($cUrl, 'about') !== false);
            $isNavContacts = ($cCtrl === 'contacts');
            ?>
            <ul class="nav flex-column wv-offcanvas-menu">
                <li class="nav-item">
                    <a class="nav-link <?php echo $isNavHome ? 'active' : ''; ?>" href="<?php echo $this->Html->url('/'); ?>"><i class="fa fa-home"></i> Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $isNavCheckBill ? 'active' : ''; ?>" href="<?php echo $this->Html->url('/bills/check_online'); ?>" style="<?php echo $isNavCheckBill ? 'color:#d11242;font-weight:800;' : ''; ?>">
                        <i class="fa fa-file-text-o text-danger"></i> Check Bill / آن لائن بل
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $isNavProperties ? 'active' : ''; ?>" href="<?php echo $this->Html->url('/Properties'); ?>"><i class="fa fa-building-o"></i> Properties for Sale</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $isNavPaymentPlans ? 'active' : ''; ?>" href="<?php echo $this->Html->url('/paymentplans'); ?>"><i class="fa fa-calculator"></i> Payment Plans</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $isNavAgencies ? 'active' : ''; ?>" href="<?php echo $this->Html->url('/Agencies'); ?>"><i class="fa fa-briefcase"></i> Agencies</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $isNavAgents ? 'active' : ''; ?>" href="<?php echo $this->Html->url('/Agents'); ?>"><i class="fa fa-users"></i> Agents</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $isNavAbout ? 'active' : ''; ?>" href="<?php echo $this->Html->url('/Contents/pages/About-Us'); ?>"><i class="fa fa-info-circle"></i> About Us</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $isNavContacts ? 'active' : ''; ?>" href="<?php echo $this->Html->url('/Contacts'); ?>"><i class="fa fa-envelope-o"></i> Contact Us</a>
                </li>
            </ul>

            <hr class="my-4" style="border-color: #f1f5f9;">

            <div class="d-grid gap-2">
                <a href="<?php echo $this->Html->url('/bills/check_online'); ?>" class="wv-offcanvas-client-btn mb-1" style="background: linear-gradient(135deg, #064e3b 0%, #047857 100%); border-color: #047857; color: #ffffff;">
                    <i class="fa fa-file-text-o"></i> CHECK BILL / بل چیک کریں
                </a>
                <a href="<?php echo $this->Html->url('/clients/login'); ?>" class="wv-offcanvas-client-btn">
                    <i class="fa fa-sign-in"></i> CLIENT PORTAL LOGIN
                </a>
                <a href="tel:<?php echo h($phone); ?>" class="wv-offcanvas-call-btn">
                    <i class="fa fa-phone"></i> CALL: <?php echo h($phone); ?>
                </a>
            </div>
        </div>
    </div>

    <?php
    $isThemeHomePage = (isset($this->params['controller']) && strtolower($this->params['controller']) === 'fronts') || (isset($page) && $page === 'home') || (empty($this->params['controller']) && empty($this->params['action']));
    ?>
    <?php if (!$isThemeHomePage && $tickerShowOnAllPages && $tickerEnabled && !empty($renderedTickerText)): ?>
    <!-- Inner Pages Flash News & Update Ticker Bar -->
    <div class="r-ticker-bar" style="background: <?php echo h($tickerBgColor); ?>; border-bottom: 2px solid <?php echo h($tickerBadgeBg); ?>; color: <?php echo h($tickerTextColor); ?>; margin-top: 0;">
        <div class="container-fluid px-3 px-md-4">
            <div class="d-flex align-items-center py-2">
                <div class="r-ticker-badge d-flex align-items-center flex-shrink-0" style="background: <?php echo h($tickerBadgeBg); ?>; color: <?php echo h($tickerBadgeColor); ?>; padding: 5px 12px; border-radius: 4px; font-weight: 800; font-size: 11px; letter-spacing: 0.06em; text-transform: uppercase; margin-right: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.25);">
                    <span class="r-ticker-pulse me-2"></span>
                    <span><?php echo $tickerBadgeText; ?></span>
                </div>
                <div class="r-ticker-content-wrapper flex-grow-1 overflow-hidden position-relative" style="white-space: nowrap; height: 30px; display: flex; align-items: center;">
                    <div class="r-ticker-track" style="animation-duration: <?php echo (int)$tickerSpeed; ?>s;">
                        <span class="r-ticker-text"><?php echo $renderedTickerText; ?></span>
                        <span class="r-ticker-text" aria-hidden="true"><?php echo $renderedTickerText; ?></span>
                    </div>
                </div>
                <?php if (!empty($tickerLink)): ?>
                <div class="ms-3 flex-shrink-0 d-none d-md-block">
                    <a href="<?php echo h($tickerLink); ?>" class="btn btn-xs text-uppercase fw-bold" style="background: rgba(255,255,255,0.12); color: #ffffff; border: 1px solid rgba(255,255,255,0.25); border-radius: 4px; font-size: 11px; padding: 3px 10px; transition: all 0.2s ease;">
                        <i class="fa fa-arrow-right me-1"></i> View
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Dynamic Content -->
    <main>
        <?php if ($isThemeHomePage): ?>
            <?php echo $this->fetch('content'); ?>
        <?php else: ?>
            <div class="r-inner-page-wrapper py-4 py-lg-5" style="background-color: #f8fafc; min-height: 65vh;">
                <div class="r-container">
                    <?php echo $this->fetch('content'); ?>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- =========================================================================
         4. Luxury Theme Footer
         ========================================================================= -->
    <footer class="wv-footer">
        <div class="r-container">
            <div class="row g-4 wv-footer-divider">
                <div class="col-12 col-lg-4">
                    <?php if (($logoDisplayMode === 'logo_only' || $logoDisplayMode === 'both') && !empty($logoImage)): ?>
                        <img src="<?php echo $this->Html->url($logoImage); ?>" alt="<?php echo h($brandName); ?>" style="max-height: 44px; width: auto; margin-bottom: 14px; display: block; object-fit: contain;">
                    <?php endif; ?>
                    <?php if ($logoDisplayMode !== 'logo_only'): ?>
                        <h3 class="wv-footer-title mb-1"><?php echo h($brandName); ?></h3>
                        <?php if (!empty($brandSubtitle)): ?>
                            <div class="wv-footer-subtitle"><?php echo h($brandSubtitle); ?></div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <p class="wv-footer-desc pe-lg-4 mt-2"><?php echo wv_render_rich_html($footerAbout); ?></p>
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <h4 class="wv-footer-title"><?php echo h($footerCol2Title); ?></h4>
                    <ul class="list-unstyled d-flex flex-column gap-2">
                        <li><a href="<?php echo $this->Html->url('/'); ?>">Home</a></li>
                        <li><a href="<?php echo $this->Html->url('/bills/check_online'); ?>" style="color:#10b981;font-weight:600;"><i class="fa fa-file-text-o"></i> Check Bill</a></li>
                        <li><a href="<?php echo $this->Html->url('/Properties'); ?>">Search Properties</a></li>
                        <li><a href="<?php echo $this->Html->url('/paymentplans'); ?>">Payment Plans</a></li>
                        <li><a href="<?php echo $this->Html->url('/Agencies'); ?>">Agencies</a></li>
                        <li><a href="<?php echo $this->Html->url('/Agents'); ?>">Agents</a></li>
                    </ul>
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <h4 class="wv-footer-title"><?php echo h($footerCol3Title); ?></h4>
                    <ul class="list-unstyled d-flex flex-column gap-2">
                        <li><a href="<?php echo $this->Html->url('/Contents/pages/About-Us'); ?>">About Us</a></li>
                        <li><a href="<?php echo $this->Html->url('/Contacts'); ?>">Contact Us</a></li>
                        <li><a href="<?php echo $this->Html->url('/clients/login'); ?>">Client Login</a></li>
                        <li><a href="<?php echo $this->Html->url('/Registers/index'); ?>">Register Account</a></li>
                    </ul>
                </div>
                <div class="col-12 col-md-6 col-lg-4">
                    <h4 class="wv-footer-title"><?php echo h($footerCol4Title); ?></h4>
                    <ul class="list-unstyled d-flex flex-column gap-2" style="font-size: 14px; color: #cbd5e1;">
                        <li style="display: flex; gap: 10px; align-items: flex-start;"><i class="fa fa-map-marker text-danger" style="margin-top: 4px; font-size: 16px;"></i> <span><?php echo h($address); ?></span></li>
                        <li style="display: flex; gap: 10px; align-items: center;"><i class="fa fa-phone text-danger"></i> <a href="tel:<?php echo h($phone); ?>"><?php echo h($phone); ?></a></li>
                        <li style="display: flex; gap: 10px; align-items: center;"><i class="fa fa-envelope text-danger"></i> <a href="mailto:<?php echo h($email); ?>"><?php echo h($email); ?></a></li>
                    </ul>
                </div>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center wv-footer-bottom">
                <div>
                    <?php echo $footerCopyright; ?>
                </div>
                <div>
                    Powered by <a href="<?php echo h($footerCreditUrl); ?>" target="_blank" rel="noopener"><?php echo h($footerCreditName); ?></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- jQuery & Core Framework Scripts -->
    <script>if(typeof jQuery === 'undefined'){ document.write('<script src="<?php echo $this->Html->url('/design1000/assets/js/jquery.js'); ?>"><\/script>'); }</script>
    <?php echo $this->fetch('script'); ?>
    <?php echo $this->Js->writeBuffer(); ?>

    <!-- Bootstrap 5 JS Bundle & Header Scroll Script -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateHeaderPosition() {
            if (document.body.classList.contains('r-inner-page-body')) {
                return;
            }
            var topBar = document.getElementById('topNotificationBar');
            var header = document.getElementById('mainHeader');
            if (header) {
                if (header.classList.contains('scrolled')) {
                    header.style.top = '0px';
                } else if (topBar && window.innerWidth >= 768 && topBar.offsetHeight > 0) {
                    header.style.top = topBar.offsetHeight + 'px';
                } else {
                    header.style.top = '0px';
                }
            }
        }
        window.addEventListener('DOMContentLoaded', updateHeaderPosition);
        window.addEventListener('resize', updateHeaderPosition);
        window.addEventListener('load', updateHeaderPosition);

        <?php if ($stickyHeaderEnabled || !empty($stickyHeaderMobileEnabled)): ?>
        window.addEventListener('scroll', function() {
            if (document.body.classList.contains('r-inner-page-body')) {
                return;
            }
            var header = document.getElementById('mainHeader');
            if (header) {
                if (window.scrollY > 80) {
                    header.classList.add('scrolled');
                    header.style.top = '0px';
                } else {
                    header.classList.remove('scrolled');
                    updateHeaderPosition();
                }
            }
        });
        <?php endif; ?>

        // Smooth scroll for in-page anchors
        $(document).on('click', 'a[href^="#"]', function(e) {
            var targetId = this.getAttribute('href');
            if (targetId && targetId.length > 1 && targetId !== '#') {
                var $target = $(targetId);
                if ($target.length) {
                    e.preventDefault();
                    $('html, body').stop().animate({
                        scrollTop: $target.offset().top - 80
                    }, 400);
                }
            }
        });
    </script>
</body>
</html>
