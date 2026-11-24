<?php
$ts = !empty($themeSetting) ? $themeSetting : array();
$topHeaderOn = !empty($ts['top_header_enabled']);

App::uses('RealEstateThemeSetting', 'RealEstateTheme.Model');
$themeModel = new RealEstateThemeSetting();
$sectionsOrder = $themeModel->parseSectionsOrder(!empty($ts['sections_order']) ? $ts['sections_order'] : '');

$sectionMeta = array(
    'hero' => array('name' => __('Hero Video Background'), 'icon' => 'fa-video-camera', 'cardId' => 'sec-video', 'enabledKey' => 'hero_section_enabled'),
    'ticker' => array('name' => __('Flash News & Announcement Ticker'), 'icon' => 'fa-bullhorn', 'cardId' => 'sec-ticker', 'enabledKey' => 'ticker_enabled'),
    'intro' => array('name' => __('Intro Headings & Split Features'), 'icon' => 'fa-th-large', 'cardId' => 'sec-intro', 'enabledKey' => 'intro_section_enabled'),
    'properties' => array('name' => __('Properties For Sale Showcase'), 'icon' => 'fa-building', 'cardId' => 'sec-properties', 'enabledKey' => 'properties_section_enabled'),
    'reasons' => array('name' => __('Reasons to Own Accent Banner'), 'icon' => 'fa-star', 'cardId' => 'sec-reasons', 'enabledKey' => 'reasons_section_enabled'),
    'area' => array('name' => __('Surrounding Area Highlights & Map'), 'icon' => 'fa-map-marker', 'cardId' => 'sec-area', 'enabledKey' => 'area_section_enabled'),
    'other' => array('name' => __('Expand Horizons & Legal Notice'), 'icon' => 'fa-globe', 'cardId' => 'sec-other', 'enabledKey' => 'other_section_enabled'),
    'amenities' => array('name' => __('World-Class Amenities & Facilities'), 'icon' => 'fa-th', 'cardId' => 'sec-amenities', 'enabledKey' => 'amenities_enabled'),
    'contact' => array('name' => __('Get In Touch Consultation Form'), 'icon' => 'fa-envelope', 'cardId' => 'sec-contact', 'enabledKey' => 'contact_section_enabled'),
    'newsletter' => array('name' => __('Newsletter Bar & Footer Accordion'), 'icon' => 'fa-newspaper-o', 'cardId' => 'sec-newsletter', 'enabledKey' => 'newsletter_section_enabled')
);
?>

<style type="text/css">
.tc-shell {
    display: grid;
    grid-template-columns: 280px minmax(0, 1fr);
    gap: 20px;
    align-items: start;
    margin-top: 10px;
}
@media (max-width: 991px) {
    .tc-shell {
        grid-template-columns: 1fr;
    }
}
.tc-sticky-nav {
    position: sticky;
    top: 80px;
    background: #fff;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
    overflow: hidden;
}
.tc-nav-header {
    background: linear-gradient(135deg, #07152a, #102746);
    color: #fff;
    padding: 14px 18px;
    font-size: 14px;
    font-weight: 800;
    letter-spacing: 0.3px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.tc-nav-list {
    list-style: none;
    margin: 0;
    padding: 8px;
}
.tc-nav-list li a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    font-size: 13px;
    font-weight: 600;
    color: #334155;
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.15s ease-in-out;
}
.tc-nav-list li a:hover {
    background: #f1f5f9;
    color: #ff0a46;
}
.tc-nav-list li a.active {
    background: #fff1f2;
    color: #e11d48;
    font-weight: 700;
}
.tc-nav-actions {
    padding: 14px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.tc-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    margin-bottom: 20px;
    overflow: hidden;
}
.tc-card-header {
    background: #fafafa;
    border-bottom: 1px solid #e2e8f0;
    padding: 12px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.tc-card-title {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 8px;
}
.tc-card-body {
    padding: 18px 20px;
}
.tc-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}
.tc-grid-3 {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 14px;
}
@media (max-width: 768px) {
    .tc-grid-2, .tc-grid-3 {
        grid-template-columns: 1fr;
    }
}
.tc-form-group {
    margin-bottom: 12px;
}
.tc-form-group:last-child {
    margin-bottom: 0;
}
.tc-label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 5px;
}
.tc-input,
.tc-textarea,
.tc-shell input,
.tc-shell textarea,
.tc-shell select,
.form-control,
input.form-control,
textarea.form-control {
    font-weight: 400 !important;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
    letter-spacing: normal !important;
    text-transform: none !important;
    color: #1e293b !important;
    font-size: 13.5px !important;
    line-height: 1.5 !important;
}
.tc-input {
    width: 100%;
    height: 38px;
    padding: 6px 12px;
    background: #fff;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    transition: border-color 0.15s ease-in-out;
}
.tc-input:focus {
    border-color: #ff0a46;
    outline: none;
    box-shadow: 0 0 0 3px rgba(255, 10, 70, 0.12);
}
.tc-textarea {
    width: 100%;
    padding: 8px 12px;
    background: #fff;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    resize: vertical;
}
.tc-textarea:focus {
    border-color: #ff0a46;
    outline: none;
    box-shadow: 0 0 0 3px rgba(255, 10, 70, 0.12);
}
.tc-shell input::placeholder,
.tc-shell textarea::placeholder {
    font-weight: 400 !important;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
    color: #94a3b8 !important;
    text-transform: none !important;
    letter-spacing: normal !important;
}
.tc-sub-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 14px;
    margin-bottom: 14px;
}
.tc-sub-box:last-child {
    margin-bottom: 0;
}
.tc-sub-heading {
    font-size: 13px;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 12px 0;
    display: flex;
    align-items: center;
    gap: 6px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.tc-photo-card {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #fff;
    border: 1px dashed #cbd5e1;
    padding: 10px;
    border-radius: 6px;
}
.tc-photo-thumb {
    width: 90px;
    height: 65px;
    border-radius: 4px;
    object-fit: cover;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    flex-shrink: 0;
}
.tc-photo-inputs {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.tc-toggle-wrap {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 10px 14px;
    border-radius: 6px;
}
.tc-switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    margin: 0;
}
.tc-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.tc-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #cbd5e1;
    transition: .2s;
    border-radius: 24px;
}
.tc-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .2s;
    border-radius: 50%;
}
.tc-switch input:checked + .tc-slider {
    background-color: #10b981;
}
.tc-switch input:checked + .tc-slider:before {
    transform: translateX(20px);
}
.tc-btn-save {
    background: #ff0a46;
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: background 0.15s ease-in-out;
}
.tc-btn-save:hover {
    background: #d80036;
    color: #fff;
}
</style>

<ul class="breadcrumb">
    <li><a href="<?php echo $this->Html->url('/admin/Dashboards'); ?>"><?php echo __('Dashboard'); ?></a></li>
    <li><a href="<?php echo $this->Html->url('/admin/real_estate_themes'); ?>"><?php echo __('Theme Manager'); ?></a></li>
    <li class="active"><?php echo __('Options & Customizer'); ?></li>
</ul>

<form method="post" action="<?php echo $this->Html->url(array('plugin' => 'RealEstateTheme', 'controller' => 'RealEstateThemes', 'action' => 'settings', 'admin' => true)); ?>" enctype="multipart/form-data">
    <div class="tc-shell">
        
        <!-- Left Sticky Navigation & Quick Actions -->
        <div class="tc-sticky-nav">
            <div class="tc-nav-header">
                <i class="fa fa-sliders"></i> <?php echo __('Theme Customizer'); ?>
            </div>
            
                        <ul class="tc-nav-list">
                <li><a href="#sec-order-manager" class="tc-nav-link" style="background: #eff6ff; color: #2563eb; font-weight: 700;"><i class="fa fa-arrows-v text-primary"></i> <?php echo __('Homepage Layout & Order'); ?></a></li>
                <li><a href="#sec-brand" class="tc-nav-link"><i class="fa fa-header text-primary"></i> <?php echo __('1. Header & Brand'); ?></a></li>
                <li><a href="#sec-video" class="tc-nav-link"><i class="fa fa-video-camera text-primary"></i> <?php echo __('2. Hero Video'); ?></a></li>
                <li><a href="#sec-ticker" class="tc-nav-link"><i class="fa fa-bullhorn text-danger"></i> <?php echo __('3. Flash Updates & Ticker'); ?></a></li>
                <li><a href="#sec-intro" class="tc-nav-link"><i class="fa fa-th-large text-primary"></i> <?php echo __('4. Intro & Split Features'); ?></a></li>
                <li><a href="#sec-properties" class="tc-nav-link"><i class="fa fa-building text-danger"></i> <?php echo __('5. Properties Showcase'); ?></a></li>
                <li><a href="#sec-reasons" class="tc-nav-link"><i class="fa fa-star text-warning"></i> <?php echo __('6. Reasons to Own'); ?></a></li>
                <li><a href="#sec-area" class="tc-nav-link"><i class="fa fa-map-marker text-success"></i> <?php echo __('7. Surrounding Area'); ?></a></li>
                <li><a href="#sec-other" class="tc-nav-link"><i class="fa fa-globe text-info"></i> <?php echo __('8. Expand Horizons'); ?></a></li>
                <li><a href="#sec-amenities" class="tc-nav-link"><i class="fa fa-th text-danger"></i> <?php echo __('9. World-Class Amenities'); ?></a></li>
                <li><a href="#sec-contact" class="tc-nav-link"><i class="fa fa-envelope text-success"></i> <?php echo __('10. Consultation Form'); ?></a></li>
                <li><a href="#sec-newsletter" class="tc-nav-link"><i class="fa fa-newspaper-o text-primary"></i> <?php echo __('11. Newsletter & Accordion'); ?></a></li>
                <li><a href="#sec-footer" class="tc-nav-link"><i class="fa fa-info-circle text-info"></i> <?php echo __('12. Footer & Legal'); ?></a></li>
            </ul>

            <div class="tc-nav-actions">
                <button type="submit" class="tc-btn-save">
                    <i class="fa fa-save"></i> <?php echo __('Save All Changes'); ?>
                </button>
                <a href="<?php echo $this->Html->url('/'); ?>" target="_blank" class="btn btn-default btn-block" style="font-weight: 600;">
                    <i class="fa fa-external-link"></i> <?php echo __('Preview Live Site'); ?>
                </a>
            </div>
        </div>

        <!-- Right Content Panels (All Sections) -->
        <div class="tc-content">
            
            
            <!-- HOMEPAGE SECTIONS LAYOUT & REORDERING MANAGER -->
            <div class="tc-card" id="sec-order-manager" style="border: 2px solid #3b82f6; box-shadow: 0 8px 24px rgba(59, 130, 246, 0.1);">
                <div class="tc-card-header" style="background: linear-gradient(135deg, #07152a, #1e3a8a); color: #ffffff; display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <h3 class="tc-card-title" style="color: #ffffff; margin: 0;"><i class="fa fa-arrows-v text-info"></i> <?php echo __('Homepage Sections Layout & Reordering Manager'); ?></h3>
                        <span class="badge badge-primary" style="background: #3b82f6;"><?php echo __('Moveable Sections'); ?></span>
                    </div>
                    <span style="font-size: 11.5px; color: #bfdbfe; font-weight: 600;">
                        <i class="fa fa-hand-pointer-o"></i> <?php echo __('Click ▲ Up or ▼ Down to reorder placement on Home Page'); ?>
                    </span>
                </div>
                <div class="tc-card-body">
                    <p class="text-muted small" style="margin-bottom: 14px; font-size: 12.5px;">
                        <?php echo __('Customize the exact sequence in which sections appear on your live homepage. Use the <strong>▲ Up</strong> and <strong>▼ Down</strong> buttons to rearrange their order, or use the quick reorder buttons in any section header below.'); ?>
                    </p>

                    <input type="hidden" id="sectionsOrderInput" name="data[RealEstateThemeSetting][sections_order]" value="<?php echo h(implode(',', $sectionsOrder)); ?>">

                    <div id="sectionSortableList" style="display: flex; flex-direction: column; gap: 8px;">
                        <?php 
                        $pos = 0;
                        foreach ($sectionsOrder as $secKey): 
                            if (!isset($sectionMeta[$secKey])) continue;
                            $pos++;
                            $meta = $sectionMeta[$secKey];
                            $isEnabled = !isset($ts[$meta['enabledKey']]) || !empty($ts[$meta['enabledKey']]);
                        ?>
                        <div class="section-order-item" data-sec-key="<?php echo $secKey; ?>" style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between; transition: all 0.2s ease;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <span class="badge sec-pos-badge" style="background: #07152a; color: #7cf9ff; font-weight: 800; font-size: 12px; padding: 5px 9px; border-radius: 5px; min-width: 32px; text-align: center;">#<?php echo $pos; ?></span>
                                <i class="fa <?php echo $meta['icon']; ?> text-danger" style="font-size: 16px; width: 22px; text-align: center;"></i>
                                <strong style="font-size: 13.5px; color: #0f172a;"><?php echo h($meta['name']); ?></strong>
                                <?php if ($isEnabled): ?>
                                    <span class="badge badge-success" style="font-size: 10px; font-weight: 700;"><?php echo __('Active'); ?></span>
                                <?php else: ?>
                                    <span class="badge badge-secondary" style="font-size: 10px; background: #94a3b8; font-weight: 700;"><?php echo __('Hidden'); ?></span>
                                <?php endif; ?>
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <a href="#<?php echo $meta['cardId']; ?>" class="btn btn-xs btn-default" style="font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 4px;" title="Jump to edit this section">
                                    <i class="fa fa-pencil"></i> <?php echo __('Edit'); ?>
                                </a>
                                <button type="button" class="btn btn-xs btn-primary btn-move-sec-up" data-sec="<?php echo $secKey; ?>" style="padding: 4px 10px; font-weight: 700; font-size: 11px; border-radius: 4px;" title="Move Up">
                                    <i class="fa fa-arrow-up"></i> <?php echo __('Up'); ?>
                                </button>
                                <button type="button" class="btn btn-xs btn-primary btn-move-sec-down" data-sec="<?php echo $secKey; ?>" style="padding: 4px 10px; font-weight: 700; font-size: 11px; border-radius: 4px;" title="Move Down">
                                    <i class="fa fa-arrow-down"></i> <?php echo __('Down'); ?>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
