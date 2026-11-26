<?php
/**
 *
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

?>
<?php
$wvAdminController=isset($this->params['controller']) ? strtolower((string)$this->params['controller']) : '';
$wvAdminNeedsMultiselect=in_array($wvAdminController,array('properties','users','recoveries'),true);
?>
<!DOCTYPE html>
  <html lang="<?php echo h($configLanguage);?>" dir="<?php echo h($dirType);?>">
    <head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
	  <meta name="description" content="<?php echo h($siteDescription);?>" />
	  <meta name="author" content="<?php echo h($siteName);?>" />
	  <meta name="google-translate-customization" content="839d71f7ff6044d0-328a2dc5159d6aa2-gd17de6447c9ba810-f">
	  <?php echo $this->Html->charset();?>
	  <title><?php echo h($siteTitle);?></title>
<?php
$siteFaviconFile = !empty($siteFavicon) ? $siteFavicon : '';
$ogLogo = !empty($frontLogo) ? $frontLogo : 'organisation-logo-20260511101817-recovered.png';
if (strpos($ogLogo, 'http://') === 0 || strpos($ogLogo, 'https://') === 0) {
    $ogImageUrl = $ogLogo;
} else {
    $ogImageUrl = Router::url('/img/' . ltrim($ogLogo, '/'), true);
}
$ogSiteName = !empty($siteName) ? $siteName : 'White Villas';
$ogPageTitle = !empty($title_for_layout) ? ($title_for_layout . ' — ' . $ogSiteName) : (!empty($siteTitle) ? $siteTitle : $ogSiteName);
$ogPageDesc = !empty($siteDescription) ? $siteDescription : 'White Villas — Premier Real Estate, Luxury Lodges & Verified Investment Opportunities.';
$ogPageUrl = Router::url(null, true);
?>
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
	<meta name="description" content="<?php echo h($siteDescription);?>"/>
	
	<?php
		echo $this->Html->meta('icon');
		echo $this->Html->css('font-awesome.min');
		  echo $this->Html->css('/design2000/assets/css/bootstrap.min');
		
		  echo $this->Html->css('/design2000/assets/css/core');
		  echo $this->Html->css('/design2000/assets/css/components');
		  echo $this->Html->css('/design2000/assets/icons/fontawesome/styles.min');
		echo $this->Html->css('validationEngine.jquery');
		if(!empty($wvAdminNeedsMultiselect)){ echo $this->Html->css('bootstrap-multiselect'); }
		echo $this->Html->css('bootstrap-datetimepicker.min');
		echo $this->Html->css('style');
		echo $this->fetch('meta');		
		echo $this->fetch('css');
?>
<style type="text/css">
/* =========================================================================
   White Villas - Senior Executive Backend Design System
   Eliminates unwanted spaces, fixes rough backgrounds, perfects topbar & sidebar
   ========================================================================= */

/* Desktop & Mobile Toggle Controls */
#toggle-sidebar-desktop {
    display: none !important;
}
#toggle-sidebar {
    display: inline-flex !important;
}

/* Base Body & Layout */
body {
    background: #f8fafc !important;
    color: #0f172a !important;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
}

.page-container {
    background: transparent !important;
    margin: 0 !important;
    padding: 0 !important;
    width: 100% !important;
}

/* =========================================================================
   1. UPPER SIDE (TOPBAR / NAVBAR)
   ========================================================================= */
.navbar.navbar-toggleable-md,
.wv-admin-topbar,
body.wv-admin-theme .wv-admin-topbar {
    align-items: center !important;
    background: linear-gradient(90deg, #070d19 0%, #0f172a 45%, #131d36 100%) !important;
    border: 0 !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
    box-shadow: 0 4px 16px -2px rgba(0, 0, 0, 0.25), 0 1px 3px 0 rgba(0, 0, 0, 0.10) !important;
    box-sizing: border-box !important;
    color: #ffffff !important;
    display: flex !important;
    flex-direction: row !important;
    flex-wrap: nowrap !important;
    height: 58px !important;
    margin: 0 !important;
    max-height: 58px !important;
    min-height: 58px !important;
    padding: 0 16px !important;
    position: sticky !important;
    top: 0 !important;
    width: 100% !important;
    z-index: 1035 !important;
}

/* Topbar Mobile Toggle */
.navbar #toggle-sidebar,
.wv-admin-topbar #toggle-sidebar {
    align-items: center !important;
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.10) !important;
    border-radius: 8px !important;
    color: #cbd5e1 !important;
    cursor: pointer !important;
    display: inline-flex !important;
    flex: 0 0 36px !important;
    height: 36px !important;
    justify-content: center !important;
    margin: 0 12px 0 0 !important;
    order: 1 !important;
    padding: 0 !important;
    transition: all 0.18s ease !important;
    width: 36px !important;
}

.navbar #toggle-sidebar:hover,
.wv-admin-topbar #toggle-sidebar:hover {
    background: rgba(255, 255, 255, 0.12) !important;
    border-color: rgba(255, 255, 255, 0.20) !important;
    color: #ffffff !important;
}

.navbar #toggle-sidebar i,
.wv-admin-topbar #toggle-sidebar i {
    color: #94a3b8 !important;
    font-size: 14px !important;
}

.navbar #toggle-sidebar:hover i,
.wv-admin-topbar #toggle-sidebar:hover i {
    color: #ffffff !important;
}

.navbar .navbar-toggler-right {
    display: none !important;
}

/* Brand Logo */
.navbar .navbar-brand.logo,
.wv-admin-topbar .navbar-brand.logo {
    align-items: center !important;
    background: transparent !important;
    border: 0 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    color: #ffffff !important;
    display: inline-flex !important;
    flex: 0 0 auto !important;
    gap: 10px !important;
    height: 100% !important;
    margin: 0 14px 0 0 !important;
    min-width: 0 !important;
    order: 2 !important;
    padding: 0 !important;
    text-decoration: none !important;
}

.navbar .navbar-brand.logo span,
.wv-admin-topbar .navbar-brand.logo span {
    color: #ffffff !important;
    font-size: 15px !important;
    font-weight: 700 !important;
    letter-spacing: -0.2px !important;
    white-space: nowrap !important;
}

.wv-topbar-version-pill,
.navbar .navbar-brand.logo .badge {
    background: rgba(37, 99, 235, 0.22) !important;
    border: 1px solid rgba(59, 130, 246, 0.35) !important;
    border-radius: 999px !important;
    color: #93c5fd !important;
    font-size: 10px !important;
    font-weight: 700 !important;
    letter-spacing: 0.3px !important;
    line-height: 1.3 !important;
    padding: 2px 8px !important;
    text-transform: uppercase !important;
    white-space: nowrap !important;
}

/* Breadcrumbs Center Container */
.wv-admin-header-center {
    align-items: center !important;
    display: flex !important;
    flex: 1 1 auto !important;
    min-width: 0 !important;
    order: 3 !important;
    padding: 0 12px !important;
}

.wv-admin-breadcrumbs {
    align-items: center !important;
    color: #64748b !important;
    display: flex !important;
    font-size: 12.5px !important;
    font-weight: 500 !important;
    gap: 6px !important;
    white-space: nowrap !important;
}

.wv-admin-breadcrumbs a {
    color: #94a3b8 !important;
    font-weight: 500 !important;
    text-decoration: none !important;
    transition: color 0.15s ease !important;
}

.wv-admin-breadcrumbs a:hover {
    color: #ffffff !important;
}

.wv-admin-breadcrumbs span {
    color: #f1f5f9 !important;
    font-weight: 600 !important;
}

.wv-admin-breadcrumb-sep {
    color: #475569 !important;
    display: inline-flex !important;
    font-size: 10px !important;
    margin: 0 4px !important;
}

