<?php
$adminThemeClass = !empty($adminThemeClass) ? (string)$adminThemeClass : 'theme-light';
$adminThemeOptions = !empty($adminThemeOptions) && is_array($adminThemeOptions) ? $adminThemeOptions : array(
    'theme-light' => __('Professional Light'),
    'theme-dark' => __('Professional Dark'),
    'theme-gold' => __('Royal Gold'),
    'theme-green' => __('Emerald Green')
);
$compact = !empty($compact);
?>
<div class="wv-admin-theme-selector<?php echo $compact ? ' compact' : '';?>" data-admin-theme-selector>
    <?php foreach($adminThemeOptions as $themeKey => $themeLabel){ ?>
        <button type="button" class="wv-admin-theme-chip<?php echo $adminThemeClass === $themeKey ? ' active' : '';?>" data-theme-value="<?php echo h($themeKey);?>" title="<?php echo h($themeLabel);?>">
            <span class="wv-admin-theme-dot theme-dot-<?php echo h(str_replace('theme-', '', $themeKey));?>"></span>
            <span class="wv-admin-theme-chip-label"><?php echo h($themeLabel);?></span>
            <span class="wv-admin-theme-check"><i class="fa fa-check"></i></span>
        </button>
    <?php } ?>
</div>
