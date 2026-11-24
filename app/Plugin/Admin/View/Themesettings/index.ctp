<style type="text/css">
.theme-page-shell{
    display:grid;
    gap:18px;
    grid-template-columns:380px minmax(0,1fr);
    align-items:start;
}
.theme-sidebar-stack,
.theme-content-stack{
    display:grid;
    gap:18px;
}
.theme-sticky-panel{
    position:sticky;
    top:88px;
}
.theme-scope-grid{
    display:grid;
    gap:18px;
    grid-template-columns:repeat(2,minmax(0,1fr));
}
.theme-scope-card{
    background:#fff;
    border:1px solid #dfe8f3;
    border-radius:14px;
    box-shadow:0 8px 18px rgba(15,23,42,.07);
    overflow:hidden;
}
.theme-scope-card h3{
    background:linear-gradient(135deg,#07152a,#102746);
    color:#ffffff!important;
    font-size:17px;
    font-weight:900;
    margin:0;
    padding:13px 16px;
    letter-spacing:.2px;
    text-shadow:0 1px 1px rgba(0,0,0,.22);
}
.theme-scope-card h3 *{
    color:#ffffff!important;
}
.theme-scope-body{
    padding:16px;
    color:#14233b!important;
}
.theme-section-title{
    color:#07152a!important;
    background:#eef5ff;
    border-left:4px solid #ff0a46;
    border-radius:8px;
    font-size:13px;
    font-weight:900;
    margin:4px 0 12px;
    padding:8px 10px;
    text-transform:uppercase;
}
.theme-field-grid{
    display:grid;
    gap:12px;
    grid-template-columns:repeat(2,minmax(0,1fr));
}
.theme-field-grid.theme-field-grid-compact{
    grid-template-columns:1fr;
}
.theme-field{
    min-width:0;
}
.theme-field label{
    display:block;
    color:#20304a!important;
    font-size:12px;
    font-weight:900;
    margin-bottom:5px;
}
.theme-color-input{
    display:flex;
    gap:8px;
}
.theme-color-input input[type="color"]{
    min-height:38px;
    padding:3px;
    width:48px;
}
.theme-color-input input[type="text"]{
    flex:1;
}
.theme-preview{
    background:#f8fbff;
    border:1px dashed #cfdbe9;
    border-radius:12px;
    margin-top:14px;
    padding:12px;
}
.theme-preview-card{
    border-radius:12px;
    overflow:hidden;
}
.theme-preview-head{
    color:#fff;
    font-weight:900;
    padding:12px;
}
.theme-preview-body{
    padding:12px;
}
.theme-preview-btn{
    border:0;
    border-radius:8px;
    color:#fff;
    font-weight:900;
    padding:8px 12px;
}
.theme-preset-grid{
    display:grid;
    gap:12px;
    grid-template-columns:repeat(2,minmax(0,1fr));
}
.theme-preset-card{
    background:#fff;
    border:1px solid #dfe8f3;
    border-radius:14px;
    box-shadow:0 8px 18px rgba(15,23,42,.07);
    overflow:hidden;
    padding:14px;
}
.theme-preset-card h4{
    color:#07152a!important;
    font-size:14px;
    font-weight:900;
    margin:0 0 4px;
}
.theme-preset-card p{
    color:#5b6b83;
    font-size:12px;
    line-height:1.45;
    margin:0 0 10px;
    min-height:34px;
}
.theme-preset-palette{
    display:flex;
    gap:8px;
}
.theme-preset-swatch{
    border:1px solid rgba(15,23,42,.08);
    border-radius:999px;
    display:inline-block;
    height:14px;
    width:14px;
}
.theme-actions-bar{
    align-items:center;
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    margin-top:8px;
}
.theme-intro-copy{
    color:#5b6b83;
    font-size:13px;
    line-height:1.6;
    margin:0;
}
.theme-config-toolbar{
    align-items:center;
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    justify-content:space-between;
}
.theme-config-toolbar .btn-group{
    display:flex;
    flex-wrap:wrap;
    gap:10px;
}
@media(max-width:1400px){
    .theme-page-shell{grid-template-columns:340px minmax(0,1fr);}
}
@media(max-width:1200px){
    .theme-page-shell{grid-template-columns:1fr;}
    .theme-sticky-panel{position:static;}
    .theme-scope-grid{grid-template-columns:1fr;}
}
@media(max-width:767px){
    .theme-field-grid{grid-template-columns:1fr;}
    .theme-preset-grid{grid-template-columns:1fr;}
    .theme-actions-bar,
    .theme-config-toolbar{flex-direction:column;align-items:flex-start;}
}
</style>

<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Theme Settings');?></h1>
        <span><?php echo __('Control frontend, backend and user dashboard theme colors and typography.');?></span>
    </div>
</div>

<div class="panel">
    <div class="panel-body">
        <?php echo $this->Session->flash();?>
        <?php echo $this->Form->create('ThemeSetting', array('url'=>array('controller'=>'Themesettings','action'=>'index'),'name'=>'post_req','id'=>'post_req','class'=>'form-horizontal'));?>

        <div class="theme-page-shell">
            <div class="theme-sidebar-stack">
                <div class="theme-sticky-panel">
                    <div class="theme-scope-card">
                        <h3><?php echo __('Admin Appearance Themes');?></h3>
                        <div class="theme-scope-body">
                            <div class="theme-config-toolbar" style="margin-bottom:14px;">
                                <div class="btn-group">
                                    <?php echo $this->Html->link('<span class="fa fa-cogs"></span>&nbsp;'.__('General Configuration'),array('controller'=>'Configurations','action'=>'index'),array('escape'=>false,'class'=>'btn btn-default'));?>
                                </div>
                            </div>

                            <div class="theme-section-title"><?php echo __('Default Backend Theme');?></div>
                            <div class="theme-field-grid theme-field-grid-compact">
                                <div class="theme-field">
                                    <label><?php echo __('Apply by default for admins');?></label>
                                    <?php echo $this->Form->select('admin_default_theme',$adminThemeOptions,array('empty'=>false,'label'=>false,'div'=>false,'class'=>'form-control'));?>
                                </div>
                            </div>

                            <div class="theme-section-title" style="margin-top:16px;"><?php echo __('Theme Presets');?></div>
                            <div class="theme-preset-grid">
                                <?php foreach($adminThemes as $themeKey => $themeRow){ ?>
                                    <div class="theme-preset-card">
                                        <h4><?php echo h($themeRow['label']);?></h4>
                                        <p><?php echo h($themeRow['description']);?></p>
                                        <div class="theme-preset-palette">
                                            <span class="theme-preset-swatch" style="background:<?php echo h($themeRow['colors']['--admin-theme-primary']);?>;"></span>
                                            <span class="theme-preset-swatch" style="background:<?php echo h($themeRow['colors']['--admin-theme-accent']);?>;"></span>
                                            <span class="theme-preset-swatch" style="background:<?php echo h($themeRow['colors']['--admin-theme-surface']);?>;"></span>
                                            <span class="theme-preset-swatch" style="background:<?php echo h($themeRow['colors']['--admin-theme-bg']);?>;"></span>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>

                            <div class="theme-section-title" style="margin-top:16px;"><?php echo __('Quick Preview');?></div>
                            <?php echo $this->element('theme_selector', array('adminThemeClass' => isset($this->request->data['ThemeSetting']['admin_default_theme']) ? $this->request->data['ThemeSetting']['admin_default_theme'] : 'theme-light', 'adminThemeOptions' => $adminThemeOptions, 'compact' => false));?>

                            <div class="theme-actions-bar">
                                <button type="submit" class="btn btn-success"><span class="fa fa-save"></span>&nbsp;<?php echo __('Save & Apply Theme');?></button>
                                <button type="submit" name="reset_theme" value="1" class="btn btn-warning" onclick="return confirm('<?php echo h(__('Reset all theme settings to default?'));?>');"><span class="fa fa-refresh"></span>&nbsp;<?php echo __('Reset Default');?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="theme-content-stack">
                <div class="theme-scope-card">
                    <h3><?php echo __('Theme Configuration Data');?></h3>
                    <div class="theme-scope-body">
                    </div>
                </div>

                <div class="theme-scope-grid">
                    <?php foreach($scopeLabels as $scope => $scopeLabel){ ?>
                    <div class="theme-scope-card">
                        <h3><?php echo h($scopeLabel);?></h3>
                        <div class="theme-scope-body">
                            <div class="theme-section-title"><?php echo __('Colors');?></div>
                            <div class="theme-field-grid">
                                <?php foreach($colorFields as $field => $label){
                                    $key = $scope.'_'.$field;
                                    $value = isset($this->request->data['ThemeSetting'][$key]) ? $this->request->data['ThemeSetting'][$key] : '#000000';
                                ?>
                                <div class="theme-field">
                                    <label><?php echo h($label);?></label>
                                    <div class="theme-color-input">
                                        <input type="color" value="<?php echo h($value);?>" data-theme-color-target="<?php echo h($key);?>">
                                        <?php echo $this->Form->input($key,array('type'=>'text','label'=>false,'div'=>false,'class'=>'form-control theme-color-text','id'=>'ThemeSetting'.str_replace(' ', '', ucwords(str_replace('_',' ', $key))),'value'=>$value));?>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>

                            <hr>

                            <div class="theme-section-title"><?php echo __('Fonts & Sizes');?></div>
                            <div class="theme-field-grid">
                                <div class="theme-field">
                                    <label><?php echo __('Font Family');?></label>
                                    <?php echo $this->Form->select($scope.'_font',$fontOptions,array('empty'=>false,'label'=>false,'div'=>false,'class'=>'form-control'));?>
                                </div>
                                <?php foreach($sizeFields as $field => $label){
                                    $key = $scope.'_'.$field;
                                ?>
                                <div class="theme-field">
                                    <label><?php echo h($label);?> (px)</label>
                                    <?php echo $this->Form->input($key,array('type'=>'number','step'=>'0.5','min'=>($field==='radius' ? 4 : 9),'max'=>($field==='radius' ? 28 : 42),'label'=>false,'div'=>false,'class'=>'form-control'));?>
                                </div>
                                <?php } ?>
                            </div>

                            <?php
                                $primary = isset($this->request->data['ThemeSetting'][$scope.'_primary']) ? $this->request->data['ThemeSetting'][$scope.'_primary'] : '#07152a';
                                $secondary = isset($this->request->data['ThemeSetting'][$scope.'_secondary']) ? $this->request->data['ThemeSetting'][$scope.'_secondary'] : '#102746';
                                $accent = isset($this->request->data['ThemeSetting'][$scope.'_accent']) ? $this->request->data['ThemeSetting'][$scope.'_accent'] : '#ff0a46';
                                $surface = isset($this->request->data['ThemeSetting'][$scope.'_surface']) ? $this->request->data['ThemeSetting'][$scope.'_surface'] : '#ffffff';
                                $text = isset($this->request->data['ThemeSetting'][$scope.'_text']) ? $this->request->data['ThemeSetting'][$scope.'_text'] : '#07152a';
                            ?>
                            <div class="theme-preview">
                                <div class="theme-preview-card" style="background:<?php echo h($surface);?>;color:<?php echo h($text);?>;">
                                    <div class="theme-preview-head" style="background:linear-gradient(135deg,<?php echo h($primary);?>,<?php echo h($secondary);?>);"><?php echo __('Live Preview');?></div>
                                    <div class="theme-preview-body">
                                        <strong><?php echo __('Readable card title');?></strong>
                                        <p><?php echo __('Sample text and table content will follow these theme colors and font sizes.');?></p>
                                        <button type="button" class="theme-preview-btn" style="background:<?php echo h($accent);?>;"><?php echo __('Primary Button');?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <?php echo $this->Form->end();?>
    </div>
</div>

<script type="text/javascript">
jQuery(function($){
    $('input[type="color"][data-theme-color-target]').on('input change', function(){
        var key = $(this).attr('data-theme-color-target');
        $('#ThemeSetting' + key.replace(/(^|_)([a-z])/g, function(m, sep, letter){ return letter.toUpperCase(); })).val($(this).val());
    });
    $('.theme-color-text').on('input change', function(){
        var value = $.trim($(this).val());
        if(/^#[0-9a-fA-F]{6}$/.test(value) || /^#[0-9a-fA-F]{3}$/.test(value)){
            $(this).closest('.theme-color-input').find('input[type="color"]').val(value);
        }
    });
});
</script>