/* Navbar Collapse & Flex In-Flow Actions */
.navbar #navbarNav,
.wv-admin-topbar #navbarNav,
body.wv-admin-theme .wv-admin-topbar #navbarNav {
    align-items: center !important;
    background: transparent !important;
    border: 0 !important;
    box-shadow: none !important;
    display: flex !important;
    flex: 0 0 auto !important;
    justify-content: flex-end !important;
    margin: 0 0 0 auto !important;
    order: 4 !important;
    padding: 0 !important;
    position: static !important;
    right: auto !important;
    top: auto !important;
    width: auto !important;
    z-index: auto !important;
}

.navbar .navbar-nav.ml-auto,
.wv-admin-topbar-actions {
    align-items: center !important;
    display: flex !important;
    flex-direction: row !important;
    flex-wrap: nowrap !important;
    gap: 8px !important;
    list-style: none !important;
    margin: 0 !important;
    padding: 0 !important;
}

.navbar .navbar-nav.ml-auto > .nav-item,
.wv-admin-topbar-actions > .nav-item {
    align-items: center !important;
    display: inline-flex !important;
    flex: 0 0 auto !important;
    margin: 0 !important;
    padding: 0 !important;
    position: relative !important;
}

/* Quick Page Search in Topbar */
.wv-admin-quick-search {
    align-items: center !important;
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.10) !important;
    border-radius: 999px !important;
    box-sizing: border-box !important;
    display: inline-flex !important;
    gap: 8px !important;
    height: 35px !important;
    min-height: 35px !important;
    padding: 0 10px 0 12px !important;
    transition: all 0.22s ease !important;
    width: 200px !important;
}

.wv-admin-quick-search:focus-within {
    background: rgba(255, 255, 255, 0.09) !important;
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25) !important;
    width: 245px !important;
}

.wv-admin-quick-search i {
    color: #94a3b8 !important;
    font-size: 12px !important;
}

.wv-admin-quick-search:focus-within i {
    color: #60a5fa !important;
}

.wv-admin-quick-search input {
    background: transparent !important;
    border: 0 !important;
    box-shadow: none !important;
    color: #ffffff !important;
    font-size: 12.5px !important;
    height: 100% !important;
    margin: 0 !important;
    outline: none !important;
    padding: 0 !important;
    width: 100% !important;
}

.wv-admin-quick-search input::placeholder {
    color: #64748b !important;
    font-size: 12px !important;
}

.wv-admin-quick-search-btn {
    align-items: center !important;
    background: transparent !important;
    border: 0 !important;
    border-radius: 50% !important;
    color: #94a3b8 !important;
    cursor: pointer !important;
    display: inline-flex !important;
    flex: 0 0 22px !important;
    height: 22px !important;
    justify-content: center !important;
    padding: 0 !important;
    transition: all 0.15s ease !important;
    width: 22px !important;
}

.wv-admin-quick-search-btn:hover {
    background: rgba(255, 255, 255, 0.12) !important;
    color: #ffffff !important;
}

/* Icon Buttons */
.wv-admin-icon-btn,
.wv-admin-theme-dropdown > a.wv-admin-icon-btn {
    align-items: center !important;
    background: rgba(255, 255, 255, 0.04) !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    border-radius: 8px !important;
    box-shadow: none !important;
    color: #cbd5e1 !important;
    cursor: pointer !important;
    display: inline-flex !important;
    flex: 0 0 35px !important;
    height: 35px !important;
    justify-content: center !important;
    margin: 0 !important;
    min-height: 35px !important;
    min-width: 35px !important;
    padding: 0 !important;
    position: relative !important;
    text-decoration: none !important;
    transition: all 0.18s ease !important;
    width: 35px !important;
}

.wv-admin-icon-btn:hover,
.wv-admin-icon-btn:focus,
.wv-admin-theme-dropdown > a.wv-admin-icon-btn:hover,
.wv-admin-theme-dropdown > a.wv-admin-icon-btn:focus {
    background: rgba(255, 255, 255, 0.12) !important;
    border-color: rgba(255, 255, 255, 0.20) !important;
    color: #ffffff !important;
    transform: translateY(-1px);
}

.wv-admin-icon-btn i {
    color: #94a3b8 !important;
    font-size: 13.5px !important;
}

.wv-admin-icon-btn:hover i {
    color: #ffffff !important;
}

.wv-admin-icon-btn i.fa-trash-o {
    color: #f87171 !important;
}

.wv-admin-icon-btn:hover i.fa-trash-o {
    color: #ef4444 !important;
}

/* User Profile Badge (Topbar Avatar) */
#navbarDropdownMenuLink {
    align-items: center !important;
    background: transparent !important;
    border: 0 !important;
    border-radius: 50% !important;
    box-shadow: none !important;
    cursor: pointer !important;
    display: inline-flex !important;
    height: 36px !important;
    justify-content: center !important;
    margin: 0 0 0 2px !important;
    min-height: 36px !important;
    padding: 0 !important;
    text-decoration: none !important;
    width: 36px !important;
}

#navbarDropdownMenuLink:after {
    display: none !important;
}

.wv-admin-profile-badge {
    align-items: center !important;
    background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%) !important;
    background-position: center !important;
    background-repeat: no-repeat !important;
    background-size: cover !important;
    border: 2px solid rgba(255, 255, 255, 0.35) !important;
    border-radius: 50% !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25) !important;
    box-sizing: border-box !important;
    color: #ffffff !important;
    display: inline-flex !important;
    font-size: 13.5px !important;
    font-weight: 800 !important;
    height: 36px !important;
    justify-content: center !important;
    line-height: 1 !important;
    text-transform: uppercase !important;
    transition: all 0.2s ease !important;
    width: 36px !important;
}

.wv-admin-profile-badge.has-photo {
    color: transparent !important;
    font-size: 0 !important;
}

.wv-admin-profile-badge:hover {
    border-color: #60a5fa !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3), 0 3px 10px rgba(0, 0, 0, 0.3) !important;
    transform: scale(1.04);
}

/* Luxury Modern Dropdown Menus */
.dropdown-menu.dropdown-menu-right {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 12px !important;
    box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.16), 0 4px 6px -2px rgba(15, 23, 42, 0.05) !important;
    box-sizing: border-box !important;
    left: auto !important;
    margin-top: 8px !important;
    min-width: 220px !important;
    padding: 6px !important;
    right: 0 !important;
    z-index: 1075 !important;
}

.table .dropdown-menu,
.table-responsive .dropdown-menu {
    margin: 0 !important;
    z-index: 99999 !important;
}

/* Explicit Dropup overrides for upside rendering */
.dropup .dropdown-menu,
.table .dropup .dropdown-menu,
.btn-group.dropup .dropdown-menu,
.table-responsive .dropup .dropdown-menu {
    top: auto !important;
    bottom: 100% !important;
    margin-top: 0 !important;
    margin-bottom: 6px !important;
}

.dropup .dropdown-toggle .caret,
.btn-group.dropup .dropdown-toggle .caret {
    border-bottom: 4px solid !important;
    border-top: 0 !important;
    content: "" !important;
}

.table tr.dropdown-open,
.table tr.dropdown-open > td,
.table tr.dropdown-open > th,
td.dropdown-open,
.table .btn-group.open,
.table .btn-group.dropup.open {
    position: relative !important;
    z-index: 1070 !important;
}

.table-responsive.dropdown-active {
    overflow-y: visible !important;
}

.dropdown-menu .dropdown-item-text {
    background: #f8fafc !important;
    border: 1px solid #f1f5f9 !important;
    border-radius: 7px !important;
    color: #475569 !important;
    font-size: 11.5px !important;
    font-weight: 600 !important;
    margin-bottom: 4px !important;
    padding: 7px 10px !important;
}

.dropdown-menu .dropdown-item-text strong {
    color: #0f172a !important;
    font-weight: 700 !important;
}

.dropdown-menu .dropdown-item {
    align-items: center !important;
    border-radius: 7px !important;
    color: #334155 !important;
    cursor: pointer !important;
    display: flex !important;
    font-size: 12.5px !important;
    font-weight: 600 !important;
    gap: 9px !important;
    min-height: 36px !important;
    padding: 7px 11px !important;
    text-decoration: none !important;
    transition: all 0.15s ease !important;
}

