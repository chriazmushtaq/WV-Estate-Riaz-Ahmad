<?php
$adminSidebarSearchId = !empty($searchInputId) ? (string)$searchInputId : 'adminSidebarSearch';
$adminAppVersionNumber = Configure::read('App.versionNumber');
if (empty($adminAppVersionNumber)) {
    $rawVersion = Configure::read('App.version');
    $adminAppVersionNumber = !empty($rawVersion) ? ltrim($rawVersion, 'vV') : '9.5';
}
$adminDisplayVersion = 'V ' . $adminAppVersionNumber;
$adminReleaseName = Configure::read('App.releaseName') ? Configure::read('App.releaseName') : ('Version ' . $adminAppVersionNumber);
?>
<div id="sidebar-main" class="sidebar sidebar-default">
    <div class="sidebar-content">
        <div class="wv-admin-sidebar-shell">
            <div class="wv-admin-sidebar-brand">
                <div class="wv-admin-sidebar-brand-logo">
                    <i class="fa fa-building-o"></i>
                </div>
                <div class="wv-admin-sidebar-brand-copy">
                    <strong><?php echo h($siteName);?></strong>
                    <span class="wv-admin-sidebar-meta"><a href="<?php echo $this->Html->url('/admin/Systemupdates'); ?>" title="<?php echo h($adminReleaseName); ?>" style="text-decoration:none;"><em><?php echo h($adminDisplayVersion);?></em></a></span>
                </div>
            </div>
            <div class="wv-admin-sidebar-search-wrap">
                <label class="sr-only" for="<?php echo h($adminSidebarSearchId);?>"><?php echo __('Search menu');?></label>
                <span class="wv-admin-sidebar-search-icon"><i class="fa fa-search"></i></span>
                <input type="text" id="<?php echo h($adminSidebarSearchId);?>" class="wv-admin-sidebar-search" placeholder="<?php echo h(__('Search menu'));?>" autocomplete="off">
            </div>
            <div class="wv-admin-sidebar-menu" data-admin-menu-search>
                <?php echo $this->MenuBuilder->build('slide-out');?>
            </div>
        </div>
    </div>
</div>
