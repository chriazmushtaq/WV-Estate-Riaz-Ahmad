<?php
$adminUserName = !empty($UserArr['User']['name']) ? (string)$UserArr['User']['name'] : __('User');
$loggedInUserLevelName = !empty($UserArr['Ugroup']['name']) ? (string)$UserArr['Ugroup']['name'] : '';
$adminUserInitial = strtoupper(substr(trim($adminUserName),0,1));
if($adminUserInitial === '')
{
    $adminUserInitial = 'U';
}
$adminUserPhoto = '';
if(!empty($UserArr['User']['photo']))
{
    $adminUserPhoto = $this->Html->url('/img/user_thumb/'.$UserArr['User']['photo']);
}
?>
<nav class="navbar navbar-toggleable-md wv-admin-topbar" role="navigation">
    <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav">
        <span><i class="fa fa-bars"></i></span>
    </button>

    <button class="navbar-toggler navbar-toggler-left" type="button" id="toggle-sidebar" aria-label="<?php echo h(__('Toggle sidebar'));?>">
        <span><i class="fa fa-align-justify"></i></span>
    </button>

<?php
$topbarAppVersionNumber = Configure::read('App.versionNumber');
if (empty($topbarAppVersionNumber)) {
    $topbarRawVersion = Configure::read('App.version');
    $topbarAppVersionNumber = !empty($topbarRawVersion) ? ltrim($topbarRawVersion, 'vV') : '9.5';
}
$topbarDisplayVersion = 'V ' . $topbarAppVersionNumber;
$topbarReleaseName = Configure::read('App.releaseName') ? Configure::read('App.releaseName') : ('Version ' . $topbarAppVersionNumber);
?>
    <a class="navbar-brand logo" href="<?php echo $this->Html->url('/');?>" target="_blank" style="display:inline-flex; align-items:center; gap:8px;">
        <span><?php echo h($siteName);?></span>
        <span class="badge" style="font-size: 10px; font-weight: 700; background: rgba(255,255,255,0.18); color: #ffffff; border: 1px solid rgba(255,255,255,0.28); border-radius: 999px; padding: 2px 7px; line-height: 1;" title="<?php echo h($topbarReleaseName);?>"><?php echo h($topbarDisplayVersion);?></span>
    </a>

    <div class="wv-admin-header-center">
        <?php echo $this->element('breadcrumb');?>
    </div>

    <div class="collapse navbar-collapse" id="navbarNav">
        <button class="sidebar-toggle btn btn-flat" id="toggle-sidebar-desktop" aria-label="<?php echo h(__('Collapse sidebar'));?>">
            <span><i class="fa fa-align-justify"></i></span>
        </button>
        <ul class="navbar-nav ml-auto wv-admin-topbar-actions">
            <li class="nav-item wv-admin-search-item">
                <div class="wv-admin-quick-search">
                    <i class="fa fa-search"></i>
                    <input type="text" id="adminTopbarSearch" placeholder="<?php echo h(__('Quick page search'));?>" autocomplete="off">
                    <button type="button" class="wv-admin-quick-search-btn" id="adminTopbarSearchBtn" aria-label="<?php echo h(__('Search this page'));?>" title="<?php echo h(__('Search this page'));?>">
                        <i class="fa fa-arrow-right"></i>
                    </button>
                </div>
            </li>
            <li class="nav-item">
                <button type="button" class="wv-admin-icon-btn" data-admin-fullscreen title="<?php echo h(__('Fullscreen'));?>" aria-label="<?php echo h(__('Fullscreen'));?>">
                    <i class="fa fa-arrows-alt"></i>
                </button>
            </li>
            <li class="nav-item dropdown wv-admin-theme-dropdown">
                <a class="nav-link dropdown-toggle wv-admin-icon-btn" href="javascript:void(0);" id="adminTopbarThemeDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="<?php echo h(__('Admin Appearance Themes'));?>" aria-label="<?php echo h(__('Admin Appearance Themes'));?>">
                    <i class="fa fa-paint-brush"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right wv-admin-theme-menu" aria-labelledby="adminTopbarThemeDropdown">
                    <div class="dropdown-item-text wv-admin-theme-menu-title" style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #edf2f7; padding-bottom: 6px; margin-bottom: 10px;">
                        <span><i class="fa fa-paint-brush text-primary"></i> <?php echo __('Select Theme');?></span>
                        <span class="badge badge-default" style="font-size: 9.5px; background: #e2e8f0; color: #334155; font-weight: 700;"><?php echo __('Appearance'); ?></span>
                    </div>
                    <?php echo $this->element('theme_selector', array(
                        'adminThemeClass' => !empty($adminThemeClass) ? $adminThemeClass : 'theme-light',
                        'adminThemeOptions' => !empty($adminThemeOptions) ? $adminThemeOptions : array(),
                        'compact' => true
                    ));?>
                    <div class="dropdown-divider" style="margin: 12px 0;"></div>
                    <a href="<?php echo $this->Html->url('/admin/Themesettings'); ?>" class="dropdown-item" style="display: flex; align-items: center; justify-content: center; gap: 8px; font-weight: 700; font-size: 12.5px; color: #1e293b; padding: 8px 12px; border-radius: 8px; background: #f8fafc; border: 1px solid #e2e8f0; text-align: center;">
                        <i class="fa fa-cog text-primary"></i> <?php echo __('Theme Settings'); ?>
                    </a>
                </div>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle dropdown-has-after" href="javascript:void(0);" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false" title="<?php echo h($adminUserName);?>">
                    <span class="wv-admin-profile-badge <?php echo $adminUserPhoto!=='' ? 'has-photo' : '';?>" <?php if($adminUserPhoto!==''){ ?>style="background-image:url('<?php echo h($adminUserPhoto);?>');"<?php } ?>><?php echo h($adminUserInitial);?></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                    <?php if($loggedInUserLevelName!==''){ ?>
                        <div class="dropdown-item-text"><strong><?php echo __('User Level');?>:</strong> <?php echo h($loggedInUserLevelName);?></div>
                    <?php } ?>
                    <div class="dropdown-item-text"><strong><?php echo __('Signed In');?>:</strong> <?php echo h($adminUserName);?></div>
                    <?php echo $this->Html->link('<i class="fa fa-pencil"></i>&nbsp;'.__('Profile'),array('controller' => 'Users','action' => 'myProfile'),array('escape' => false,'class'=>'dropdown-item'));?>
                    <?php echo $this->Html->link('<i class="fa fa-cog"></i>&nbsp;'.__('Password'),array('controller' => 'Users','action' => 'changePass'),array('escape' => false,'class'=>'dropdown-item'));?>
                    <?php echo $this->Html->link('<i class="fa fa-paint-brush"></i>&nbsp;'.__('Appearance'),array('controller' => 'Themesettings','action' => 'index'),array('escape' => false,'class'=>'dropdown-item'));?>
                    <?php
                        $adminImpMeta = (array)$this->Session->read('Impersonation.admin');
                        if(!empty($adminImpMeta['original_user']['User']['id']))
                        {
                            echo $this->Form->postLink('<i class="fa fa-user-secret"></i>&nbsp;'.__('Stop Impersonation'),array('controller' => 'Users','action' => 'stopimpersonation'),array('escape' => false,'class'=>'dropdown-item'),__('Stop impersonation and return to Super Admin?'));
                        }
                    ?>
                    <?php echo $this->Html->link('<i class="fa fa-power-off"></i>&nbsp;'.__('Logout'),array('controller' => 'Users','action' => 'logout'),array('escape' => false,'class'=>'dropdown-item'));?>
                </div>
            </li>
        </ul>
    </div>
</nav>