.dropdown-menu .dropdown-item:hover,
.dropdown-menu .dropdown-item:focus {
    background: #f1f5f9 !important;
    color: #0f172a !important;
    text-decoration: none !important;
}

.dropdown-menu .dropdown-item i {
    color: #64748b !important;
    font-size: 13px !important;
    text-align: center !important;
    transition: color 0.15s ease;
    width: 18px !important;
}

.dropdown-menu .dropdown-item:hover i {
    color: #2563eb !important;
}

.dropdown-menu .dropdown-item i.fa-trash-o {
    color: #ef4444 !important;
}

.dropdown-menu .dropdown-item:hover i.fa-trash-o {
    color: #dc2626 !important;
}

.dropdown-menu .dropdown-divider {
    border-top: 1px solid #f1f5f9 !important;
    margin: 6px 0 !important;
}

/* =========================================================================
   2. SIDEBAR ("sidebare")
   ========================================================================= */
#sidebar-main.sidebar,
body.wv-admin-theme #sidebar-main.sidebar {
    background: #090e17 !important;
    border: 0 !important;
    border-right: 1px solid rgba(255, 255, 255, 0.07) !important;
    box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15) !important;
    box-sizing: border-box !important;
    color: #ffffff !important;
    display: block !important;
    flex: 0 0 250px !important;
    height: 100vh !important;
    max-height: 100vh !important;
    max-width: 250px !important;
    min-width: 250px !important;
    overflow-x: hidden !important;
    overflow-y: auto !important;
    position: sticky !important;
    top: 0 !important;
    transition: flex 0.22s ease, width 0.22s ease, max-width 0.22s ease, min-width 0.22s ease !important;
    width: 250px !important;
    z-index: 1000 !important;
}

/* Custom Sleek Scrollbar for Sidebar */
#sidebar-main.sidebar::-webkit-scrollbar,
#sidebar-main .sidebar-content::-webkit-scrollbar {
    width: 5px !important;
}

#sidebar-main.sidebar::-webkit-scrollbar-thumb,
#sidebar-main .sidebar-content::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.10) !important;
    border-radius: 999px !important;
}

#sidebar-main.sidebar::-webkit-scrollbar-thumb:hover,
#sidebar-main .sidebar-content::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.20) !important;
}

#sidebar-main.sidebar,
#sidebar-main .sidebar-content {
    scrollbar-color: rgba(255, 255, 255, 0.12) transparent !important;
    scrollbar-width: thin !important;
}

#sidebar-main .sidebar-content {
    background: transparent !important;
    box-sizing: border-box !important;
    min-height: 100% !important;
    width: 100% !important;
}

.wv-admin-sidebar-shell {
    box-sizing: border-box !important;
    padding: 12px 10px 24px !important;
    position: relative !important;
    width: 100% !important;
}

/* Sidebar Brand Card - Removed per user request (topbar already displays site brand) */
.wv-admin-sidebar-brand {
    display: none !important;
}

.wv-admin-sidebar-mobile-close {
    align-items: center !important;
    background: rgba(255, 255, 255, 0.08) !important;
    border: 1px solid rgba(255, 255, 255, 0.14) !important;
    border-radius: 7px !important;
    color: #cbd5e1 !important;
    cursor: pointer !important;
    display: none !important;
    flex: 0 0 30px !important;
    font-size: 14px !important;
    height: 30px !important;
    justify-content: center !important;
    padding: 0 !important;
    position: absolute !important;
    right: 10px !important;
    top: 10px !important;
    transition: all 0.15s ease !important;
    width: 30px !important;
    z-index: 10 !important;
}

.wv-admin-sidebar-mobile-close:hover {
    background: #ef4444 !important;
    border-color: #ef4444 !important;
    color: #ffffff !important;
}

/* Sidebar Search Box */
.wv-admin-sidebar-search-wrap {
    align-items: center !important;
    background: rgba(255, 255, 255, 0.03) !important;
    border: 1px solid rgba(255, 255, 255, 0.07) !important;
    border-radius: 8px !important;
    box-sizing: border-box !important;
    display: flex !important;
    gap: 8px !important;
    height: 34px !important;
    margin-bottom: 10px !important;
    padding: 0 10px !important;
    transition: all 0.18s ease !important;
}

.wv-admin-sidebar-search-wrap:focus-within {
    background: rgba(255, 255, 255, 0.06) !important;
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2) !important;
}

.wv-admin-sidebar-search-icon {
    color: #64748b !important;
    font-size: 12px !important;
}

.wv-admin-sidebar-search {
    background: transparent !important;
    border: 0 !important;
    box-shadow: none !important;
    color: #f1f5f9 !important;
    font-size: 12px !important;
    height: 100% !important;
    outline: none !important;
    padding: 0 !important;
    width: 100% !important;
}

.wv-admin-sidebar-search::placeholder {
    color: #64748b !important;
    font-size: 11.5px !important;
}

.wv-admin-sidebar-search-clear {
    align-items: center !important;
    background: transparent !important;
    border: 0 !important;
    border-radius: 50% !important;
    color: #94a3b8 !important;
    cursor: pointer !important;
    display: none;
    flex: 0 0 20px !important;
    font-size: 11px !important;
    height: 20px !important;
    justify-content: center !important;
    line-height: 1 !important;
    margin-left: auto !important;
    padding: 0 !important;
    transition: all 0.15s ease !important;
    width: 20px !important;
}

.wv-admin-sidebar-search-clear:hover {
    background: rgba(255, 255, 255, 0.15) !important;
    color: #ffffff !important;
}

/* Sidebar Navigation Tree */
#sidebar-main .navigation,
body.wv-admin-theme #sidebar-main .navigation {
    box-sizing: border-box !important;
    display: block !important;
    list-style: none !important;
    margin: 0 !important;
    padding: 0 !important;
    width: 100% !important;
}

#sidebar-main .navigation > li,
body.wv-admin-theme #sidebar-main .navigation > li {
    box-sizing: border-box !important;
    display: block;
    margin: 0 0 2px 0 !important;
    padding: 0 !important;
    position: relative !important;
    width: 100% !important;
}

#sidebar-main .navigation li.menu-item-hidden,
body.wv-admin-theme #sidebar-main .navigation li.menu-item-hidden {
    display: none !important;
}

/* Sidebar Primary Menu Links */
#sidebar-main .navigation > li > a,
body.wv-admin-theme #sidebar-main .navigation > li > a {
    align-items: center !important;
    background: transparent !important;
    border-radius: 7px !important;
    box-sizing: border-box !important;
    color: #94a3b8 !important;
    display: flex !important;
    font-size: 12.5px !important;
    font-weight: 600 !important;
    gap: 9px !important;
    justify-content: flex-start !important;
    line-height: 1.3 !important;
    min-height: 38px !important;
    padding: 6px 10px !important;
    position: relative !important;
    text-align: left !important;
    text-decoration: none !important;
    transition: background 0.15s ease, color 0.15s ease, transform 0.15s ease !important;
    white-space: normal !important;
    width: 100% !important;
}

/* Sidebar Item Icons */
#sidebar-main .navigation > li > a > i,
body.wv-admin-theme #sidebar-main .navigation > li > a > i {
    align-items: center !important;
    background: rgba(255, 255, 255, 0.04) !important;
    border-radius: 6px !important;
    box-sizing: border-box !important;
    color: #94a3b8 !important;
    display: inline-flex !important;
    flex: 0 0 28px !important;
    float: none !important;
    font-size: 13px !important;
    height: 28px !important;
    justify-content: center !important;
    left: auto !important;
    margin: 0 !important;
    position: static !important;
    top: auto !important;
    transition: all 0.15s ease !important;
    width: 28px !important;
}