<!-- 1. Header & General Brand Settings -->
            <div class="tc-card" id="sec-brand">
                <div class="tc-card-header">
                    <h3 class="tc-card-title"><i class="fa fa-picture-o text-danger"></i> <?php echo __('1. Brand Identity, Logo & Header'); ?></h3>
                    <span class="badge badge-info"><?php echo __('Global Brand'); ?></span>
                </div>
                <div class="tc-card-body">
                    
                    <!-- Logo Display Mode Selection -->
                    <div class="tc-form-group">
                        <label class="tc-label"><i class="fa fa-eye"></i> <?php echo __('Logo & Brand Presentation Mode'); ?></label>
                        <div class="tc-grid-3">
                            <label class="tc-toggle-wrap" style="cursor: pointer; <?php echo (!empty($ts['logo_display_mode']) && $ts['logo_display_mode'] === 'both' || empty($ts['logo_display_mode'])) ? 'border-color: #ff0a46; background: #fff1f2;' : ''; ?>">
                                <input type="radio" name="data[RealEstateThemeSetting][logo_display_mode]" value="both" <?php echo (empty($ts['logo_display_mode']) || $ts['logo_display_mode'] === 'both') ? 'checked' : ''; ?>>
                                <div>
                                    <strong style="font-size: 13px; color: #0f172a;"><?php echo __('Logo + Title & Subtitle'); ?></strong>
                                    <div class="text-muted" style="font-size: 11px;"><?php echo __('Shows graphic logo with site title & subtitle.'); ?></div>
                                </div>
                            </label>

                            <label class="tc-toggle-wrap" style="cursor: pointer; <?php echo (!empty($ts['logo_display_mode']) && $ts['logo_display_mode'] === 'logo_only') ? 'border-color: #ff0a46; background: #fff1f2;' : ''; ?>">
                                <input type="radio" name="data[RealEstateThemeSetting][logo_display_mode]" value="logo_only" <?php echo (!empty($ts['logo_display_mode']) && $ts['logo_display_mode'] === 'logo_only') ? 'checked' : ''; ?>>
                                <div>
                                    <strong style="font-size: 13px; color: #0f172a;"><?php echo __('Only Logo Image'); ?></strong>
                                    <div class="text-muted" style="font-size: 11px;"><?php echo __('Shows only the uploaded logo graphic.'); ?></div>
                                </div>
                            </label>

                            <label class="tc-toggle-wrap" style="cursor: pointer; <?php echo (!empty($ts['logo_display_mode']) && $ts['logo_display_mode'] === 'text_only') ? 'border-color: #ff0a46; background: #fff1f2;' : ''; ?>">
                                <input type="radio" name="data[RealEstateThemeSetting][logo_display_mode]" value="text_only" <?php echo (!empty($ts['logo_display_mode']) && $ts['logo_display_mode'] === 'text_only') ? 'checked' : ''; ?>>
                                <div>
                                    <strong style="font-size: 13px; color: #0f172a;"><?php echo __('Only Title & Subtitle'); ?></strong>
                                    <div class="text-muted" style="font-size: 11px;"><?php echo __('Shows styled brand typography text only.'); ?></div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Logo Photo Upload Card -->
                    <div class="tc-sub-box">
                        <div class="tc-sub-heading"><i class="fa fa-image text-primary"></i> <?php echo __('Upload Theme Brand Logo'); ?></div>
                        <div class="tc-photo-card">
                            <img src="<?php echo $this->Html->url(!empty($ts['brand_logo_image']) ? $ts['brand_logo_image'] : '/img/theme_customizer/placeholder.jpg'); ?>" class="tc-photo-thumb" style="height: 75px; width: 110px; object-fit: contain; background: #07152a;" onerror="this.src='https://images.unsplash.com/photo-1599305445671-ac291c95aaa9?w=200';">
                            <div class="tc-photo-inputs">
                                <label class="tc-label" style="font-size: 11px; margin-bottom: 2px;"><?php echo __('Logo Image File / URL'); ?></label>
                                <input type="text" name="data[RealEstateThemeSetting][brand_logo_image]" class="tc-input" value="<?php echo h(!empty($ts['brand_logo_image']) ? $ts['brand_logo_image'] : ''); ?>" placeholder="Enter logo image URL or upload from your computer">
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <input type="file" name="brand_logo_image_file" class="tc-input" style="padding: 3px 6px; font-size: 11px; height: 30px;">
                                    <small class="text-muted" style="white-space: nowrap;"><?php echo __('PNG, SVG, JPG (Transparent recommended)'); ?></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tc-grid-2">
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Brand Title / Company Name'); ?></label>
                            <input type="text" name="data[RealEstateThemeSetting][brand_name]" class="tc-input" value="<?php echo h(!empty($ts['brand_name']) ? $ts['brand_name'] : 'White Villas & Lodges'); ?>" placeholder="White Villas & Lodges">
                        </div>
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Brand Subtitle / Tagline'); ?></label>
                            <input type="text" name="data[RealEstateThemeSetting][brand_subtitle]" class="tc-input" value="<?php echo h(!empty($ts['brand_subtitle']) ? $ts['brand_subtitle'] : 'HOTEL & SUITES • RESORT RESIDENCES'); ?>" placeholder="HOTEL & SUITES • RESORT RESIDENCES">
                        </div>
                    </div>

                    <div class="tc-grid-3" style="margin-top: 10px;">
                        <div class="tc-form-group">
                            <div class="tc-toggle-wrap">
                                <label class="tc-switch">
                                    <input type="checkbox" name="data[RealEstateThemeSetting][top_header_enabled]" value="1" <?php echo $topHeaderOn ? 'checked' : ''; ?>>
                                    <span class="tc-slider"></span>
                                </label>
                                <div>
                                    <strong style="color: #0f172a; font-size: 13px;"><?php echo __('Top Notification Bar'); ?></strong>
                                    <div class="text-muted" style="font-size: 11px;"><?php echo __('Display contact info bar above hero navigation.'); ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="tc-form-group">
                            <div class="tc-toggle-wrap">
                                <label class="tc-switch">
                                    <input type="checkbox" name="data[RealEstateThemeSetting][sticky_header_enabled]" value="1" <?php echo (!isset($ts['sticky_header_enabled']) || !empty($ts['sticky_header_enabled'])) ? 'checked' : ''; ?>>
                                    <span class="tc-slider"></span>
                                </label>
                                <div>
                                    <strong style="color: #0f172a; font-size: 13px;"><?php echo __('Desktop Sticky Header'); ?></strong>
                                    <div class="text-muted" style="font-size: 11px;"><?php echo __('Keep navbar fixed & floating on desktop when scrolling.'); ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="tc-form-group">
                            <div class="tc-toggle-wrap">
                                <label class="tc-switch">
                                    <input type="checkbox" name="data[RealEstateThemeSetting][sticky_header_mobile_enabled]" value="1" <?php echo !empty($ts['sticky_header_mobile_enabled']) ? 'checked' : ''; ?>>
                                    <span class="tc-slider"></span>
                                </label>
                                <div>
                                    <strong style="color: #0f172a; font-size: 13px;"><?php echo __('Mobile Sticky Menu'); ?></strong>
                                    <div class="text-muted" style="font-size: 11px;"><?php echo __('Show floating sticky header & menu on mobile phones.'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Header "BOOK NOW" Button Settings -->
                    <div class="tc-sub-box" style="margin-top: 14px; background: #fff5f7; border-color: #fecdd3;">
                        <div class="tc-sub-heading" style="color: #d11242;">
                            <i class="fa fa-mouse-pointer text-danger"></i> <?php echo __('Header "BOOK NOW" Button Settings'); ?>
                        </div>
                        <div class="tc-grid-3">
                            <div class="tc-form-group">
                                <label class="tc-label"><?php echo __('Button Label Text'); ?></label>
                                <input type="text" name="data[RealEstateThemeSetting][header_book_btn_text]" class="tc-input" value="<?php echo h(!empty($ts['header_book_btn_text']) ? $ts['header_book_btn_text'] : 'BOOK NOW'); ?>" placeholder="BOOK NOW">
                            </div>
                            <div class="tc-form-group">
                                <label class="tc-label"><?php echo __('Button Target Link / URL'); ?></label>
                                <input type="text" name="data[RealEstateThemeSetting][header_book_btn_link]" class="tc-input" value="<?php echo h(!empty($ts['header_book_btn_link']) ? $ts['header_book_btn_link'] : '#contact-form'); ?>" placeholder="#contact-form or /paymentplans or custom link">
                            </div>
                            <div class="tc-form-group" style="display: flex; align-items: flex-end;">
                                <label class="tc-toggle-wrap" style="cursor: pointer; width: 100%; height: 38px; padding: 6px 12px; margin-bottom: 0;">
                                    <input type="checkbox" name="data[RealEstateThemeSetting][header_book_btn_new_tab]" value="1" <?php echo !empty($ts['header_book_btn_new_tab']) ? 'checked' : ''; ?>>
                                    <span style="font-size: 12px; font-weight: 700; color: #0f172a; margin-left: 6px;"><?php echo __('Open in New Tab'); ?></span>
                                </label>
                            </div>
                        </div>
                        <div style="font-size: 11px; color: #64748b; margin-top: 6px;">
                            <i class="fa fa-info-circle text-primary"></i> <?php echo __('Set `#contact-form` to scroll down to the consultation form, `/paymentplans` for payment plans, or full URLs like `https://wa.me/923074432729`.'); ?>
                        </div>
                    </div>

                    <div class="tc-grid-2" style="margin-top: 14px;">
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Contact Phone'); ?></label>
                            <input type="text" name="data[RealEstateThemeSetting][site_phone]" class="tc-input" value="<?php echo h(!empty($ts['site_phone']) ? $ts['site_phone'] : '+92 339 1101156'); ?>" placeholder="+92 339 1101156">
                        </div>
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Contact Email'); ?></label>
                            <input type="email" name="data[RealEstateThemeSetting][site_email]" class="tc-input" value="<?php echo h(!empty($ts['site_email']) ? $ts['site_email'] : 'info@whitevillas.com.pk'); ?>" placeholder="info@whitevillas.com.pk">
                        </div>
                    </div>

                    <div class="tc-form-group">
                        <label class="tc-label"><?php echo __('Physical Address'); ?></label>
                        <input type="text" name="data[RealEstateThemeSetting][site_address]" class="tc-input" value="<?php echo h(!empty($ts['site_address']) ? $ts['site_address'] : '49-AA F/F Sector D Commercial Bahria Town Lahore, PAKISTAN'); ?>" placeholder="Full street address">
                    </div>
                </div>
            </div>

            <!-- 2. Hero Video Background -->
            <div class="tc-card" id="sec-video" data-section-key="hero">
                <div class="tc-card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <h3 class="tc-card-title"><i class="fa fa-video-camera text-danger"></i> <?php echo __('2. Hero Video Background'); ?></h3>
                        <span class="badge badge-primary"><?php echo __('Hero'); ?></span>
                    </div>
                    <div class="tc-card-move-actions" style="display: flex; align-items: center; gap: 4px; margin-right: 8px;">
    <button type="button" class="btn btn-xs btn-default btn-move-card-up" style="padding: 2px 7px; font-weight: 700; font-size: 11px; border-radius: 4px;" title="Move Section UP on Homepage">
        <i class="fa fa-arrow-up text-primary"></i>
    </button>
    <button type="button" class="btn btn-xs btn-default btn-move-card-down" style="padding: 2px 7px; font-weight: 700; font-size: 11px; border-radius: 4px;" title="Move Section DOWN on Homepage">
        <i class="fa fa-arrow-down text-primary"></i>
    </button>
</div>
<div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;"><?php echo __('Show Section'); ?></span>
                        <label class="tc-switch" style="margin: 0;" title="<?php echo __('Toggle Hero Section Visibility'); ?>">
                            <input type="checkbox" name="data[RealEstateThemeSetting][hero_section_enabled]" value="1" <?php echo (!isset($ts['hero_section_enabled']) || !empty($ts['hero_section_enabled'])) ? 'checked' : ''; ?>>
                            <span class="tc-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="tc-card-body">
                    <div class="tc-form-group">
                        <label class="tc-label"><?php echo __('Hero Video URL (YouTube, Vimeo, or Direct .mp4/.webm)'); ?></label>
                        <input type="text" name="data[RealEstateThemeSetting][hero_video_url]" class="tc-input" value="<?php echo h(!empty($ts['hero_video_url']) ? $ts['hero_video_url'] : 'https://www.ramadacostadelsol.com/wp-content/uploads/2025/03/Ramada-Costa-del-Sol-1080px.mp4'); ?>" placeholder="YouTube watch/share link, Vimeo URL, or direct .mp4 link">
                        <small class="text-muted" style="margin-top: 4px; display: block;"><?php echo __('Supports YouTube links (e.g. youtube.com/watch?v=... or youtu.be/...), Vimeo URLs, and direct .mp4 files. Player controls, branding, and playback UI will automatically be hidden for a clean, full-width background video experience.'); ?></small>
                    </div>

                    <div class="tc-form-group" style="margin-top: 10px;">
                        <div class="tc-toggle-wrap">
                            <label class="tc-switch">
                                <input type="checkbox" name="data[RealEstateThemeSetting][hero_audio_btn_enabled]" value="1" <?php echo (!isset($ts['hero_audio_btn_enabled']) || !empty($ts['hero_audio_btn_enabled'])) ? 'checked' : ''; ?>>
                                <span class="tc-slider"></span>
                            </label>
                            <div>
                                <strong style="color: #0f172a; font-size: 13px;"><?php echo __('Hero Video Audio Mute / Unmute Button'); ?></strong>
                                <div class="text-muted" style="font-size: 11px;"><?php echo __('Show interactive floating volume control button on the hero video.'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Flash Updates & Ticker Bar -->
            <div class="tc-card" id="sec-ticker" data-section-key="ticker">
                <div class="tc-card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <h3 class="tc-card-title"><i class="fa fa-bullhorn text-danger"></i> <?php echo __('3. Flash Updates & Announcement Ticker'); ?></h3>
                        <span class="badge badge-danger"><?php echo __('Emoji-Ready'); ?></span>
                    </div>
                    <div class="tc-card-move-actions" style="display: flex; align-items: center; gap: 4px; margin-right: 8px;">
    <button type="button" class="btn btn-xs btn-default btn-move-card-up" style="padding: 2px 7px; font-weight: 700; font-size: 11px; border-radius: 4px;" title="Move Section UP on Homepage">
        <i class="fa fa-arrow-up text-primary"></i>
    </button>
    <button type="button" class="btn btn-xs btn-default btn-move-card-down" style="padding: 2px 7px; font-weight: 700; font-size: 11px; border-radius: 4px;" title="Move Section DOWN on Homepage">
        <i class="fa fa-arrow-down text-primary"></i>
    </button>
</div>
<div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;"><?php echo __('Show Section'); ?></span>
                        <label class="tc-switch" style="margin: 0;" title="<?php echo __('Toggle Ticker Bar Visibility'); ?>">
                            <input type="checkbox" name="data[RealEstateThemeSetting][ticker_enabled]" value="1" <?php echo (!isset($ts['ticker_enabled']) || !empty($ts['ticker_enabled'])) ? 'checked' : ''; ?>>
                            <span class="tc-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="tc-card-body">
                    <div class="tc-grid-3">
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Badge Title / Label'); ?> <span class="badge badge-default" style="font-size: 10px;">Supports Emojis</span></label>
                            <input type="text" id="tickerBadgeInput" name="data[RealEstateThemeSetting][ticker_badge_text]" class="tc-input" value="<?php echo h(!empty($ts['ticker_badge_text']) ? $ts['ticker_badge_text'] : '🔥 LATEST UPDATES'); ?>" placeholder="🔥 LATEST UPDATES">
                        </div>
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Badge Background Color'); ?></label>
                            <div style="display: flex; gap: 6px; align-items: center;">
                                <input type="color" name="data[RealEstateThemeSetting][ticker_badge_bg]" class="form-control" style="width: 48px; height: 38px; padding: 2px; border-radius: 6px;" value="<?php echo h(!empty($ts['ticker_badge_bg']) ? $ts['ticker_badge_bg'] : '#d11242'); ?>">
                                <input type="text" class="tc-input" value="<?php echo h(!empty($ts['ticker_badge_bg']) ? $ts['ticker_badge_bg'] : '#d11242'); ?>" readonly style="flex: 1; font-family: monospace;">
                            </div>
                        </div>
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Badge Text Color'); ?></label>
                            <div style="display: flex; gap: 6px; align-items: center;">
                                <input type="color" name="data[RealEstateThemeSetting][ticker_badge_color]" class="form-control" style="width: 48px; height: 38px; padding: 2px; border-radius: 6px;" value="<?php echo h(!empty($ts['ticker_badge_color']) ? $ts['ticker_badge_color'] : '#ffffff'); ?>">
                                <input type="text" class="tc-input" value="<?php echo h(!empty($ts['ticker_badge_color']) ? $ts['ticker_badge_color'] : '#ffffff'); ?>" readonly style="flex: 1; font-family: monospace;">
                            </div>
                        </div>
                    </div>

                    <div class="tc-grid-3" style="margin-top: 10px;">
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Bar Background Color'); ?></label>
                            <div style="display: flex; gap: 6px; align-items: center;">
                                <input type="color" name="data[RealEstateThemeSetting][ticker_bg_color]" class="form-control" style="width: 48px; height: 38px; padding: 2px; border-radius: 6px;" value="<?php echo h(!empty($ts['ticker_bg_color']) ? $ts['ticker_bg_color'] : '#07152a'); ?>">
                                <input type="text" class="tc-input" value="<?php echo h(!empty($ts['ticker_bg_color']) ? $ts['ticker_bg_color'] : '#07152a'); ?>" readonly style="flex: 1; font-family: monospace;">
                            </div>
                        </div>
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Bar Text Color'); ?></label>
                            <div style="display: flex; gap: 6px; align-items: center;">
                                <input type="color" name="data[RealEstateThemeSetting][ticker_text_color]" class="form-control" style="width: 48px; height: 38px; padding: 2px; border-radius: 6px;" value="<?php echo h(!empty($ts['ticker_text_color']) ? $ts['ticker_text_color'] : '#ffffff'); ?>">
                                <input type="text" class="tc-input" value="<?php echo h(!empty($ts['ticker_text_color']) ? $ts['ticker_text_color'] : '#ffffff'); ?>" readonly style="flex: 1; font-family: monospace;">
                            </div>
                        </div>
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Marquee Scroll Speed'); ?> <span class="text-muted" style="font-size: 11px;">(Seconds: 15-60)</span></label>
                            <input type="number" name="data[RealEstateThemeSetting][ticker_speed]" min="5" max="120" class="tc-input" value="<?php echo (int)(!empty($ts['ticker_speed']) ? $ts['ticker_speed'] : 30); ?>">
                        </div>
                    </div>

                    <div class="tc-grid-2" style="margin-top: 10px;">
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Optional Action / View Link'); ?></label>
                            <input type="text" name="data[RealEstateThemeSetting][ticker_link]" class="tc-input" value="<?php echo h(!empty($ts['ticker_link']) ? $ts['ticker_link'] : ''); ?>" placeholder="e.g. #contact-form or /paymentplans">
                        </div>
                        <div class="tc-form-group" style="display: flex; align-items: flex-end;">
                            <label style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 8px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="data[RealEstateThemeSetting][ticker_show_on_all_pages]" value="1" <?php echo !empty($ts['ticker_show_on_all_pages']) ? 'checked' : ''; ?> style="width: 18px; height: 18px; accent-color: #d11242;">
                                <?php echo __('Show this ticker bar on all inner pages as well'); ?>
                            </label>
                        </div>
                    </div>

                    <!-- Mobile Ticker Controls -->
                    <div class="tc-grid-2" style="margin-top: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                        <div>
                            <label style="font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 4px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="data[RealEstateThemeSetting][ticker_mobile_hide_badge]" value="1" <?php echo (!isset($ts['ticker_mobile_hide_badge']) || !empty($ts['ticker_mobile_hide_badge'])) ? 'checked' : ''; ?> style="width: 18px; height: 18px; accent-color: #d11242;">
                                <i class="fa fa-mobile text-danger" style="font-size: 18px;"></i> <?php echo __('Hide Badge Label on Mobile (Show Only Ticker)'); ?>
                            </label>
                            <div class="text-muted" style="font-size: 11.5px; margin-left: 26px;">
                                <?php echo __('Hides the badge on mobile phones to maximize full-width space for the scrolling announcements.'); ?>
                            </div>
                        </div>
                        <div>
                            <label style="font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 4px; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                                <input type="checkbox" name="data[RealEstateThemeSetting][ticker_mobile_enabled]" value="1" <?php echo (!isset($ts['ticker_mobile_enabled']) || !empty($ts['ticker_mobile_enabled'])) ? 'checked' : ''; ?> style="width: 18px; height: 18px; accent-color: #d11242;">
                                <i class="fa fa-eye text-primary"></i> <?php echo __('Enable Ticker on Mobile Devices'); ?>
                            </label>
                            <div class="text-muted" style="font-size: 11.5px; margin-left: 26px;">
                                <?php echo __('Keep ticker visible on mobile browsers.'); ?>
                            </div>
                        </div>
                    </div>

                    <div class="tc-form-group" style="margin-top: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <label class="tc-label" style="margin: 0;"><?php echo __('Ticker Announcement Content (Emojis & Unicode Supported)'); ?></label>
                            <div class="emoji-palette" style="display: flex; gap: 4px; flex-wrap: wrap;">
                                <span style="font-size: 11px; font-weight: 700; color: #64748b; margin-right: 4px; align-self: center;">Insert:</span>
                                <?php 
                                $emojis = array('🔥', '📢', '⚡', '🌴', '💎', '🏡', '✨', '🌟', '🚀', '📞', '📍', '🎯', '🏷️', '💰', '🏖️', '🏊‍♂️', '🛡️');
                                foreach ($emojis as $em): ?>
                                    <button type="button" class="btn btn-xs btn-default btn-insert-emoji" data-emoji="<?php echo $em; ?>" style="font-size: 13px; padding: 1px 5px; border-radius: 4px; line-height: 1.2;" title="Insert <?php echo $em; ?>"><?php echo $em; ?></button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <textarea id="tickerTextInput" name="data[RealEstateThemeSetting][ticker_text]" class="tc-textarea" rows="3" placeholder="Enter ticker announcements... Separate multiple news items with | or new lines"><?php echo h(!empty($ts['ticker_text']) ? $ts['ticker_text'] : '📢 Special Launch Offer: 10% Discount on Luxury 2 & 3 Bed Apartments! 🔥 | ⚡ Limited inventory available with flexible 36-month installment plans 🌴 | 💎 Book your site tour today and get exclusive VIP benefits 🏡 | ✨ Call +92339 1101156 for instant consultation!'); ?></textarea>
                        <div class="text-muted" style="font-size: 11.5px; margin-top: 4px;">
                            <i class="fa fa-info-circle text-primary"></i> <?php echo __('Separate multiple announcement slides with pipe (|) or new lines. Real emojis (🔥, 📢, ⚡, etc.) are fully supported and will not turn into ???.'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Intro Headings & Split Features -->
            <div class="tc-card" id="sec-intro" data-section-key="intro">
                <div class="tc-card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <h3 class="tc-card-title"><i class="fa fa-th-large text-danger"></i> <?php echo __('4. Intro Headings & 2-Column Split Features'); ?></h3>
                        <span class="badge badge-success"><?php echo __('Section 1'); ?></span>
                    </div>
                    <div class="tc-card-move-actions" style="display: flex; align-items: center; gap: 4px; margin-right: 8px;">
    <button type="button" class="btn btn-xs btn-default btn-move-card-up" style="padding: 2px 7px; font-weight: 700; font-size: 11px; border-radius: 4px;" title="Move Section UP on Homepage">
        <i class="fa fa-arrow-up text-primary"></i>
    </button>
    <button type="button" class="btn btn-xs btn-default btn-move-card-down" style="padding: 2px 7px; font-weight: 700; font-size: 11px; border-radius: 4px;" title="Move Section DOWN on Homepage">
        <i class="fa fa-arrow-down text-primary"></i>
    </button>
</div>
<div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;"><?php echo __('Show Section'); ?></span>
                        <label class="tc-switch" style="margin: 0;" title="<?php echo __('Toggle Intro Features Visibility'); ?>">
                            <input type="checkbox" name="data[RealEstateThemeSetting][intro_section_enabled]" value="1" <?php echo (!isset($ts['intro_section_enabled']) || !empty($ts['intro_section_enabled'])) ? 'checked' : ''; ?>>
                            <span class="tc-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="tc-card-body">
                    <div class="tc-grid-2" style="margin-bottom: 14px;">
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Main Intro Heading'); ?></label>
                            <input type="text" name="data[RealEstateThemeSetting][intro_heading]" class="tc-input" value="<?php echo h(!empty($ts['intro_heading']) ? $ts['intro_heading'] : 'UNLOCK YOUR NEXT HOME'); ?>">
                        </div>
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Sub-heading'); ?></label>
                            <input type="text" name="data[RealEstateThemeSetting][intro_subheading]" class="tc-input" value="<?php echo h(!empty($ts['intro_subheading']) ? $ts['intro_subheading'] : 'Unrivalled opportunities, unlimited potential'); ?>">
                        </div>
                    </div>

                    <!-- Split Feature 1 -->
                    <div class="tc-sub-box">
                        <div class="tc-sub-heading"><i class="fa fa-clone text-primary"></i> <?php echo __('Split Feature #1 (Left Text, Right Image)'); ?></div>
                        <div class="tc-grid-2">
                            <div class="tc-form-group">
                                <label class="tc-label"><?php echo __('Badge'); ?></label>
                                <input type="text" name="data[RealEstateThemeSetting][intro_f1_badge]" class="tc-input" value="<?php echo h(!empty($ts['intro_f1_badge']) ? $ts['intro_f1_badge'] : 'COSTA DEL SOL • WHITE VILLAS'); ?>">
                            </div>
                            <div class="tc-form-group">
                                <label class="tc-label"><?php echo __('Title'); ?></label>
                                <input type="text" name="data[RealEstateThemeSetting][intro_f1_title]" class="tc-input" value="<?php echo h(!empty($ts['intro_f1_title']) ? $ts['intro_f1_title'] : 'EXCEPTIONAL APARTMENTS FOR SALE'); ?>">
                            </div>
                        </div>
                        <div class="tc-grid-2">
                            <div class="tc-form-group">
                                <label class="tc-label"><?php echo __('Description'); ?></label>
                                <textarea name="data[RealEstateThemeSetting][intro_f1_desc]" class="tc-textarea" rows="3"><?php echo h(!empty($ts['intro_f1_desc']) ? $ts['intro_f1_desc'] : 'Ramada Hotels & Suites on Spain’s legendary Costa del Sol can offer an array of exceptional apartments.'); ?></textarea>
                            </div>
                            <div class="tc-form-group">
                                <label class="tc-label"><?php echo __('Button Text'); ?></label>
                                <input type="text" name="data[RealEstateThemeSetting][intro_f1_btn_text]" class="tc-input" value="<?php echo h(!empty($ts['intro_f1_btn_text']) ? $ts['intro_f1_btn_text'] : 'CONTACT US'); ?>">
                                <div style="margin-top: 10px;">
                                    <label class="tc-label"><?php echo __('Feature Photo'); ?></label>
                                    <div class="tc-photo-card">
                                        <img src="<?php echo $this->Html->url(!empty($ts['intro_f1_image']) ? $ts['intro_f1_image'] : '/img/theme_customizer/placeholder.jpg'); ?>" class="tc-photo-thumb" onerror="this.src='https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=200';">
                                        <div class="tc-photo-inputs">
                                            <input type="text" name="data[RealEstateThemeSetting][intro_f1_image]" class="tc-input" value="<?php echo h(!empty($ts['intro_f1_image']) ? $ts['intro_f1_image'] : ''); ?>" placeholder="Photo URL">
                                            <input type="file" name="intro_f1_image_file" class="tc-input" style="padding: 3px 6px; font-size: 11px; height: 30px;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Split Feature 2 -->
                    <div class="tc-sub-box">
                        <div class="tc-sub-heading"><i class="fa fa-clone text-primary"></i> <?php echo __('Split Feature #2 (Left Image, Right Text)'); ?></div>
                        <div class="tc-grid-2">
                            <div class="tc-form-group">
                                <label class="tc-label"><?php echo __('Badge'); ?></label>
                                <input type="text" name="data[RealEstateThemeSetting][intro_f2_badge]" class="tc-input" value="<?php echo h(!empty($ts['intro_f2_badge']) ? $ts['intro_f2_badge'] : 'RESORT-BASED OWNERSHIP'); ?>">
                            </div>
                            <div class="tc-form-group">
                                <label class="tc-label"><?php echo __('Title'); ?></label>
                                <input type="text" name="data[RealEstateThemeSetting][intro_f2_title]" class="tc-input" value="<?php echo h(!empty($ts['intro_f2_title']) ? $ts['intro_f2_title'] : 'STRONG RENTAL RETURNS'); ?>">
                            </div>
                        </div>
                        <div class="tc-grid-2">
                            <div class="tc-form-group">
                                <label class="tc-label"><?php echo __('Description'); ?></label>
                                <textarea name="data[RealEstateThemeSetting][intro_f2_desc]" class="tc-textarea" rows="3"><?php echo h(!empty($ts['intro_f2_desc']) ? $ts['intro_f2_desc'] : 'Whether you’re looking for a new home, holiday property or an investment opportunity with strong potential rental income...'); ?></textarea>
                            </div>
                            <div class="tc-form-group">
                                <label class="tc-label"><?php echo __('Button Text'); ?></label>
                                <input type="text" name="data[RealEstateThemeSetting][intro_f2_btn_text]" class="tc-input" value="<?php echo h(!empty($ts['intro_f2_btn_text']) ? $ts['intro_f2_btn_text'] : 'CONTACT US'); ?>">
                                <div style="margin-top: 10px;">
                                    <label class="tc-label"><?php echo __('Feature Photo'); ?></label>
                                    <div class="tc-photo-card">
                                        <img src="<?php echo $this->Html->url(!empty($ts['intro_f2_image']) ? $ts['intro_f2_image'] : '/img/theme_customizer/placeholder.jpg'); ?>" class="tc-photo-thumb" onerror="this.src='https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=200';">
                                        <div class="tc-photo-inputs">
                                            <input type="text" name="data[RealEstateThemeSetting][intro_f2_image]" class="tc-input" value="<?php echo h(!empty($ts['intro_f2_image']) ? $ts['intro_f2_image'] : ''); ?>" placeholder="Photo URL">
                                            <input type="file" name="intro_f2_image_file" class="tc-input" style="padding: 3px 6px; font-size: 11px; height: 30px;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. Properties Showcase Section -->
            <div class="tc-card" id="sec-properties" data-section-key="properties">
                <div class="tc-card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <h3 class="tc-card-title"><i class="fa fa-building text-danger"></i> <?php echo __('5. Properties For Sale Showcase & Action Buttons'); ?></h3>
                        <span class="badge badge-warning"><?php echo __('Live Database'); ?></span>
                    </div>
                    <div class="tc-card-move-actions" style="display: flex; align-items: center; gap: 4px; margin-right: 8px;">
    <button type="button" class="btn btn-xs btn-default btn-move-card-up" style="padding: 2px 7px; font-weight: 700; font-size: 11px; border-radius: 4px;" title="Move Section UP on Homepage">
        <i class="fa fa-arrow-up text-primary"></i>
    </button>
    <button type="button" class="btn btn-xs btn-default btn-move-card-down" style="padding: 2px 7px; font-weight: 700; font-size: 11px; border-radius: 4px;" title="Move Section DOWN on Homepage">
        <i class="fa fa-arrow-down text-primary"></i>
    </button>
</div>
<div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;"><?php echo __('Show Section'); ?></span>
                        <label class="tc-switch" style="margin: 0;" title="<?php echo __('Toggle Properties Section Visibility'); ?>">
                            <input type="checkbox" name="data[RealEstateThemeSetting][properties_section_enabled]" value="1" <?php echo (!isset($ts['properties_section_enabled']) || !empty($ts['properties_section_enabled'])) ? 'checked' : ''; ?>>
                            <span class="tc-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="tc-card-body">
                    <div class="tc-grid-3">
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Section Subtitle'); ?></label>
                            <input type="text" name="data[RealEstateThemeSetting][resorts_subtitle]" class="tc-input" value="<?php echo h(!empty($ts['resorts_subtitle']) ? $ts['resorts_subtitle'] : 'EXCLUSIVE DEVELOPMENTS'); ?>">
                        </div>
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Section Main Title'); ?></label>
                            <input type="text" name="data[RealEstateThemeSetting][resorts_title]" class="tc-input" value="<?php echo h(!empty($ts['resorts_title']) ? $ts['resorts_title'] : 'PROPERTIES FOR SALE'); ?>">
                        </div>
                        <div class="tc-form-group">
                            <label class="tc-label"><i class="fa fa-list-ol text-danger"></i> <?php echo __('Number of Properties to Show'); ?></label>
                            <input type="number" name="data[RealEstateThemeSetting][properties_count]" class="tc-input" value="<?php echo h(!empty($ts['properties_count']) ? (int)$ts['properties_count'] : 4); ?>" min="1" max="30" step="1" placeholder="4">
                        </div>
                    </div>
                    <div class="alert alert-info" style="margin: 10px 0 0 0; padding: 10px 14px; font-size: 12px; border-radius: 6px;">
                        <i class="fa fa-info-circle"></i> <?php echo __('Set how many live luxury properties are fetched sequentially from your active database and displayed in the tall showcase section on the homepage.'); ?>
                    </div>

                    <!-- Property Action Buttons Customizer (Rename & PC vs Mobile Toggles) -->
                    <div class="tc-sub-box" style="margin-top: 18px; border: 1.5px solid #dbeafe; background: #f8fafc; border-radius: 8px; padding: 16px;">
                        <div class="tc-sub-heading" style="color: #1e3a8a; font-weight: 800; font-size: 14px; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
                            <span><i class="fa fa-sliders text-primary"></i> <?php echo __('Property Card Action Buttons (Rename & Mobile / PC Visibility)'); ?></span>
                            <span class="badge badge-info" style="font-size: 10px; font-weight: 700;"><?php echo __('Separate PC & Mobile Controls'); ?></span>
                        </div>
                        <div style="font-size: 12px; color: #475569; margin-bottom: 16px;">
                            <?php echo __('Customize the button labels and toggle their visibility independently for desktop computers (PC) and mobile screens.'); ?>
                        </div>

                        <!-- 1. PDF Payment Plan Button -->
                        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin-bottom: 12px;">
                            <div class="row align-items-center g-3">
                                <div class="col-12 col-md-6">
                                    <label class="tc-label" style="font-weight: 700; display: flex; align-items: center; gap: 6px;">
                                        <i class="fa fa-file-pdf-o text-danger"></i> <?php echo __('Payment Plan (PDF) Button Text'); ?>
                                    </label>
                                    <input type="text" name="data[RealEstateThemeSetting][btn_pdf_text]" class="tc-input" value="<?php echo h(!empty($ts['btn_pdf_text']) ? $ts['btn_pdf_text'] : 'Download Payment Plan (PDF)'); ?>" placeholder="Download Payment Plan (PDF)">
                                    <div class="text-muted" style="font-size: 11px; margin-top: 4px;"><?php echo __('e.g. Payment Plan, Download Payment Plan (PDF), Get Brochure PDF'); ?></div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="tc-toggle-wrap" style="background: #f8fafc; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                        <label class="tc-switch" style="margin: 0;">
                                            <input type="checkbox" name="data[RealEstateThemeSetting][btn_pdf_pc_enabled]" value="1" <?php echo (!isset($ts['btn_pdf_pc_enabled']) || !empty($ts['btn_pdf_pc_enabled'])) ? 'checked' : ''; ?>>
                                            <span class="tc-slider"></span>
                                        </label>
                                        <div>
                                            <strong style="font-size: 12px; color: #0f172a;"><i class="fa fa-desktop text-primary"></i> <?php echo __('Show on PC'); ?></strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="tc-toggle-wrap" style="background: #f8fafc; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                        <label class="tc-switch" style="margin: 0;">
                                            <input type="checkbox" name="data[RealEstateThemeSetting][btn_pdf_mobile_enabled]" value="1" <?php echo (!isset($ts['btn_pdf_mobile_enabled']) || !empty($ts['btn_pdf_mobile_enabled'])) ? 'checked' : ''; ?>>
                                            <span class="tc-slider"></span>
                                        </label>
                                        <div>
                                            <strong style="font-size: 12px; color: #0f172a;"><i class="fa fa-mobile text-primary" style="font-size: 15px;"></i> <?php echo __('Show on Mobile'); ?></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Agent WhatsApp Button -->
                        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin-bottom: 12px;">
                            <div class="row align-items-center g-3">
                                <div class="col-12 col-md-6">
                                    <label class="tc-label" style="font-weight: 700; display: flex; align-items: center; gap: 6px;">
                                        <i class="fa fa-whatsapp text-success" style="font-size: 15px;"></i> <?php echo __('Agent WhatsApp Button Text'); ?>
                                    </label>
                                    <input type="text" name="data[RealEstateThemeSetting][btn_whatsapp_text]" class="tc-input" value="<?php echo h(!empty($ts['btn_whatsapp_text']) ? $ts['btn_whatsapp_text'] : 'WhatsApp'); ?>" placeholder="WhatsApp">
                                    <div class="text-muted" style="font-size: 11px; margin-top: 4px;"><?php echo __('e.g. WhatsApp, Chat with Agent, Direct WhatsApp'); ?></div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="tc-toggle-wrap" style="background: #f8fafc; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                        <label class="tc-switch" style="margin: 0;">
                                            <input type="checkbox" name="data[RealEstateThemeSetting][btn_whatsapp_pc_enabled]" value="1" <?php echo (!isset($ts['btn_whatsapp_pc_enabled']) || !empty($ts['btn_whatsapp_pc_enabled'])) ? 'checked' : ''; ?>>
                                            <span class="tc-slider"></span>
                                        </label>
                                        <div>
                                            <strong style="font-size: 12px; color: #0f172a;"><i class="fa fa-desktop text-success"></i> <?php echo __('Show on PC'); ?></strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="tc-toggle-wrap" style="background: #f8fafc; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                        <label class="tc-switch" style="margin: 0;">
                                            <input type="checkbox" name="data[RealEstateThemeSetting][btn_whatsapp_mobile_enabled]" value="1" <?php echo (!isset($ts['btn_whatsapp_mobile_enabled']) || !empty($ts['btn_whatsapp_mobile_enabled'])) ? 'checked' : ''; ?>>
                                            <span class="tc-slider"></span>
                                        </label>
                                        <div>
                                            <strong style="font-size: 12px; color: #0f172a;"><i class="fa fa-mobile text-success" style="font-size: 15px;"></i> <?php echo __('Show on Mobile'); ?></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Call Owner / Agent Button -->
                        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px;">
                            <div class="row align-items-center g-3">
                                <div class="col-12 col-md-6">
                                    <label class="tc-label" style="font-weight: 700; display: flex; align-items: center; gap: 6px;">
                                        <i class="fa fa-phone text-primary"></i> <?php echo __('Call Button Text'); ?>
                                    </label>
                                    <input type="text" name="data[RealEstateThemeSetting][btn_call_text]" class="tc-input" value="<?php echo h(!empty($ts['btn_call_text']) ? $ts['btn_call_text'] : 'Call Owner'); ?>" placeholder="Call Owner">
                                    <div class="text-muted" style="font-size: 11px; margin-top: 4px;"><?php echo __('e.g. Call Owner, Call Agent, Instant Call'); ?></div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="tc-toggle-wrap" style="background: #f8fafc; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                        <label class="tc-switch" style="margin: 0;">
                                            <input type="checkbox" name="data[RealEstateThemeSetting][btn_call_pc_enabled]" value="1" <?php echo (!isset($ts['btn_call_pc_enabled']) || !empty($ts['btn_call_pc_enabled'])) ? 'checked' : ''; ?>>
                                            <span class="tc-slider"></span>
                                        </label>
                                        <div>
                                            <strong style="font-size: 12px; color: #0f172a;"><i class="fa fa-desktop text-primary"></i> <?php echo __('Show on PC'); ?></strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="tc-toggle-wrap" style="background: #f8fafc; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                        <label class="tc-switch" style="margin: 0;">
                                            <input type="checkbox" name="data[RealEstateThemeSetting][btn_call_mobile_enabled]" value="1" <?php echo (!isset($ts['btn_call_mobile_enabled']) || !empty($ts['btn_call_mobile_enabled'])) ? 'checked' : ''; ?>>
                                            <span class="tc-slider"></span>
                                        </label>
                                        <div>
                                            <strong style="font-size: 12px; color: #0f172a;"><i class="fa fa-mobile text-primary" style="font-size: 15px;"></i> <?php echo __('Show on Mobile'); ?></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Assigned Agent on Property Card -->
                        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin-top: 12px;">
                            <div class="row align-items-center g-3">
                                <div class="col-12 col-md-6">
                                    <label class="tc-label" style="font-weight: 700; display: flex; align-items: center; gap: 6px;">
                                        <i class="fa fa-user-secret text-info" style="font-size: 15px;"></i> <?php echo __('Assigned Agent Badge Label'); ?>
                                    </label>
                                    <input type="text" name="data[RealEstateThemeSetting][card_agent_badge_text]" class="tc-input" value="<?php echo h(!empty($ts['card_agent_badge_text']) ? $ts['card_agent_badge_text'] : 'Verified Agent'); ?>" placeholder="Verified Agent">
                                    <div class="text-muted" style="font-size: 11px; margin-top: 4px;"><?php echo __('Displays next to the property representative name on listings & cards.'); ?></div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="tc-toggle-wrap" style="background: #f8fafc; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                        <label class="tc-switch" style="margin: 0;">
                                            <input type="checkbox" name="data[RealEstateThemeSetting][card_agent_pc_enabled]" value="1" <?php echo (!isset($ts['card_agent_pc_enabled']) || !empty($ts['card_agent_pc_enabled'])) ? 'checked' : ''; ?>>
                                            <span class="tc-slider"></span>
                                        </label>
                                        <div>
                                            <strong style="font-size: 12px; color: #0f172a;"><i class="fa fa-desktop text-info"></i> <?php echo __('Show Agent on PC'); ?></strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="tc-toggle-wrap" style="background: #f8fafc; padding: 8px 12px; border-radius: 6px; border: 1px solid #e2e8f0;">
                                        <label class="tc-switch" style="margin: 0;">
                                            <input type="checkbox" name="data[RealEstateThemeSetting][card_agent_mobile_enabled]" value="1" <?php echo (!isset($ts['card_agent_mobile_enabled']) || !empty($ts['card_agent_mobile_enabled'])) ? 'checked' : ''; ?>>
                                            <span class="tc-slider"></span>
                                        </label>
                                        <div>
                                            <strong style="font-size: 12px; color: #0f172a;"><i class="fa fa-mobile text-info" style="font-size: 15px;"></i> <?php echo __('Show Agent on Mobile'); ?></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- 6. Reasons to Own -->
            <div class="tc-card" id="sec-reasons" data-section-key="reasons">
                <div class="tc-card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <h3 class="tc-card-title"><i class="fa fa-star text-danger"></i> <?php echo __('6. Reasons to Own (Red Accent Banner)'); ?></h3>
                        <span class="badge badge-danger"><?php echo __('Accent Banner'); ?></span>
                    </div>
                    <div class="tc-card-move-actions" style="display: flex; align-items: center; gap: 4px; margin-right: 8px;">
    <button type="button" class="btn btn-xs btn-default btn-move-card-up" style="padding: 2px 7px; font-weight: 700; font-size: 11px; border-radius: 4px;" title="Move Section UP on Homepage">
        <i class="fa fa-arrow-up text-primary"></i>
    </button>
    <button type="button" class="btn btn-xs btn-default btn-move-card-down" style="padding: 2px 7px; font-weight: 700; font-size: 11px; border-radius: 4px;" title="Move Section DOWN on Homepage">
        <i class="fa fa-arrow-down text-primary"></i>
    </button>
</div>
<div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;"><?php echo __('Show Section'); ?></span>
                        <label class="tc-switch" style="margin: 0;" title="<?php echo __('Toggle Reasons to Own Banner Visibility'); ?>">
                            <input type="checkbox" name="data[RealEstateThemeSetting][reasons_section_enabled]" value="1" <?php echo (!isset($ts['reasons_section_enabled']) || !empty($ts['reasons_section_enabled'])) ? 'checked' : ''; ?>>
                            <span class="tc-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="tc-card-body">
                    <div class="tc-grid-2">
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Banner Super Title / Heading'); ?></label>
                            <input type="text" name="data[RealEstateThemeSetting][reasons_title]" class="tc-input" value="<?php echo h(!empty($ts['reasons_title']) ? $ts['reasons_title'] : 'REASONS TO OWN'); ?>">
                        </div>
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Banner CTA Button Text'); ?></label>
                            <input type="text" name="data[RealEstateThemeSetting][reasons_btn_text]" class="tc-input" value="<?php echo h(!empty($ts['reasons_btn_text']) ? $ts['reasons_btn_text'] : 'CONTACT US'); ?>">
                        </div>
                    </div>

                    <div class="tc-grid-2" style="margin-top: 10px;">
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Banner Bullet Points (One per line)'); ?></label>
                            <textarea name="data[RealEstateThemeSetting][reasons_items]" class="tc-textarea" rows="4"><?php echo h(!empty($ts['reasons_items']) ? $ts['reasons_items'] : "High, guaranteed rental returns\nGenerous owner holiday usage\n24-hour concierge, security, and resort services\nPrime Costa del Sol location close to Marbella"); ?></textarea>
                        </div>
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Accent Banner Photo'); ?></label>
                            <div class="tc-photo-card">
                                <img src="<?php echo $this->Html->url(!empty($ts['reasons_image']) ? $ts['reasons_image'] : '/img/theme_customizer/placeholder.jpg'); ?>" class="tc-photo-thumb" onerror="this.src='https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=200';">
                                <div class="tc-photo-inputs">
                                    <input type="text" name="data[RealEstateThemeSetting][reasons_image]" class="tc-input" value="<?php echo h(!empty($ts['reasons_image']) ? $ts['reasons_image'] : ''); ?>" placeholder="Photo URL">
                                    <input type="file" name="reasons_image_file" class="tc-input" style="padding: 3px 6px; font-size: 11px; height: 30px;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 7. Surrounding Area Highlights -->
            <div class="tc-card" id="sec-area" data-section-key="area">
                <div class="tc-card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <h3 class="tc-card-title"><i class="fa fa-map-marker text-danger"></i> <?php echo __('7. Surrounding Area & Location Highlights'); ?></h3>
                        <span class="badge badge-info"><?php echo __('Location'); ?></span>
                    </div>
                    <div class="tc-card-move-actions" style="display: flex; align-items: center; gap: 4px; margin-right: 8px;">
    <button type="button" class="btn btn-xs btn-default btn-move-card-up" style="padding: 2px 7px; font-weight: 700; font-size: 11px; border-radius: 4px;" title="Move Section UP on Homepage">
        <i class="fa fa-arrow-up text-primary"></i>
    </button>
    <button type="button" class="btn btn-xs btn-default btn-move-card-down" style="padding: 2px 7px; font-weight: 700; font-size: 11px; border-radius: 4px;" title="Move Section DOWN on Homepage">
        <i class="fa fa-arrow-down text-primary"></i>
    </button>
</div>
<div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;"><?php echo __('Show Section'); ?></span>
                        <label class="tc-switch" style="margin: 0;" title="<?php echo __('Toggle Surrounding Area Section Visibility'); ?>">
                            <input type="checkbox" name="data[RealEstateThemeSetting][area_section_enabled]" value="1" <?php echo (!isset($ts['area_section_enabled']) || !empty($ts['area_section_enabled'])) ? 'checked' : ''; ?>>
                            <span class="tc-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="tc-card-body">
                    <div class="tc-grid-2">
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Area Subtitle'); ?></label>
                            <input type="text" name="data[RealEstateThemeSetting][area_subtitle]" class="tc-input" value="<?php echo h(!empty($ts['area_subtitle']) ? $ts['area_subtitle'] : 'SURROUNDING AREA'); ?>">
                        </div>
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Main Title'); ?></label>
                            <input type="text" name="data[RealEstateThemeSetting][area_title]" class="tc-input" value="<?php echo h(!empty($ts['area_title']) ? $ts['area_title'] : 'MORE THAN A HOME'); ?>">
                        </div>
                    </div>
                    <div class="tc-form-group">
                        <label class="tc-label"><?php echo __('Area Section Description'); ?></label>
                        <textarea name="data[RealEstateThemeSetting][area_desc]" class="tc-textarea" rows="2"><?php echo h(!empty($ts['area_desc']) ? $ts['area_desc'] : 'Explore the finest coastal destinations, premier golf clubs, and exceptional dining.'); ?></textarea>
                    </div>

                    <!-- Location 1 -->
                    <div class="tc-sub-box">
                        <div class="tc-sub-heading"><?php echo __('Location Card #1'); ?></div>
                        <div class="tc-grid-2">
                            <div class="tc-form-group">
                                <label class="tc-label"><?php echo __('Card Title & Description'); ?></label>
                                <input type="text" name="data[RealEstateThemeSetting][area_card1_title]" class="tc-input" value="<?php echo h(!empty($ts['area_card1_title']) ? $ts['area_card1_title'] : 'FUENGIROLA'); ?>" placeholder="Title" style="margin-bottom: 6px;">
                                <input type="text" name="data[RealEstateThemeSetting][area_card1_desc]" class="tc-input" value="<?php echo h(!empty($ts['area_card1_desc']) ? $ts['area_card1_desc'] : 'A lively coastal resort featuring miles of sandy beaches.'); ?>" placeholder="Description">
                            </div>
                            <div class="tc-form-group">
                                <label class="tc-label"><?php echo __('Photo'); ?></label>
                                <div class="tc-photo-card">
                                    <img src="<?php echo $this->Html->url(!empty($ts['area_card1_image']) ? $ts['area_card1_image'] : '/img/theme_customizer/placeholder.jpg'); ?>" class="tc-photo-thumb" onerror="this.src='https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=200';">
                                    <div class="tc-photo-inputs">
                                        <input type="text" name="data[RealEstateThemeSetting][area_card1_image]" class="tc-input" value="<?php echo h(!empty($ts['area_card1_image']) ? $ts['area_card1_image'] : ''); ?>" placeholder="Photo URL">
                                        <input type="file" name="area_card1_image_file" class="tc-input" style="padding: 3px 6px; font-size: 11px; height: 30px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Location 2 -->
                    <div class="tc-sub-box">
                        <div class="tc-sub-heading"><?php echo __('Location Card #2'); ?></div>
                        <div class="tc-grid-2">
                            <div class="tc-form-group">
                                <label class="tc-label"><?php echo __('Card Title & Description'); ?></label>
                                <input type="text" name="data[RealEstateThemeSetting][area_card2_title]" class="tc-input" value="<?php echo h(!empty($ts['area_card2_title']) ? $ts['area_card2_title'] : 'LA CALA DE MIJAS'); ?>" placeholder="Title" style="margin-bottom: 6px;">
                                <input type="text" name="data[RealEstateThemeSetting][area_card2_desc]" class="tc-input" value="<?php echo h(!empty($ts['area_card2_desc']) ? $ts['area_card2_desc'] : 'A charming village retaining authentic Andalusian character.'); ?>" placeholder="Description">
                            </div>
                            <div class="tc-form-group">
                                <label class="tc-label"><?php echo __('Photo'); ?></label>
                                <div class="tc-photo-card">
                                    <img src="<?php echo $this->Html->url(!empty($ts['area_card2_image']) ? $ts['area_card2_image'] : '/img/theme_customizer/placeholder.jpg'); ?>" class="tc-photo-thumb" onerror="this.src='https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=200';">
                                    <div class="tc-photo-inputs">
                                        <input type="text" name="data[RealEstateThemeSetting][area_card2_image]" class="tc-input" value="<?php echo h(!empty($ts['area_card2_image']) ? $ts['area_card2_image'] : ''); ?>" placeholder="Photo URL">
                                        <input type="file" name="area_card2_image_file" class="tc-input" style="padding: 3px 6px; font-size: 11px; height: 30px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Location 3 -->
                    <div class="tc-sub-box">
                        <div class="tc-sub-heading"><?php echo __('Location Card #3'); ?></div>
                        <div class="tc-grid-2">
                            <div class="tc-form-group">
                                <label class="tc-label"><?php echo __('Card Title & Description'); ?></label>
                                <input type="text" name="data[RealEstateThemeSetting][area_card3_title]" class="tc-input" value="<?php echo h(!empty($ts['area_card3_title']) ? $ts['area_card3_title'] : 'MARBELLA & PUERTO BANÚS'); ?>" placeholder="Title" style="margin-bottom: 6px;">
                                <input type="text" name="data[RealEstateThemeSetting][area_card3_desc]" class="tc-input" value="<?php echo h(!empty($ts['area_card3_desc']) ? $ts['area_card3_desc'] : 'World-famous luxury marina and designer shopping.'); ?>" placeholder="Description">
                            </div>
                            <div class="tc-form-group">
                                <label class="tc-label"><?php echo __('Photo'); ?></label>
                                <div class="tc-photo-card">
                                    <img src="<?php echo $this->Html->url(!empty($ts['area_card3_image']) ? $ts['area_card3_image'] : '/img/theme_customizer/placeholder.jpg'); ?>" class="tc-photo-thumb" onerror="this.src='https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=200';">
                                    <div class="tc-photo-inputs">
                                        <input type="text" name="data[RealEstateThemeSetting][area_card3_image]" class="tc-input" value="<?php echo h(!empty($ts['area_card3_image']) ? $ts['area_card3_image'] : ''); ?>" placeholder="Photo URL">
                                        <input type="file" name="area_card3_image_file" class="tc-input" style="padding: 3px 6px; font-size: 11px; height: 30px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 8. Expand Horizons & Legal Notice -->
            <div class="tc-card" id="sec-other" data-section-key="other">
                <div class="tc-card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <h3 class="tc-card-title"><i class="fa fa-globe text-danger"></i> <?php echo __('8. Expand Your Horizons & Legal Notice'); ?></h3>
                        <span class="badge badge-default"><?php echo __('Portfolio'); ?></span>
                    </div>
                    <div class="tc-card-move-actions" style="display: flex; align-items: center; gap: 4px; margin-right: 8px;">
    <button type="button" class="btn btn-xs btn-default btn-move-card-up" style="padding: 2px 7px; font-weight: 700; font-size: 11px; border-radius: 4px;" title="Move Section UP on Homepage">
        <i class="fa fa-arrow-up text-primary"></i>
    </button>
    <button type="button" class="btn btn-xs btn-default btn-move-card-down" style="padding: 2px 7px; font-weight: 700; font-size: 11px; border-radius: 4px;" title="Move Section DOWN on Homepage">
        <i class="fa fa-arrow-down text-primary"></i>
    </button>
</div>
<div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;"><?php echo __('Show Section'); ?></span>
                        <label class="tc-switch" style="margin: 0;" title="<?php echo __('Toggle Other Opportunities Visibility'); ?>">
                            <input type="checkbox" name="data[RealEstateThemeSetting][other_section_enabled]" value="1" <?php echo (!isset($ts['other_section_enabled']) || !empty($ts['other_section_enabled'])) ? 'checked' : ''; ?>>
                            <span class="tc-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="tc-card-body">
                    <div class="tc-grid-2">
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Subtitle & Title'); ?></label>
                            <input type="text" name="data[RealEstateThemeSetting][other_subtitle]" class="tc-input" value="<?php echo h(!empty($ts['other_subtitle']) ? $ts['other_subtitle'] : 'OTHER PROPERTIES'); ?>" placeholder="Subtitle" style="margin-bottom: 6px;">
                            <input type="text" name="data[RealEstateThemeSetting][other_title]" class="tc-input" value="<?php echo h(!empty($ts['other_title']) ? $ts['other_title'] : 'EXPAND YOUR HORIZONS'); ?>" placeholder="Main Title">
                        </div>
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Showcase Photo'); ?></label>
                            <div class="tc-photo-card">
                                <img src="<?php echo $this->Html->url(!empty($ts['other_image']) ? $ts['other_image'] : '/img/theme_customizer/placeholder.jpg'); ?>" class="tc-photo-thumb" onerror="this.src='https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=200';">
                                <div class="tc-photo-inputs">
                                    <input type="text" name="data[RealEstateThemeSetting][other_image]" class="tc-input" value="<?php echo h(!empty($ts['other_image']) ? $ts['other_image'] : ''); ?>" placeholder="Photo URL">
                                    <input type="file" name="other_image_file" class="tc-input" style="padding: 3px 6px; font-size: 11px; height: 30px;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tc-grid-2" style="margin-top: 10px;">
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Description'); ?></label>
                            <textarea name="data[RealEstateThemeSetting][other_desc]" class="tc-textarea" rows="3"><?php echo h(!empty($ts['other_desc']) ? $ts['other_desc'] : 'Discover an exceptional portfolio of properties in Spain, UK, and worldwide destinations.'); ?></textarea>
                        </div>
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Disclaimer Notice Text'); ?></label>
                            <textarea name="data[RealEstateThemeSetting][other_disclaimer]" class="tc-textarea" rows="3"><?php echo h(!empty($ts['other_disclaimer']) ? $ts['other_disclaimer'] : 'Purchaser should verify all details, floor plans, dimensions and specification with the sales team before entering contract.'); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 9. World-Class Amenities & Modern Facilities -->
            <div class="tc-card" id="sec-amenities" data-section-key="amenities">
                <div class="tc-card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <h3 class="tc-card-title"><i class="fa fa-th text-danger"></i> <?php echo __('9. World-Class Amenities & Facilities'); ?></h3>
                        <span class="badge badge-primary"><?php echo __('Lifestyle Features'); ?></span>
                    </div>
                    <div class="tc-card-move-actions" style="display: flex; align-items: center; gap: 4px; margin-right: 8px;">
    <button type="button" class="btn btn-xs btn-default btn-move-card-up" style="padding: 2px 7px; font-weight: 700; font-size: 11px; border-radius: 4px;" title="Move Section UP on Homepage">
        <i class="fa fa-arrow-up text-primary"></i>
    </button>
    <button type="button" class="btn btn-xs btn-default btn-move-card-down" style="padding: 2px 7px; font-weight: 700; font-size: 11px; border-radius: 4px;" title="Move Section DOWN on Homepage">
        <i class="fa fa-arrow-down text-primary"></i>
    </button>
</div>
<div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;"><?php echo __('Show Section'); ?></span>
                        <label class="tc-switch" style="margin: 0;" title="<?php echo __('Toggle Amenities Section Visibility'); ?>">
                            <input type="checkbox" name="data[RealEstateThemeSetting][amenities_enabled]" value="1" <?php echo (!isset($ts['amenities_enabled']) || !empty($ts['amenities_enabled'])) ? 'checked' : ''; ?>>
                            <span class="tc-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="tc-card-body">
                    <div class="tc-grid-2">
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Super Title / Subtitle'); ?></label>
                            <input type="text" name="data[RealEstateThemeSetting][amenities_super_title]" class="tc-input" value="<?php echo h(!empty($ts['amenities_super_title']) ? $ts['amenities_super_title'] : 'WORLD-CLASS LIVING'); ?>" placeholder="WORLD-CLASS LIVING">
                        </div>
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Main Heading'); ?></label>
                            <input type="text" name="data[RealEstateThemeSetting][amenities_main_title]" class="tc-input" value="<?php echo h(!empty($ts['amenities_main_title']) ? $ts['amenities_main_title'] : 'Premium Amenities & Modern Facilities'); ?>" placeholder="Premium Amenities & Modern Facilities">
                        </div>
                    </div>
                    <div class="tc-form-group" style="margin-top: 10px;">
                        <label class="tc-label"><?php echo __('Section Description'); ?></label>
                        <textarea name="data[RealEstateThemeSetting][amenities_desc]" class="tc-textarea" rows="2" placeholder="Section description..."><?php echo h(!empty($ts['amenities_desc']) ? $ts['amenities_desc'] : 'Experience an extraordinary standard of living with cutting-edge recreational hubs, uninterrupted utilities, executive leisure facilities, and state-of-the-art wellness zones.'); ?></textarea>
                    </div>

                    <!-- Amenities Items Manager -->
                    <div style="margin-top: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">
                            <label class="tc-label" style="margin: 0; font-size: 13px;"><?php echo __('Manage Amenities & Icons'); ?></label>
                            <button type="button" id="btnAddAmenity" class="btn btn-xs btn-success" style="font-weight: 700;">
                                <i class="fa fa-plus"></i> <?php echo __('Add New Amenity'); ?>
                            </button>
                        </div>

                        <div id="amenitiesContainer" class="row g-3">
                            <?php 
                            $aList = !empty($amenitiesList) ? $amenitiesList : array();
                            $index = 0;
                            foreach ($aList as $amenity): 
                                $index++;
                            ?>
                            <div class="col-12 col-md-6 amenity-item-row" id="amenity_row_<?php echo $index; ?>" style="margin-bottom: 16px;">
                                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04); height: 100%;">
                                    <!-- Dedicated Top Header Bar for Amenity Item -->
                                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #edf2f7; padding-bottom: 8px; margin-bottom: 12px;">
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <span class="badge badge-default" style="background: #07152a; color: #7cf9ff; font-weight: 700; font-size: 11px; padding: 4px 8px; border-radius: 4px;">#<?php echo $index; ?></span>
                                            <strong style="font-size: 12px; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px;"><?php echo __('Amenity Card'); ?></strong>
                                        </div>
                                        <button type="button" class="btn btn-xs btn-danger btn-remove-amenity" style="padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 4px; display: inline-flex; align-items: center; gap: 5px; box-shadow: 0 2px 6px rgba(220, 38, 38, 0.2);" title="<?php echo __('Delete this amenity'); ?>">
                                            <i class="fa fa-trash"></i> <?php echo __('Delete'); ?>
                                        </button>
                                    </div>

                                    <div class="row g-2">
                                        <div class="col-sm-6">
                                            <label style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 4px; display: block;"><?php echo __('Amenity Title'); ?> <span class="text-danger">*</span></label>
                                            <input type="text" name="data[RealEstateThemeSetting][amenities_items][<?php echo $index; ?>][title]" class="form-control input-sm" value="<?php echo h(isset($amenity['title']) ? $amenity['title'] : ''); ?>" placeholder="e.g. Commercial Hub" required style="border-radius: 6px; border: 1px solid #cbd5e1; height: 36px;">
                                        </div>
                                        <div class="col-sm-6">
                                            <label style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 4px; display: block;"><?php echo __('Badge / Category'); ?></label>
                                            <input type="text" name="data[RealEstateThemeSetting][amenities_items][<?php echo $index; ?>][badge]" class="form-control input-sm" value="<?php echo h(isset($amenity['badge']) ? $amenity['badge'] : ''); ?>" placeholder="e.g. Commercial" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 36px;">
                                        </div>
                                        <div class="col-sm-6" style="margin-top: 10px;">
                                            <label style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 4px; display: block;"><?php echo __('Icon Class'); ?></label>
                                            <div class="input-group input-group-sm" style="border-radius: 6px; overflow: hidden;">
                                                <span class="input-group-addon" style="background: #f1f5f9; border: 1px solid #cbd5e1; border-right: 0; color: #d11242; font-size: 14px;"><i class="fa <?php echo h(isset($amenity['icon']) ? str_replace('fa ', '', $amenity['icon']) : 'fa-star'); ?>"></i></span>
                                                <input type="text" name="data[RealEstateThemeSetting][amenities_items][<?php echo $index; ?>][icon]" class="form-control input-sm icon-input" value="<?php echo h(isset($amenity['icon']) ? $amenity['icon'] : 'fa-star'); ?>" placeholder="fa-shopping-bag" style="border: 1px solid #cbd5e1; height: 36px;">
                                            </div>
                                        </div>
                                        <div class="col-sm-6" style="margin-top: 10px; display: flex; align-items: flex-end;">
                                            <label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 8px; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                                <input type="checkbox" name="data[RealEstateThemeSetting][amenities_items][<?php echo $index; ?>][status]" value="1" <?php echo !isset($amenity['status']) || !empty($amenity['status']) ? 'checked' : ''; ?> style="width: 16px; height: 16px; accent-color: #d11242;">
                                                <?php echo __('Active / Visible'); ?>
                                            </label>
                                        </div>
                                        <div class="col-sm-12" style="margin-top: 10px;">
                                            <label style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 4px; display: block;"><?php echo __('Short Description'); ?></label>
                                            <textarea name="data[RealEstateThemeSetting][amenities_items][<?php echo $index; ?>][description]" class="form-control input-sm" rows="2" placeholder="Brief details about facility..." style="border-radius: 6px; border: 1px solid #cbd5e1;"><?php echo h(isset($amenity['description']) ? $amenity['description'] : ''); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 10. Get in Touch & Consultation Form -->
            <div class="tc-card" id="sec-contact" data-section-key="contact">
                <div class="tc-card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <h3 class="tc-card-title"><i class="fa fa-envelope text-danger"></i> <?php echo __('10. Get In Touch / Consultation Form'); ?></h3>
                        <span class="badge badge-success"><?php echo __('Lead Gen'); ?></span>
                    </div>
                    <div class="tc-card-move-actions" style="display: flex; align-items: center; gap: 4px; margin-right: 8px;">
    <button type="button" class="btn btn-xs btn-default btn-move-card-up" style="padding: 2px 7px; font-weight: 700; font-size: 11px; border-radius: 4px;" title="Move Section UP on Homepage">
        <i class="fa fa-arrow-up text-primary"></i>
    </button>
    <button type="button" class="btn btn-xs btn-default btn-move-card-down" style="padding: 2px 7px; font-weight: 700; font-size: 11px; border-radius: 4px;" title="Move Section DOWN on Homepage">
        <i class="fa fa-arrow-down text-primary"></i>
    </button>
</div>
<div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;"><?php echo __('Show Section'); ?></span>
                        <label class="tc-switch" style="margin: 0;" title="<?php echo __('Toggle Contact Section Visibility'); ?>">
                            <input type="checkbox" name="data[RealEstateThemeSetting][contact_section_enabled]" value="1" <?php echo (!isset($ts['contact_section_enabled']) || !empty($ts['contact_section_enabled'])) ? 'checked' : ''; ?>>
                            <span class="tc-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="tc-card-body">
                    <div class="tc-grid-3">
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Subtitle'); ?></label>
                            <input type="text" name="data[RealEstateThemeSetting][contact_subtitle]" class="tc-input" value="<?php echo h(!empty($ts['contact_subtitle']) ? $ts['contact_subtitle'] : 'GET IN TOUCH'); ?>">
                        </div>
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Main Title'); ?></label>
                            <input type="text" name="data[RealEstateThemeSetting][contact_title]" class="tc-input" value="<?php echo h(!empty($ts['contact_title']) ? $ts['contact_title'] : 'REQUEST A CONSULTATION'); ?>">
                        </div>
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Submit Button Text'); ?></label>
                            <input type="text" name="data[RealEstateThemeSetting][contact_btn_text]" class="tc-input" value="<?php echo h(!empty($ts['contact_btn_text']) ? $ts['contact_btn_text'] : 'GET IN TOUCH'); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 11. Newsletter & Footer Accordion -->
            <div class="tc-card" id="sec-newsletter" data-section-key="newsletter">
                <div class="tc-card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <h3 class="tc-card-title"><i class="fa fa-newspaper-o text-danger"></i> <?php echo __('11. Newsletter & Footer Accordion'); ?></h3>
                        <span class="badge badge-default"><?php echo __('Footer'); ?></span>
                    </div>
                    <div class="tc-card-move-actions" style="display: flex; align-items: center; gap: 4px; margin-right: 8px;">
    <button type="button" class="btn btn-xs btn-default btn-move-card-up" style="padding: 2px 7px; font-weight: 700; font-size: 11px; border-radius: 4px;" title="Move Section UP on Homepage">
        <i class="fa fa-arrow-up text-primary"></i>
    </button>
    <button type="button" class="btn btn-xs btn-default btn-move-card-down" style="padding: 2px 7px; font-weight: 700; font-size: 11px; border-radius: 4px;" title="Move Section DOWN on Homepage">
        <i class="fa fa-arrow-down text-primary"></i>
    </button>
</div>
<div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;"><?php echo __('Show Section'); ?></span>
                        <label class="tc-switch" style="margin: 0;" title="<?php echo __('Toggle Newsletter Section Visibility'); ?>">
                            <input type="checkbox" name="data[RealEstateThemeSetting][newsletter_section_enabled]" value="1" <?php echo (!isset($ts['newsletter_section_enabled']) || !empty($ts['newsletter_section_enabled'])) ? 'checked' : ''; ?>>
                            <span class="tc-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="tc-card-body">
                    <div class="tc-grid-3">
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Newsletter Title'); ?></label>
                            <input type="text" name="data[RealEstateThemeSetting][newsletter_title]" class="tc-input" value="<?php echo h(!empty($ts['newsletter_title']) ? $ts['newsletter_title'] : 'KEEP IN TOUCH'); ?>">
                        </div>
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Newsletter Subtitle'); ?></label>
                            <input type="text" name="data[RealEstateThemeSetting][newsletter_subtitle]" class="tc-input" value="<?php echo h(!empty($ts['newsletter_subtitle']) ? $ts['newsletter_subtitle'] : 'Subscribe to receive the latest property updates.'); ?>">
                        </div>
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Button Text'); ?></label>
                            <input type="text" name="data[RealEstateThemeSetting][newsletter_btn_text]" class="tc-input" value="<?php echo h(!empty($ts['newsletter_btn_text']) ? $ts['newsletter_btn_text'] : 'SUBSCRIBE'); ?>">
                        </div>
                    </div>
                    <div class="tc-grid-2" style="margin-top: 10px;">
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Footer Accordion Title'); ?></label>
                            <input type="text" name="data[RealEstateThemeSetting][accordion_title]" class="tc-input" value="<?php echo h(!empty($ts['accordion_title']) ? $ts['accordion_title'] : 'ABOUT OUR DEVELOPMENTS & RESORTS'); ?>">
                        </div>
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Accordion Expandable Content'); ?></label>
                            <textarea name="data[RealEstateThemeSetting][accordion_content]" class="tc-textarea" rows="2"><?php echo h(!empty($ts['accordion_content']) ? $ts['accordion_content'] : 'Discover why discerning property buyers trust White Villas for luxury residences and lifestyle excellence.'); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 12. Footer Branding & Legal Settings -->
            <div class="tc-card" id="sec-footer">
                <div class="tc-card-header">
                    <h3 class="tc-card-title"><i class="fa fa-info-circle text-danger"></i> <?php echo __('12. Footer Branding, Columns & Copyright'); ?></h3>
                    <span class="badge badge-info"><?php echo __('Footer'); ?></span>
                </div>
                <div class="tc-card-body">
                    <div class="tc-form-group">
                        <label class="tc-label"><?php echo __('Footer Brand About Text'); ?></label>
                        <textarea name="data[RealEstateThemeSetting][footer_about_text]" class="tc-textarea" rows="2" placeholder="Ramada Hotels & Suites and White Villas provide premier real estate solutions..."><?php echo h(!empty($ts['footer_about_text']) ? $ts['footer_about_text'] : 'Ramada Hotels & Suites and White Villas provide premier real estate solutions, luxury holiday villas, and verified investment opportunities with guaranteed transparency and high rental yields.'); ?></textarea>
                    </div>

                    <div class="tc-grid-3" style="margin-top: 10px;">
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Column 2 Title'); ?></label>
                            <input type="text" name="data[RealEstateThemeSetting][footer_col2_title]" class="tc-input" value="<?php echo h(!empty($ts['footer_col2_title']) ? $ts['footer_col2_title'] : 'Quick Links'); ?>">
                        </div>
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Column 3 Title'); ?></label>
                            <input type="text" name="data[RealEstateThemeSetting][footer_col3_title]" class="tc-input" value="<?php echo h(!empty($ts['footer_col3_title']) ? $ts['footer_col3_title'] : 'Information'); ?>">
                        </div>
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Column 4 Title'); ?></label>
                            <input type="text" name="data[RealEstateThemeSetting][footer_col4_title]" class="tc-input" value="<?php echo h(!empty($ts['footer_col4_title']) ? $ts['footer_col4_title'] : 'Contact Office'); ?>">
                        </div>
                    </div>

                    <div class="tc-grid-3" style="margin-top: 10px;">
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Custom Copyright Text'); ?></label>
                            <input type="text" name="data[RealEstateThemeSetting][footer_copyright_text]" class="tc-input" value="<?php echo h(!empty($ts['footer_copyright_text']) ? $ts['footer_copyright_text'] : ''); ?>" placeholder="Leave empty for default (© 2026 {BrandName}. All Rights Reserved.)">
                        </div>
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Developer / Credit Name'); ?></label>
                            <input type="text" name="data[RealEstateThemeSetting][footer_credit_name]" class="tc-input" value="<?php echo h(!empty($ts['footer_credit_name']) ? $ts['footer_credit_name'] : 'Al-Riaz Tech'); ?>" placeholder="Al-Riaz Tech">
                        </div>
                        <div class="tc-form-group">
                            <label class="tc-label"><?php echo __('Developer Website URL'); ?></label>
                            <input type="text" name="data[RealEstateThemeSetting][footer_credit_url]" class="tc-input" value="<?php echo h(!empty($ts['footer_credit_url']) ? $ts['footer_credit_url'] : 'https://alriaztech.com'); ?>" placeholder="https://alriaztech.com">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Sticky Action Bar -->
            <div style="position: sticky; bottom: 15px; background: #07152a; padding: 14px 20px; border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.25); display: flex; justify-content: space-between; align-items: center; z-index: 100;">
                <div style="color: #fff; font-size: 13px; font-weight: 600;">
                    <i class="fa fa-check-circle text-success"></i> <?php echo __('Ready to update your frontend? Click Save Changes.'); ?>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="<?php echo $this->Html->url('/'); ?>" target="_blank" class="btn btn-sm btn-default" style="font-weight: 700;">
                        <i class="fa fa-external-link"></i> <?php echo __('Preview Frontend'); ?>
                    </a>
                    <button type="submit" class="tc-btn-save" style="padding: 8px 24px;">
                        <i class="fa fa-save"></i> <?php echo __('Save Theme Settings'); ?>
                    </button>
                </div>
            </div>

        </div>
    </div>
</form>

<script type="text/javascript">
$(document).ready(function(){
    // Smooth scroll for left nav links
    $('.tc-nav-list a').on('click', function(e){
        var href = $(this).attr('href');
        if(href && href.indexOf('#') === 0) {
            var target = $(href);
            if(target.length) {
                e.preventDefault();
                $('html, body').stop().animate({
                    scrollTop: target.offset().top - 80
                }, 300);
                $('.tc-nav-list a').removeClass('active');
                $(this).addClass('active');
            }
        }
    });

    // ScrollSpy to highlight active sidebar link on scroll
    var navLinks = $('.tc-nav-list a[href^="#"]');
    $(window).on('scroll', function(){
        var scrollPos = $(document).scrollTop() + 120;
        var currentSection = null;
        $('.tc-card[id]').each(function(){
            var top = $(this).offset().top;
            var height = $(this).outerHeight();
            if (scrollPos >= top && scrollPos < top + height) {
                currentSection = $(this).attr('id');
            }
        });
        if (currentSection) {
            navLinks.removeClass('active');
            $('.tc-nav-list a[href="#' + currentSection + '"]').addClass('active');
        }
    });

    // Icon Preview update
    $(document).on('keyup change', '.icon-input', function(){
        var val = $(this).val().trim();
        var iconName = val.replace('fa ', '');
        var addon = $(this).closest('.input-group').find('.input-group-addon i');
        addon.attr('class', 'fa ' + iconName);
    });

    // Remove Amenity Row (Instant DOM removal on confirmation)
    $(document).on('click', '.btn-remove-amenity', function(e){
        e.preventDefault();
        var row = $(this).closest('.amenity-item-row');
        if(confirm('<?php echo __('Are you sure you want to delete this amenity?'); ?>')) {
            row.fadeOut(200, function(){
                row.remove();
            });
        }
    });

    // Add New Amenity Row
    var nextIndex = <?php echo count($aList) + 1; ?>;
    $('#btnAddAmenity').on('click', function(e){
        e.preventDefault();
        nextIndex++;
        var html = '<div class="col-12 col-md-6 amenity-item-row" id="amenity_row_' + nextIndex + '" style="margin-bottom: 16px; display: none;">' +
            '<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04); height: 100%;">' +
                '<div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #edf2f7; padding-bottom: 8px; margin-bottom: 12px;">' +
                    '<div style="display: flex; align-items: center; gap: 8px;">' +
                        '<span class="badge badge-default" style="background: #07152a; color: #7cf9ff; font-weight: 700; font-size: 11px; padding: 4px 8px; border-radius: 4px;">#' + nextIndex + '</span>' +
                        '<strong style="font-size: 12px; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px;"><?php echo __('Amenity Card'); ?></strong>' +
                    '</div>' +
                    '<button type="button" class="btn btn-xs btn-danger btn-remove-amenity" style="padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 4px; display: inline-flex; align-items: center; gap: 5px; box-shadow: 0 2px 6px rgba(220, 38, 38, 0.2);" title="<?php echo __('Delete this amenity'); ?>">' +
                        '<i class="fa fa-trash"></i> <?php echo __('Delete'); ?>' +
                    '</button>' +
                '</div>' +
                '<div class="row g-2">' +
                    '<div class="col-sm-6">' +
                        '<label style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 4px; display: block;"><?php echo __('Amenity Title'); ?> <span class="text-danger">*</span></label>' +
                        '<input type="text" name="data[RealEstateThemeSetting][amenities_items][' + nextIndex + '][title]" class="form-control input-sm" value="" placeholder="e.g. Cinema / Theater" required style="border-radius: 6px; border: 1px solid #cbd5e1; height: 36px;">' +
                    '</div>' +
                    '<div class="col-sm-6">' +
                        '<label style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 4px; display: block;"><?php echo __('Badge / Category'); ?></label>' +
                        '<input type="text" name="data[RealEstateThemeSetting][amenities_items][' + nextIndex + '][badge]" class="form-control input-sm" value="" placeholder="e.g. Entertainment" style="border-radius: 6px; border: 1px solid #cbd5e1; height: 36px;">' +
                    '</div>' +
                    '<div class="col-sm-6" style="margin-top: 10px;">' +
                        '<label style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 4px; display: block;"><?php echo __('Icon Class'); ?></label>' +
                        '<div class="input-group input-group-sm" style="border-radius: 6px; overflow: hidden;">' +
                            '<span class="input-group-addon" style="background: #f1f5f9; border: 1px solid #cbd5e1; border-right: 0; color: #d11242; font-size: 14px;"><i class="fa fa-star"></i></span>' +
                            '<input type="text" name="data[RealEstateThemeSetting][amenities_items][' + nextIndex + '][icon]" class="form-control input-sm icon-input" value="fa-star" placeholder="fa-film" style="border: 1px solid #cbd5e1; height: 36px;">' +
                        '</div>' +
                    '</div>' +
                    '<div class="col-sm-6" style="margin-top: 10px; display: flex; align-items: flex-end;">' +
                        '<label style="font-size: 12px; font-weight: 700; color: #334155; margin-bottom: 8px; cursor: pointer; display: flex; align-items: center; gap: 6px;">' +
                            '<input type="checkbox" name="data[RealEstateThemeSetting][amenities_items][' + nextIndex + '][status]" value="1" checked style="width: 16px; height: 16px; accent-color: #d11242;">' +
                            ' <?php echo __('Active / Visible'); ?>' +
                        '</label>' +
                    '</div>' +
                    '<div class="col-sm-12" style="margin-top: 10px;">' +
                        '<label style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 4px; display: block;"><?php echo __('Short Description'); ?></label>' +
                        '<textarea name="data[RealEstateThemeSetting][amenities_items][' + nextIndex + '][description]" class="form-control input-sm" rows="2" placeholder="Brief details about facility..." style="border-radius: 6px; border: 1px solid #cbd5e1;"></textarea>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>';
        
        var $el = $(html);
        $('#amenitiesContainer').append($el);
        $el.fadeIn(250);
    });

        // Section Reordering Engine
    function updateSectionsOrder() {
        var order = [];
        $('#sectionSortableList .section-order-item').each(function(index){
            var key = $(this).data('sec-key');
            if (key) {
                order.push(key);
                $(this).find('.sec-pos-badge').text('#' + (index + 1));
            }
        });
        $('#sectionsOrderInput').val(order.join(','));
    }

    // Move item Up in Section Manager
    $(document).on('click', '.btn-move-sec-up', function(e){
        e.preventDefault();
        var item = $(this).closest('.section-order-item');
        var prev = item.prev('.section-order-item');
        if (prev.length) {
            item.insertBefore(prev);
            updateSectionsOrder();
            item.css('background', '#e0f2fe');
            setTimeout(function(){ item.css('background', '#f8fafc'); }, 400);
        }
    });

    // Move item Down in Section Manager
    $(document).on('click', '.btn-move-sec-down', function(e){
        e.preventDefault();
        var item = $(this).closest('.section-order-item');
        var next = item.next('.section-order-item');
        if (next.length) {
            item.insertAfter(next);
            updateSectionsOrder();
            item.css('background', '#e0f2fe');
            setTimeout(function(){ item.css('background', '#f8fafc'); }, 400);
        }
    });

    // Move card Up from card header
    $(document).on('click', '.btn-move-card-up', function(e){
        e.preventDefault();
        var card = $(this).closest('.tc-card[data-section-key]');
        var key = card.data('section-key');
        if (key) {
            var listItem = $('#sectionSortableList .section-order-item[data-sec-key="' + key + '"]');
            var prev = listItem.prev('.section-order-item');
            if (prev.length) {
                listItem.insertBefore(prev);
                updateSectionsOrder();
                var prevCard = card.prev('.tc-card[data-section-key]');
                if (prevCard.length) {
                    card.insertBefore(prevCard);
                }
                card.css('box-shadow', '0 0 0 3px #3b82f6');
                setTimeout(function(){ card.css('box-shadow', ''); }, 800);
            }
        }
    });

    // Move card Down from card header
    $(document).on('click', '.btn-move-card-down', function(e){
        e.preventDefault();
        var card = $(this).closest('.tc-card[data-section-key]');
        var key = card.data('section-key');
        if (key) {
            var listItem = $('#sectionSortableList .section-order-item[data-sec-key="' + key + '"]');
            var next = listItem.next('.section-order-item');
            if (next.length) {
                listItem.insertAfter(next);
                updateSectionsOrder();
                var nextCard = card.next('.tc-card[data-section-key]');
                if (nextCard.length) {
                    card.insertAfter(nextCard);
                }
                card.css('box-shadow', '0 0 0 3px #3b82f6');
                setTimeout(function(){ card.css('box-shadow', ''); }, 800);
            }
        }
    });

    // Emoji Palette Inserter
    $(document).on('click', '.btn-insert-emoji', function(e){
        e.preventDefault();
        var emoji = $(this).data('emoji');
        var textarea = document.getElementById('tickerTextInput');
        if (textarea) {
            var start = textarea.selectionStart;
            var end = textarea.selectionEnd;
            var text = textarea.value;
            textarea.value = text.substring(0, start) + ' ' + emoji + ' ' + text.substring(end);
            textarea.focus();
            textarea.selectionStart = textarea.selectionEnd = start + emoji.length + 2;
        }
    });

    // Color Pickers Sync
    $('input[type="color"]').on('input change', function(){
        $(this).next('input[type="text"]').val($(this).val());
    });
});
</script>