/* Sidebar Item Label */
#sidebar-main .navigation > li > a > span,
body.wv-admin-theme #sidebar-main .navigation > li > a > span {
    color: inherit !important;
    display: inline-block !important;
    flex: 1 1 auto !important;
    font-size: 12.5px !important;
    font-weight: inherit !important;
    line-height: 1.25 !important;
    margin: 0 !important;
    min-width: 0 !important;
    overflow-wrap: anywhere !important;
    white-space: normal !important;
}

/* Hover State */
#sidebar-main .navigation > li > a:hover,
body.wv-admin-theme #sidebar-main .navigation > li > a:hover {
    background: rgba(255, 255, 255, 0.05) !important;
    color: #f8fafc !important;
    text-decoration: none !important;
    transform: none !important;
}

#sidebar-main .navigation > li > a:hover > i,
body.wv-admin-theme #sidebar-main .navigation > li > a:hover > i {
    background: rgba(37, 99, 235, 0.18) !important;
    color: #60a5fa !important;
}

/* Active & Open States (NO HARSH RED!) */
#sidebar-main .navigation > li.active > a,
#sidebar-main .navigation > li.open > a,
body.wv-admin-theme #sidebar-main .navigation > li.active > a,
body.wv-admin-theme #sidebar-main .navigation > li.open > a {
    background: rgba(37, 99, 235, 0.12) !important;
    color: #ffffff !important;
    font-weight: 700 !important;
    text-decoration: none !important;
    transform: none !important;
}

#sidebar-main .navigation > li.active > a > i,
#sidebar-main .navigation > li.open > a > i,
body.wv-admin-theme #sidebar-main .navigation > li.active > a > i,
body.wv-admin-theme #sidebar-main .navigation > li.open > a > i {
    background: #2563eb !important;
    box-shadow: 0 2px 6px rgba(37, 99, 235, 0.35) !important;
    color: #ffffff !important;
}

/* Submenu Arrow Indicator (has-ul) */
#sidebar-main .navigation > li > a.has-ul,
body.wv-admin-theme #sidebar-main .navigation > li > a.has-ul {
    padding-right: 28px !important;
    position: relative !important;
}

#sidebar-main .navigation > li > a.has-ul:after,
body.wv-admin-theme #sidebar-main .navigation > li > a.has-ul:after {
    color: #64748b !important;
    content: "\f105" !important;
    display: block !important;
    font-family: "FontAwesome" !important;
    font-size: 11px !important;
    font-weight: 900 !important;
    position: absolute !important;
    right: 11px !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    transition: transform 0.2s ease, color 0.15s ease !important;
}

#sidebar-main .navigation > li.open > a.has-ul:after,
body.wv-admin-theme #sidebar-main .navigation > li.open > a.has-ul:after {
    color: #94a3b8 !important;
    transform: translateY(-50%) rotate(90deg) !important;
}

/* Submenu Lists - Collapsed/unexpanded by default */
#sidebar-main .navigation > li ul,
body.wv-admin-theme #sidebar-main .navigation > li ul {
    background: rgba(0, 0, 0, 0.22) !important;
    border: 1px solid rgba(255, 255, 255, 0.04) !important;
    border-left: 2px solid #2563eb !important;
    border-radius: 7px !important;
    box-shadow: none !important;
    box-sizing: border-box !important;
    display: none;
    list-style: none !important;
    margin: 3px 0 5px 12px !important;
    padding: 3px 4px !important;
}

/* Strictly hide collapsed submenus */
#sidebar-main .navigation ul.hidden-ul,
#sidebar-main .navigation > li ul.hidden-ul,
body.wv-admin-theme #sidebar-main .navigation ul.hidden-ul,
body.wv-admin-theme #sidebar-main .navigation > li ul.hidden-ul {
    display: none !important;
}

/* Open submenus */
#sidebar-main .navigation > li.open > ul:not(.hidden-ul),
#sidebar-main .navigation > li.active > ul:not(.hidden-ul),
body.wv-admin-theme #sidebar-main .navigation > li.open > ul:not(.hidden-ul),
body.wv-admin-theme #sidebar-main .navigation > li.active > ul:not(.hidden-ul) {
    display: block;
}

#sidebar-main .navigation li ul li,
body.wv-admin-theme #sidebar-main .navigation li ul li {
    box-sizing: border-box !important;
    display: block;
    margin: 0 0 1px 0 !important;
    padding: 0 !important;
}

#sidebar-main .navigation li ul li:last-child,
body.wv-admin-theme #sidebar-main .navigation li ul li:last-child {
    margin-bottom: 0 !important;
}

#sidebar-main .navigation li ul li > a,
body.wv-admin-theme #sidebar-main .navigation li ul li > a {
    align-items: center !important;
    background: transparent !important;
    border-radius: 5px !important;
    box-sizing: border-box !important;
    color: #94a3b8 !important;
    display: flex !important;
    font-size: 11.5px !important;
    font-weight: 500 !important;
    gap: 7px !important;
    min-height: 31px !important;
    padding: 5px 8px !important;
    text-decoration: none !important;
    transition: background 0.15s ease, color 0.15s ease !important;
    white-space: normal !important;
}

#sidebar-main .navigation li ul li > a:hover,
body.wv-admin-theme #sidebar-main .navigation li ul li > a:hover {
    background: rgba(255, 255, 255, 0.05) !important;
    color: #ffffff !important;
    text-decoration: none !important;
}

#sidebar-main .navigation li ul li.active > a,
body.wv-admin-theme #sidebar-main .navigation li ul li.active > a {
    background: rgba(37, 99, 235, 0.20) !important;
    color: #ffffff !important;
    font-weight: 600 !important;
    text-decoration: none !important;
}

#sidebar-main .navigation li ul li > a > i,
body.wv-admin-theme #sidebar-main .navigation li ul li > a > i {
    color: #60a5fa !important;
    font-size: 11px !important;
    text-align: center !important;
    width: 16px !important;
}

/* =========================================================================
   3. COLLAPSED SIDEBAR MODE
   ========================================================================= */
#sidebar-main.sidebar.sidebar-collapsed,
body.wv-admin-theme #sidebar-main.sidebar.sidebar-collapsed {
    flex: 0 0 68px !important;
    max-width: 68px !important;
    min-width: 68px !important;
    width: 68px !important;
}

#sidebar-main.sidebar.sidebar-collapsed .wv-admin-sidebar-shell {
    padding: 10px 6px !important;
}

#sidebar-main.sidebar.sidebar-collapsed .wv-admin-sidebar-brand,
#sidebar-main.sidebar.sidebar-collapsed .wv-admin-sidebar-search-wrap,
#sidebar-main.sidebar.sidebar-collapsed .navigation > li > a > span,
#sidebar-main.sidebar.sidebar-collapsed .navigation > li > a.has-ul:after,
#sidebar-main.sidebar.sidebar-collapsed .navigation > li > ul {
    display: none !important;
}

#sidebar-main.sidebar.sidebar-collapsed .navigation > li > a {
    align-items: center !important;
    justify-content: center !important;
    min-height: 40px !important;
    padding: 6px 0 !important;
    transform: none !important;
}

#sidebar-main.sidebar.sidebar-collapsed .navigation > li > a > i {
    flex: 0 0 32px !important;
    height: 32px !important;
    margin: 0 auto !important;
    width: 32px !important;
}

/* =========================================================================
   4. CONTENT & UNWANTED SPACING RESETS
   ========================================================================= */
@media (min-width: 992px) {
    .page-content {
        align-items: stretch !important;
        box-sizing: border-box !important;
        display: flex !important;
        flex-direction: row !important;
        margin: 0 !important;
        min-height: calc(100vh - 58px) !important;
        padding: 0 !important;
        position: relative !important;
        width: 100% !important;
    }

    .content-wrapper,
    body.wv-admin-theme .content-wrapper {
        background: transparent !important;
        box-sizing: border-box !important;
        display: block !important;
        flex: 1 1 auto !important;
        margin: 0 !important;
        max-width: calc(100% - 250px) !important;
        min-width: 0 !important;
        padding: 0 !important;
        transition: max-width 0.22s ease, width 0.22s ease !important;
        width: calc(100% - 250px) !important;
    }

    #sidebar-main.sidebar.sidebar-collapsed + .content-wrapper,
    body.wv-admin-theme #sidebar-main.sidebar.sidebar-collapsed + .content-wrapper {
        max-width: calc(100% - 68px) !important;
        width: calc(100% - 68px) !important;
    }

    .content.wv-admin-dashboard-shell,
    body.wv-admin-theme .content {
        box-sizing: border-box !important;
        margin: 0 !important;
        padding: 22px 26px 36px 26px !important;
        width: 100% !important;
    }
}

@media (max-width: 991px) {
    .content-wrapper,
    body.wv-admin-theme .content-wrapper {
        margin: 0 !important;
        max-width: 100% !important;
        padding: 0 !important;
        width: 100% !important;
    }

    .content.wv-admin-dashboard-shell,
    body.wv-admin-theme .content {
        box-sizing: border-box !important;
        padding: 14px 14px 28px 14px !important;
        width: 100% !important;
    }
}
</style>
<?php
                echo $this->Html->css('wv-theme-tokens');
                echo $this->Html->css('wv-urdu-fonts.css?v=20260901');
                echo $this->Html->css('wv-admin-theme.css?v=20260522-filter-selects');
                echo $this->Html->css('wv-admin-media-picker');
                echo $this->Html->css('wv-admin-form-polish.css?v=20260521-button-theme');
                echo $this->Html->css('wv-whatsapp-portal.css?v=20260518');
                echo $this->Html->css('admin/admin-base');
                echo $this->Html->css('admin/admin-layout.css?v=20261123-search-v3');
                echo $this->Html->css('admin/admin-components.css?v=20261123-pro-ui');
                echo $this->Html->css('admin/admin-themes.css?v=20261123-pro-ui');
                echo $this->Html->css('admin/admin-responsive.css?v=20261123-search-v3');
                echo $this->Html->css('/design2000/assets/css/admin-pro-upgrade.css?v=20261126-pro-v2');
                if(!empty($themeDynamicCss)){ echo '<style type="text/css" id="wv-dynamic-theme">'.$themeDynamicCss.'</style>'; }
                echo $this->Html->script('/design800/js/plugins/jquery-1.11.2.min');
		 // echo $this->Html->script('/design2000/lib/js/jquery.min');
		 echo $this->Html->script('bootstrap.min');
                echo $this->Html->script('jquery.validationEngine-en');
                echo $this->Html->script('jquery.validationEngine');
		
		  echo $this->Html->script('/design2000/lib/js/tether.min');
		  //echo $this->Html->script('/design2000/lib/js/bootstrap.min');
		  echo $this->Html->script('/design2000/assets/js/app.min.js?v=20261123-accordion-v3');
		
		if(!empty($wvAdminNeedsMultiselect)){ echo $this->Html->script('bootstrap-multiselect'); }
		echo $this->Html->script('moment-with-locales');
		echo $this->Html->script('bootstrap-datetimepicker.min');
		echo $this->Html->script('waiting-dialog.min');
		echo $this->Html->script('main.custom.min');
		echo $this->Html->script('wv-theme');
                echo '<script type="text/javascript">window.WV_ADMIN_MEDIA_PICKER='.json_encode(array(
                    'pickerUrl' => $this->Html->url(array('plugin'=>'admin','controller'=>'media_galleries','action'=>'picker')),
                    'labels' => array(
                        'title' => __('Select from Media Gallery'),
                        'search' => __('Search media'),
                        'searchButton' => __('Search'),
                        'all' => __('All'),
                        'images' => __('Images'),
                        'documents' => __('Documents'),
                        'loading' => __('Loading media...'),
                        'empty' => __('No media found.'),
                        'loadFailed' => __('Media could not be loaded.'),
                        'use' => __('Use File'),
                        'select' => __('Select from Gallery'),
                        'clear' => __('Clear'),
                        'notReady' => __('Media Gallery picker is not ready.'),
                        'browserUnsupported' => __('Your browser cannot attach gallery files automatically. Please download and upload manually.'),
                        'selectFailed' => __('Selected media could not be attached to this upload field.')
                    )
                )).';</script>';
		echo '<script type="text/javascript" src="'.$this->Html->url('/js/wv-admin-media-picker.js?v=20260824-multi-picker').'"></script>';
		echo $this->Html->script('wv-admin-form-polish');
		echo $this->Html->script('wv-whatsapp-portal.js?v=20260518');
                echo $this->Html->script('admin/admin-ui.js?v=20261123-search-v3');
                echo $this->Html->script('admin/theme-manager');
		echo $this->Html->script("langs/$configLanguage");
		echo $this->Html->script('print');
		if($this->Session->check('User')){
			echo $this->Html->script('admin/notifications.js?v=20260913-notif-v1');
		}
		
		echo $this->fetch('script');
                echo $this->Js->writeBuffer();
		$UserArr=$this->Session->read('User');
?>
<?php if($translate>0){?>
<div id="google_translate_element"></div><script type="text/javascript">
function googleTranslateElementInit() {
  new google.translate.TranslateElement({pageLanguage: 'en', layout: google.translate.TranslateElement.InlineLayout.SIMPLE}, 'google_translate_element');
}
</script><script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit" async defer></script>
<?php }?>
</head>
    <?php if($this->Session->check('User')){?>
     
     
     <body class="wv-admin-theme <?php echo h(!empty($adminThemeClass) ? $adminThemeClass : 'theme-light');?>" data-admin-theme="<?php echo h(!empty($adminThemeClass) ? $adminThemeClass : 'theme-light');?>" data-admin-theme-cookie="<?php echo h(!empty($adminThemeCookieName) ? $adminThemeCookieName : 'wv_admin_theme_guest');?>">
    <?php echo $this->element('Admin.navbar', array('UserArr' => $UserArr));?>
    <?php
        $adminImpMeta = (array)$this->Session->read('Impersonation.admin');
        if(!empty($adminImpMeta['original_user']['User']['id']))
        {
            $targetLabel = !empty($adminImpMeta['target_user_name']) ? $adminImpMeta['target_user_name'] : __('User');
            $targetRole = !empty($adminImpMeta['target_user_role']) ? $adminImpMeta['target_user_role'] : __('User');
            $isImpAgencyAdmin = (!empty($adminImpMeta['impersonator_type']) && $adminImpMeta['impersonator_type'] === 'AGY') ||
                                (!empty($adminImpMeta['impersonator_role']) && stripos($adminImpMeta['impersonator_role'], 'Agency') !== false);
            $impersonatorName = !empty($adminImpMeta['impersonator_name']) ? $adminImpMeta['impersonator_name'] : '';
            $exitButtonLabel = $isImpAgencyAdmin
                ? ('<i class="fa fa-sign-out"></i>&nbsp;<strong>'.__('Return to Agency Admin').'</strong>')
                : ('<i class="fa fa-sign-out"></i>&nbsp;<strong>'.__('Return to Super Admin').'</strong>');
            $exitConfirmMsg = $isImpAgencyAdmin
                ? __('Stop impersonation and return to your Agency Admin account?')
                : __('Stop impersonation and return to Super Admin?');
    ?>
    <div class="alert alert-warning wv-impersonation-alert" style="margin:12px 24px 0 24px;position:sticky;top:58px;z-index:1030;display:flex;align-items:center;justify-content:space-between;box-shadow:0 2px 8px rgba(0,0,0,0.06);border:1px solid #fde68a;border-left:4px solid #f59e0b;background-color:#fffbeb;color:#92400e;padding:10px 18px;border-radius:8px;">
        <div style="font-size:14px;">
            <i class="fa fa-user-secret fa-lg" style="margin-right:8px;color:#d35400;"></i>
            <strong style="color:#d35400;"><?php echo __('Impersonation Mode Active'); ?>:</strong>
            <?php echo sprintf(__('You are logged in as %s (%s)'), '<strong style="color:#1a5276;">'.h($targetLabel).'</strong>', '<span class="badge" style="background:#e67e22;color:#fff;font-size:11px;padding:3px 8px;border-radius:10px;">'.h($targetRole).'</span>'); ?>
            <?php if(!empty($impersonatorName)): ?>
            <span style="font-size:12px;color:#78350f;margin-left:8px;"><i class="fa fa-shield"></i>&nbsp;(<?php echo sprintf(__('Admin: %s'), h($impersonatorName)); ?>)</span>
            <?php endif; ?>
        </div>
        <div>
            <?php echo $this->Html->link($exitButtonLabel, array('controller'=>'Users','action'=>'stopimpersonation'), array('escape'=>false,'class'=>'btn btn-danger btn-sm','style'=>'font-weight:bold;box-shadow:0 1px 3px rgba(0,0,0,0.2);'), $exitConfirmMsg); ?>
        </div>
    </div>
    <?php } ?>

    <div class="page-container">
        <div class="page-content">
            <?php echo $this->element('Admin.sidebar');?>
<div class="content-wrapper">
                <div class="content wv-admin-dashboard-shell">
            <?php
                $showRightsBadge = (!empty($this->request->query['debug_rights']) && Configure::read('debug') > 0);
                $rightsView = !empty($allowViewAction);
                $rightsAdd = !empty($allowAddAction);
                $rightsEdit = !empty($allowEditAction);
                $rightsDelete = !empty($allowDeleteAction);
                if(!empty($isSuperAdminSession) || !empty($isAgencyAdminSession))
                {
                    $rightsView = true;
                    $rightsAdd = true;
                    $rightsEdit = true;
                    $rightsDelete = true;
                }
            ?>
            <?php if($showRightsBadge){ ?>
            <div class="panel panel-default" style="margin-bottom:10px;">
                <div class="panel-body" style="padding:8px 12px;">
                    <div style="font-weight:600;display:inline-block;margin-right:8px;"><?php echo __('Current Rights');?>:</div>
                    <span class="label label-<?php echo $rightsView ? 'success' : 'default'; ?>" style="margin-right:4px;"><?php echo __('View');?>: <?php echo $rightsView ? __('On') : __('Off'); ?></span>
                    <span class="label label-<?php echo $rightsAdd ? 'success' : 'default'; ?>" style="margin-right:4px;"><?php echo __('Add');?>: <?php echo $rightsAdd ? __('On') : __('Off'); ?></span>
                    <span class="label label-<?php echo $rightsEdit ? 'success' : 'default'; ?>" style="margin-right:4px;"><?php echo __('Edit');?>: <?php echo $rightsEdit ? __('On') : __('Off'); ?></span>
                    <span class="label label-<?php echo $rightsDelete ? 'success' : 'default'; ?>"><?php echo __('Delete');?>: <?php echo $rightsDelete ? __('On') : __('Off'); ?></span>
                </div>
            </div>
            <?php } ?>
             
            <!-- inject:/SIDEBAR -->
	    <?php echo $this->fetch('content');?></div></div>
        </div>
    </div>



     
     
     
     
     
  <!-- END FOOTER -->
        <?php } else{
	echo $this->Html->css('/design800/css/layouts/page-center');
	?>
	<body class="wv-admin-theme wv-admin-auth">
	  <div class="page-container"><br/><br/><br/>
	    <center><?php if(strlen($frontLogo)>0){?><?php echo$this->Html->image($frontLogo,array('alt'=>$siteName,'class'=>'responsive-img '));}?></center>
           
          
	   <?php  echo $this->fetch('content');?>
	   
    
	  <?php  }?>
<?php if($this->Session->check('User')){
    $isSuper = !empty($isSuperAdminSession);
    $isAgency = !empty($isAgencyAdminSession);
    $canAddAction = ($isSuper || $isAgency || !empty($allowAddAction)) ? 1 : 0;
    $canEditAction = ($isSuper || $isAgency || !empty($allowEditAction)) ? 1 : 0;
    $canDeleteAction = ($isSuper || $isAgency || !empty($allowDeleteAction)) ? 1 : 0;
?>
<script type="text/javascript">
jQuery(function($){
    var $sidebar = $('#sidebar-main');
    var collapsedKey = 'wv_sidebar_collapsed';
    function applySidebarState(isCollapsed){
        if(!$sidebar.length){ return; }
        if(isCollapsed){
            $sidebar.addClass('sidebar-collapsed');
        } else {
            $sidebar.removeClass('sidebar-collapsed');
        }
    }
    function readCollapsedState(){
        try{
            return window.localStorage.getItem(collapsedKey) === '1';
        }catch(ex){
            return false;
        }
    }
    function saveCollapsedState(isCollapsed){
        try{
            window.localStorage.setItem(collapsedKey, isCollapsed ? '1' : '0');
        }catch(ex){}
    }
    applySidebarState(readCollapsedState());
    function isAdminMobileView(){
        return window.matchMedia && window.matchMedia('(max-width: 991px)').matches;
    }
    function ensureAdminSidebarBackdrop(){
        var $b = $('.admin-sidebar-backdrop');
        if(!$b.length){
            $b = $('<div class="admin-sidebar-backdrop"></div>').appendTo('body');
        }
        return $b;
    }
    function openAdminSidebar(){
        if(window.toggleAdminMobileSidebar){
            var sidebar = document.getElementById('sidebar-main');
            if(!sidebar || !sidebar.classList.contains('is-mobile-open')){
                window.toggleAdminMobileSidebar();
            }
            return;
        }
        if(!$sidebar.length){ $sidebar = $('#sidebar-main'); }
        if(!$sidebar.length){ return; }
        ensureAdminSidebarBackdrop();
        $sidebar.removeClass('sidebar-collapsed').addClass('is-mobile-open').attr('aria-hidden','false');
        $('body').addClass('admin-mobile-sidebar-open');
    }
    function closeAdminSidebar(){
        if(window.closeAdminMobileSidebar){
            window.closeAdminMobileSidebar();
            return;
        }
        if(!$sidebar.length){ $sidebar = $('#sidebar-main'); }
        if(!$sidebar.length){ return; }
        $sidebar.removeClass('is-mobile-open').attr('aria-hidden','true');
        $('body').removeClass('admin-mobile-sidebar-open');
    }
    ensureAdminSidebarBackdrop();

    function handleAdminSidebarToggle(e){
        if(e){
            e.preventDefault();
            e.stopImmediatePropagation();
            e.stopPropagation();
        }
        if(!$sidebar.length){ $sidebar = $('#sidebar-main'); }
        if(!$sidebar.length){ return false; }
        if(isAdminMobileView()){
            if(window.toggleAdminMobileSidebar){
                return window.toggleAdminMobileSidebar(e);
            }
            if($('body').hasClass('admin-mobile-sidebar-open') || $sidebar.hasClass('is-mobile-open')){
                closeAdminSidebar();
            }else{
                openAdminSidebar();
            }
            return false;
        }
        var willCollapse = !$sidebar.hasClass('sidebar-collapsed');
        applySidebarState(willCollapse);
        saveCollapsedState(willCollapse);
        return false;
    }

    // Unbind and rebind toggle controls for desktop toggling (mobile #toggle-sidebar uses direct inline onclick)
    $('#toggle-sidebar-desktop, #sidebar-inline-toggle').off('click').on('click', handleAdminSidebarToggle);
    $(document).off('click.adminMobileSidebarToggle', '#toggle-sidebar-desktop, #sidebar-inline-toggle')
        .on('click.adminMobileSidebarToggle', '#toggle-sidebar-desktop, #sidebar-inline-toggle', handleAdminSidebarToggle);

    // Mobile close button inside drawer
    $(document).off('click.adminMobileClose', '#sidebar-mobile-close')
        .on('click.adminMobileClose', '#sidebar-mobile-close', function(e){
            if(e){ e.preventDefault(); e.stopPropagation(); }
            closeAdminSidebar();
        });

    // Backdrop click
    $(document).off('click.adminMobileBackdrop', '.admin-sidebar-backdrop')
        .on('click.adminMobileBackdrop', '.admin-sidebar-backdrop', function(e){
            if(e){ e.preventDefault(); e.stopPropagation(); }
            closeAdminSidebar();
        });

    // Close on mobile when navigating leaf links
    $('#sidebar-main .navigation a').off('click.adminMobileSidebar').on('click.adminMobileSidebar', function(){
        if(isAdminMobileView() && !$(this).hasClass('has-ul')){
            closeAdminSidebar();
        }
    });

    // Escape key closes mobile drawer
    $(document).off('keyup.adminMobileSidebar').on('keyup.adminMobileSidebar', function(e){
        if(e.keyCode === 27){
            closeAdminSidebar();
        }
    });

    // Mobile swipe-to-close gesture support
    var touchStartX = 0;
    var touchStartY = 0;
    $(document).off('touchstart.adminMobileSwipe', '#sidebar-main')
        .on('touchstart.adminMobileSwipe', '#sidebar-main', function(e){
            var touch = e.originalEvent.touches ? e.originalEvent.touches[0] : null;
            if(touch){
                touchStartX = touch.clientX;
                touchStartY = touch.clientY;
            }
        });
    $(document).off('touchend.adminMobileSwipe', '#sidebar-main')
        .on('touchend.adminMobileSwipe', '#sidebar-main', function(e){
            if(!isAdminMobileView() || !$('body').hasClass('admin-mobile-sidebar-open')){ return; }
            var touch = e.originalEvent.changedTouches ? e.originalEvent.changedTouches[0] : null;
            if(touch){
                var deltaX = touch.clientX - touchStartX;
                var deltaY = touch.clientY - touchStartY;
                if(deltaX < -50 && Math.abs(deltaY) < 70){
                    closeAdminSidebar();
                }
            }
        });

    // Screen resize / orientation change handling
    $(window).off('resize.adminMobileSidebar orientationchange.adminMobileSidebar').on('resize.adminMobileSidebar orientationchange.adminMobileSidebar', function(){
        if(!isAdminMobileView()){
            closeAdminSidebar();
            applySidebarState(readCollapsedState());
        }else{
            $sidebar.removeClass('sidebar-collapsed');
        }
    });
    if(isAdminMobileView()){
        $sidebar.removeClass('sidebar-collapsed');
    }

    var canAddAction = <?php echo (int)$canAddAction;?>;
    var canEditAction = <?php echo (int)$canEditAction;?>;
    var canDeleteAction = <?php echo (int)$canDeleteAction;?>;
    function actionFromUrl(url){
        if(!url){return '';}
        var clean = String(url).split('?')[0].split('#')[0];
        if(/^javascript:/i.test(clean)){return '';}
        clean = clean.replace(/^https?:\/\/[^\/]+/i, '');
        var parts = clean.split('/');
        var routeParts = [];
        for(var i=0;i<parts.length;i++){
            var seg = $.trim(parts[i] || '');
            if(!seg){continue;}
            if(seg.toLowerCase() === 'admin'){continue;}
            routeParts.push(seg.toLowerCase());
        }
        if(!routeParts.length){return '';}
        return routeParts[routeParts.length - 1];
    }
    function isActionIn(actionName, list){
        if(!actionName){return false;}
        for(var i=0;i<list.length;i++){
            if(actionName === list[i]){return true;}
        }
        return false;
    }
    var addActions = ['add','create','unitadd','manualallotment','import','upload'];
    var editActions = ['edit','update','assignrightsedit'];
    var deleteActions = ['delete','deleteall','remove','destroy','trashdelete','permanentdelete'];

    if(canAddAction && canEditAction && canDeleteAction){
        // Full action rights active: do not hide any action buttons or links
    } else {
        $('a[href]').each(function(){
            var $el = $(this);
            var actionName = actionFromUrl($el.attr('href'));
            if(!canAddAction && isActionIn(actionName, addActions)){
                $el.hide();
                return;
            }
            if(!canEditAction && isActionIn(actionName, editActions)){
                $el.hide();
                return;
            }
            if(!canDeleteAction && isActionIn(actionName, deleteActions)){
                $el.hide();
            }
        });

        $('form[action]').each(function(){
            var $form = $(this);
            var actionName = actionFromUrl($form.attr('action'));
            if((!canAddAction && isActionIn(actionName, addActions)) ||
               (!canEditAction && isActionIn(actionName, editActions)) ||
               (!canDeleteAction && isActionIn(actionName, deleteActions))){
                $form.find('button[type=submit],input[type=submit],a.btn').hide();
            }
        });
    }
});
</script>
<?php } ?>
<div class="modal fade" id="targetModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
<div class="modal-content">        
</div>
</div>

<!-- Facebook Leads Throttled Background Auto-Sync (Cross-Tab Coordinated) -->
<script type="text/javascript">
(function() {
    var SYNC_COOLDOWN_MS = 300000; // 5 minutes minimum cooldown across all tabs

    function triggerFacebookAutoSync() {
        if (typeof jQuery === 'undefined') return;

        var now = Date.now();
        var lastSync = 0;
        try {
            lastSync = parseInt(localStorage.getItem('wv_fb_sync_last_ts') || '0', 10);
        } catch(e) {}

        // Prevent multiple tabs from storming the server
        if (now - lastSync < SYNC_COOLDOWN_MS) {
            return;
        }

        try {
            localStorage.setItem('wv_fb_sync_last_ts', now.toString());
        } catch(e) {}

        jQuery.ajax({
            url: '<?php echo $this->Html->url(array("plugin" => "admin", "controller" => "facebook_integrations", "action" => "cron_autofetch")); ?>',
            type: 'GET',
            dataType: 'json',
            cache: false,
            success: function(resp) {
                if (resp && resp.count > 0) {
                    console.log('[FB CRM Auto-Sync] Ingested ' + resp.count + ' new lead(s)!');
                }
            }
        });
    }

    // Check once after 15s page load, then check every 60s (only fires if 5m cooldown elapsed)
    setTimeout(triggerFacebookAutoSync, 15000);
    setInterval(triggerFacebookAutoSync, 60000);
})();
</script>

<!-- Table Action Dropdown Body-Portal Delegated Fixed Positioning & Direction Handler (Zoom-Proof & Overflow-Proof) -->
<script type="text/javascript">
(function($) {
    if (typeof $ === 'undefined') return;

    var actionDropdownSelector = '.table .btn-group, .table .dropdown, .table-responsive .btn-group, .table-responsive .dropdown, .deal-action-cell .btn-group, .deal-control-cell .btn-group, .wv-contacts-action .btn-group, .wv-rental-actions .btn-group, .actions-col .btn-group';

    // Helper: Restore any portaled menu back to its original parent .btn-group
    function restorePortaledMenu($btnGroup) {
        var $placeholders = $btnGroup ? $btnGroup.find('.wv-portal-placeholder') : $('.wv-portal-placeholder');
        $placeholders.each(function() {
            var $ph = $(this);
            var $menu = $ph.data('wv-menu');
            var $bg = $ph.data('wv-btn-group');
            if ($menu && $menu.length) {
                $menu.css({
                    position: '',
                    top: '',
                    left: '',
                    right: '',
                    bottom: '',
                    display: '',
                    visibility: '',
                    'z-index': '',
                    'max-height': '',
                    'overflow-y': '',
                    margin: ''
                }).removeClass('wv-portal-active-menu dropup-menu-up');

                $ph.before($menu);
                $ph.remove();
            }
            if ($bg && $bg.length) {
                $bg.removeClass('dropup');
            }
        });

        // Safety fallback: any active portal menu on body without matching placeholder
        $('body > .dropdown-menu.wv-portal-active-menu').each(function() {
            var $m = $(this);
            var $ph = $m.data('wv-placeholder');
            if ($ph && $ph.length && $ph.parent().length) {
                $ph.before($m);
                $ph.remove();
            }
            $m.css({
                position: '',
                top: '',
                left: '',
                right: '',
                bottom: '',
                display: '',
                visibility: '',
                'z-index': '',
                'max-height': '',
                'overflow-y': '',
                margin: ''
            }).removeClass('wv-portal-active-menu dropup-menu-up');
        });
    }

    // Helper: Detach menu, append to body, and position dynamically relative to viewport
    function portalAndPositionMenu($btnGroup) {
        var $toggle = $btnGroup.children('[data-toggle="dropdown"]');
        var $menu = $btnGroup.children('.dropdown-menu');

        // Check if menu is already in body and we have a placeholder
        if (!$menu.length) {
            var $existingPh = $btnGroup.find('.wv-portal-placeholder');
            if ($existingPh.length) {
                $menu = $existingPh.data('wv-menu');
            }
        }
        if (!$toggle.length || !$menu.length) return;

        var btn = $toggle[0];
        var btnRect = btn.getBoundingClientRect();

        // 1. Create a placeholder right after menu in original parent if not already created
        var $placeholder = $btnGroup.find('.wv-portal-placeholder');
        if (!$placeholder.length) {
            $placeholder = $('<span class="wv-portal-placeholder" style="display:none !important;"></span>');
            $placeholder.data('wv-menu', $menu);
            $placeholder.data('wv-btn-group', $btnGroup);
            $menu.after($placeholder);
            $menu.data('wv-placeholder', $placeholder);
        }

        // 2. Detach and append menu to document.body
        if ($menu.parent()[0] !== document.body) {
            $('body').append($menu);
        }
        $menu.addClass('wv-portal-active-menu');

        // 3. Set initial fixed coordinates to measure menu accurately
        $menu.css({
            position: 'fixed',
            display: 'block',
            visibility: 'hidden',
            top: 0,
            left: 0,
            right: 'auto',
            bottom: 'auto',
            'z-index': 99999,
            margin: 0
        });

        var menuWidth = $menu.outerWidth() || 220;
        var menuHeight = $menu.outerHeight() || 380;

        var clientHeight = document.documentElement.clientHeight || window.innerHeight;
        var clientWidth = document.documentElement.clientWidth || window.innerWidth;

        var spaceBelow = clientHeight - btnRect.bottom;
        var spaceAbove = btnRect.top;

        var topPos;
        var isDropup = false;

        // Auto-Direction:
        // If spaceBelow < menuHeight + 10 AND spaceAbove > spaceBelow -> Position UPWARD directly above button
        if (spaceBelow < (menuHeight + 10) && spaceAbove > spaceBelow) {
            isDropup = true;
            topPos = btnRect.top - menuHeight - 4;
            if (topPos < 8) {
                topPos = 8;
                $menu.css({
                    'max-height': Math.max(160, (btnRect.top - 14)) + 'px',
                    'overflow-y': 'auto'
                });
            } else {
                $menu.css({ 'max-height': '', 'overflow-y': '' });
            }
        } else {
            // Position DOWNWARD directly below button
            isDropup = false;
            topPos = btnRect.bottom + 4;
            if (topPos + menuHeight > clientHeight - 8) {
                $menu.css({
                    'max-height': Math.max(160, (clientHeight - topPos - 10)) + 'px',
                    'overflow-y': 'auto'
                });
            } else {
                $menu.css({ 'max-height': '', 'overflow-y': '' });
            }
        }

        // Smart horizontal alignment:
        // For far-right action columns, align menu's right edge with button's right edge.
        // For middle columns (e.g. Deal Control), center the menu directly over the button so it opens in its column area!
        var isFarRight = ($btnGroup.closest('.actions-col, .deal-action-cell, .wv-contacts-action, .wv-rental-actions').length > 0) || (btnRect.right > clientWidth - 250);
        var leftPos;
        if (isFarRight) {
            leftPos = Math.max(8, btnRect.right - menuWidth);
        } else {
            leftPos = Math.round(btnRect.left + (btnRect.width / 2) - (menuWidth / 2));
        }
        if (leftPos < 8) {
            leftPos = 8;
        }
        if (leftPos + menuWidth > clientWidth - 8) {
            leftPos = Math.max(8, clientWidth - menuWidth - 8);
        }

        if (isDropup) {
            $btnGroup.addClass('dropup');
            $menu.addClass('dropup-menu-up');
        } else {
            $btnGroup.removeClass('dropup');
            $menu.removeClass('dropup-menu-up');
        }

        $menu.css({
            position: 'fixed',
            top: Math.round(topPos) + 'px',
            left: Math.round(leftPos) + 'px',
            right: 'auto',
            bottom: 'auto',
            display: 'block',
            visibility: 'visible',
            'z-index': 99999
        });
    }

    // Delegated click handler on all table dropdown toggles
    $(document).on('click', '.table [data-toggle="dropdown"], .table-responsive [data-toggle="dropdown"], .deal-action-cell [data-toggle="dropdown"], .deal-control-cell [data-toggle="dropdown"], .wv-contacts-action [data-toggle="dropdown"], .wv-rental-actions [data-toggle="dropdown"], .actions-col [data-toggle="dropdown"]', function(e) {
        var $toggle = $(this);
        var $btnGroup = $toggle.closest('.btn-group, .dropdown');
        if (!$btnGroup.length) return;

        setTimeout(function() {
            if ($btnGroup.hasClass('open')) {
                portalAndPositionMenu($btnGroup);
            } else {
                restorePortaledMenu($btnGroup);
            }
        }, 10);
    });

    $(document).on('show.bs.dropdown', actionDropdownSelector, function(e) {
        var $btnGroup = $(this);
        portalAndPositionMenu($btnGroup);
    });

    $(document).on('shown.bs.dropdown', actionDropdownSelector, function(e) {
        var $btnGroup = $(this);
        portalAndPositionMenu($btnGroup);
    });

    $(document).on('hidden.bs.dropdown', actionDropdownSelector, function(e) {
        var $btnGroup = $(this);
        restorePortaledMenu($btnGroup);
    });

    // Handle clicks inside portaled menu links/items: allow action, then restore
    $(document).on('click', '.wv-portal-active-menu a, .wv-portal-active-menu button, .wv-portal-active-menu input[type="submit"]', function(e) {
        var $item = $(this);
        var $menu = $item.closest('.wv-portal-active-menu');
        var $ph = $menu.data('wv-placeholder');
        var $bg = $ph ? $ph.data('wv-btn-group') : null;
        if ($bg && $bg.length) {
            $bg.removeClass('open');
        }
        setTimeout(function() {
            restorePortaledMenu($bg);
        }, 50);
    });

    // Dismiss portaled menu when clicking outside
    $(document).on('click.wvPortalDismiss', function(e) {
        var $target = $(e.target);
        if ($target.closest('.wv-portal-active-menu').length || $target.closest('[data-toggle="dropdown"]').length) {
            return;
        }
        var $openGroups = $(actionDropdownSelector).filter('.open');
        if ($openGroups.length) {
            $openGroups.removeClass('open');
        }
        restorePortaledMenu();
    });

    // Window scroll, resize (zoom level changes): close/restore portaled menus cleanly
    $(window).on('scroll.wvTableDropdown resize.wvTableDropdown', function(e) {
        var $openGroups = $(actionDropdownSelector).filter('.open');
        if ($openGroups.length) {
            $openGroups.each(function() {
                var $bg = $(this);
                $bg.removeClass('open');
                restorePortaledMenu($bg);
            });
        } else {
            restorePortaledMenu();
        }
    });

    $(document).on('scroll', '.table-responsive, .deals-table-wrap, .wv-contacts-table-responsive', function(e) {
        var $openGroups = $(actionDropdownSelector).filter('.open');
        if ($openGroups.length) {
            $openGroups.removeClass('open');
            restorePortaledMenu();
        }
    });
})(jQuery);
</script>
</body>
</html>
