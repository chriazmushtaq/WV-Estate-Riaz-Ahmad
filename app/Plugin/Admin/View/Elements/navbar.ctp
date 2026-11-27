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
    <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-label="<?php echo h(__('Toggle navigation'));?>">
        <span><i class="fa fa-bars"></i></span>
    </button>

    <button class="navbar-toggler navbar-toggler-left" type="button" id="toggle-sidebar" onclick="window.toggleAdminMobileSidebar && window.toggleAdminMobileSidebar(event); return false;" aria-label="<?php echo h(__('Toggle sidebar'));?>" title="<?php echo h(__('Toggle sidebar'));?>">
        <span><i class="fa fa-bars"></i></span>
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
    <a class="navbar-brand logo" href="<?php echo $this->Html->url('/');?>" target="_blank">
        <span><?php echo h($siteName);?></span>
        <span class="badge wv-topbar-version-pill" title="<?php echo h($topbarReleaseName);?>"><?php echo h($topbarDisplayVersion);?></span>
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
            <li class="nav-item">
                <a class="wv-admin-icon-btn" href="<?php echo $this->Html->url(array('plugin' => 'admin', 'controller' => 'configurations', 'action' => 'clearcache')); ?>" title="<?php echo h(__('Clear System Cache'));?>" aria-label="<?php echo h(__('Clear System Cache'));?>" onclick="return confirm('<?php echo __('Clear all system model, view, and persistent caches?'); ?>');">
                    <i class="fa fa-trash-o text-danger"></i>
                </a>
            </li>
            <li class="nav-item dropdown wv-admin-theme-dropdown">
                <a class="nav-link dropdown-toggle wv-admin-icon-btn" href="javascript:void(0);" id="adminTopbarThemeDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="<?php echo h(__('Admin Appearance Themes'));?>" aria-label="<?php echo h(__('Admin Appearance Themes'));?>">
                    <i class="fa fa-paint-brush"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right wv-admin-theme-menu" aria-labelledby="adminTopbarThemeDropdown">
                    <div class="wv-theme-menu-header">
                        <div class="wv-theme-header-left">
                            <span class="wv-theme-header-icon"><i class="fa fa-paint-brush"></i></span>
                            <span class="wv-theme-header-title"><?php echo __('Appearance & Theme');?></span>
                        </div>
                        <span class="wv-theme-header-badge"><?php echo __('Presets'); ?></span>
                    </div>
                    <?php echo $this->element('theme_selector', array(
                        'adminThemeClass' => !empty($adminThemeClass) ? $adminThemeClass : 'theme-light',
                        'adminThemeOptions' => !empty($adminThemeOptions) ? $adminThemeOptions : array(),
                        'compact' => true
                    ));?>
                    <div class="wv-theme-menu-divider"></div>
                    <a href="<?php echo $this->Html->url('/admin/Themesettings'); ?>" class="wv-theme-menu-settings-btn">
                        <i class="fa fa-sliders"></i>
                        <span><?php echo __('Theme & Style Settings'); ?></span>
                        <i class="fa fa-angle-right wv-theme-arrow"></i>
                    </a>
                </div>
            </li>
            <li class="nav-item dropdown wv-admin-notif-dropdown" id="wvAdminNotifDropdownWrap">
                <a class="nav-link dropdown-toggle wv-admin-icon-btn notif-bell-btn" href="javascript:void(0);" id="wvAdminNotifBellBtn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="<?php echo h(__('Notifications'));?>" aria-label="<?php echo h(__('Notifications'));?>">
                    <i class="fa fa-bell-o" id="notifBellIcon"></i>
                    <span class="notif-unread-badge" id="notifUnreadBadge" style="display:none;">0</span>
                </a>
                <div class="dropdown-menu dropdown-menu-right wv-admin-notif-menu" aria-labelledby="wvAdminNotifBellBtn">
                    <div class="wv-notif-menu-header">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="font-weight:700; font-size:13px; color:#0f172a;"><i class="fa fa-bell text-primary"></i> <?php echo __('Notifications'); ?></span>
                            <span class="badge badge-primary" id="notifHeaderCount" style="display:none; font-size:10px; border-radius:10px; padding:2px 7px;">0 new</span>
                        </div>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <?php if(!empty($isSuperAdminSession) || (!empty($UserArr['User']['ugroup_id']) && (int)$UserArr['User']['ugroup_id'] === 1)){ ?>
                                <a href="<?php echo $this->Html->url('/admin/Notifications/broadcast'); ?>" title="<?php echo __('Send Custom Broadcast'); ?>" style="color:#64748b; font-size:13px;"><i class="fa fa-bullhorn"></i></a>
                                <a href="<?php echo $this->Html->url('/admin/Notifications/settings'); ?>" title="<?php echo __('Notification Settings'); ?>" style="color:#64748b; font-size:13px;"><i class="fa fa-sliders"></i></a>
                            <?php } ?>
                            <a href="javascript:void(0);" id="notifMarkAllReadBtn" class="wv-notif-mark-all"><?php echo __('Mark all read'); ?></a>
                        </div>
                    </div>
                    <div class="wv-notif-menu-list" id="notifMenuList">
                        <div class="wv-notif-empty" style="padding: 24px 16px; text-align: center; color: #94a3b8; font-size: 12.5px;">
                            <i class="fa fa-bell-slash-o" style="font-size: 26px; opacity: 0.5; margin-bottom: 6px; display: block;"></i>
                            <?php echo __('Loading notifications...'); ?>
                        </div>
                    </div>
                    <div class="wv-notif-menu-footer">
                        <a href="<?php echo $this->Html->url('/admin/Notifications/index'); ?>" class="wv-notif-view-all">
                            <?php echo __('View All Notifications'); ?> <i class="fa fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </li>
            <li class="nav-item dropdown wv-admin-user-dropdown">
                <a class="nav-link dropdown-toggle wv-admin-user-trigger dropdown-has-after" href="javascript:void(0);" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false" title="<?php echo h($adminUserName);?>">
                    <div class="wv-user-avatar-wrap">
                        <span class="wv-admin-profile-badge <?php echo $adminUserPhoto!=='' ? 'has-photo' : '';?>" <?php if($adminUserPhoto!==''){ ?>style="background-image:url('<?php echo h($adminUserPhoto);?>');"<?php } ?>><?php echo h($adminUserInitial);?></span>
                        <span class="wv-user-online-dot" title="<?php echo __('Online'); ?>"></span>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-right wv-admin-user-menu" aria-labelledby="navbarDropdownMenuLink">
                    <!-- USER DOSSIER HEADER -->
                    <div class="wv-user-menu-header">
                        <div class="wv-user-menu-avatar">
                            <span class="wv-admin-profile-badge sm <?php echo $adminUserPhoto!=='' ? 'has-photo' : '';?>" <?php if($adminUserPhoto!==''){ ?>style="background-image:url('<?php echo h($adminUserPhoto);?>');"<?php } ?>><?php echo h($adminUserInitial);?></span>
                        </div>
                        <div class="wv-user-menu-details">
                            <div class="wv-user-menu-name" title="<?php echo h($adminUserName); ?>"><?php echo h($adminUserName); ?></div>
                            <div class="wv-user-menu-meta">
                                <?php if($loggedInUserLevelName!==''){ ?>
                                    <span class="wv-user-role-pill"><?php echo h($loggedInUserLevelName); ?></span>
                                <?php } ?>
                                <span class="wv-user-status-text"><span class="dot-green"></span> <?php echo __('Active Session'); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="wv-user-menu-divider"></div>

                    <!-- NAVIGATION ITEMS -->
                    <div class="wv-user-menu-nav">
                        <a href="<?php echo $this->Html->url(array('controller' => 'Users', 'action' => 'myProfile')); ?>" class="wv-user-nav-link">
                            <span class="nav-icon-box"><i class="fa fa-user-circle-o"></i></span>
                            <span class="nav-label-box">
                                <span class="nav-title"><?php echo __('My Profile'); ?></span>
                                <span class="nav-sub"><?php echo __('Account settings & info'); ?></span>
                            </span>
                            <i class="fa fa-angle-right nav-arrow"></i>
                        </a>

                        <a href="<?php echo $this->Html->url(array('controller' => 'Users', 'action' => 'changePass')); ?>" class="wv-user-nav-link">
                            <span class="nav-icon-box"><i class="fa fa-lock"></i></span>
                            <span class="nav-label-box">
                                <span class="nav-title"><?php echo __('Change Password'); ?></span>
                                <span class="nav-sub"><?php echo __('Update security credentials'); ?></span>
                            </span>
                            <i class="fa fa-angle-right nav-arrow"></i>
                        </a>

                        <a href="<?php echo $this->Html->url(array('controller' => 'Themesettings', 'action' => 'index')); ?>" class="wv-user-nav-link">
                            <span class="nav-icon-box"><i class="fa fa-paint-brush"></i></span>
                            <span class="nav-label-box">
                                <span class="nav-title"><?php echo __('Theme Settings'); ?></span>
                                <span class="nav-sub"><?php echo __('Colors, branding & layout'); ?></span>
                            </span>
                            <i class="fa fa-angle-right nav-arrow"></i>
                        </a>

                        <a href="<?php echo $this->Html->url(array('controller' => 'Configurations', 'action' => 'clearcache')); ?>" class="wv-user-nav-link" onclick="return confirm('<?php echo __('Clear all system model, view, and persistent caches?'); ?>');">
                            <span class="nav-icon-box cache"><i class="fa fa-trash-o text-danger"></i></span>
                            <span class="nav-label-box">
                                <span class="nav-title text-danger"><?php echo __('Clear System Cache'); ?></span>
                                <span class="nav-sub"><?php echo __('Purge application caches'); ?></span>
                            </span>
                            <i class="fa fa-angle-right nav-arrow"></i>
                        </a>

                        <?php
                            $adminImpMeta = (array)$this->Session->read('Impersonation.admin');
                            if(!empty($adminImpMeta['original_user']['User']['id']))
                            {
                                echo $this->Form->postLink(
                                    '<span class="nav-icon-box"><i class="fa fa-user-secret"></i></span><span class="nav-label-box"><span class="nav-title">'.__('Stop Impersonation').'</span><span class="nav-sub">'.__('Return to Super Admin').'</span></span><i class="fa fa-angle-right nav-arrow"></i>',
                                    array('controller' => 'Users', 'action' => 'stopimpersonation'),
                                    array('escape' => false, 'class' => 'wv-user-nav-link impersonate'),
                                    __('Stop impersonation and return to Super Admin?')
                                );
                            }
                        ?>
                    </div>

                    <div class="wv-user-menu-divider"></div>

                    <!-- LOGOUT ACTION -->
                    <div class="wv-user-menu-footer">
                        <a href="<?php echo $this->Html->url(array('controller' => 'Users', 'action' => 'logout')); ?>" class="wv-user-logout-btn">
                            <span class="logout-icon-box"><i class="fa fa-power-off"></i></span>
                            <span class="logout-text"><?php echo __('Sign Out / Logout'); ?></span>
                            <i class="fa fa-sign-out logout-arrow"></i>
                        </a>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</nav>
<script>
(function(){
    window.toggleAdminMobileSidebar = function(e){
        if(e){
            if(typeof e.preventDefault === 'function') e.preventDefault();
            if(typeof e.stopPropagation === 'function') e.stopPropagation();
            if(typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();
        }
        var sidebar = document.getElementById('sidebar-main');
        if(!sidebar) return false;
        var backdrop = document.getElementById('admin-mobile-backdrop');
        if(!backdrop){
            backdrop = document.createElement('div');
            backdrop.id = 'admin-mobile-backdrop';
            backdrop.className = 'admin-sidebar-backdrop';
            backdrop.setAttribute('onclick', 'window.closeAdminMobileSidebar && window.closeAdminMobileSidebar(event); return false;');
            document.body.appendChild(backdrop);
        }
        var isOpen = document.body.classList.contains('admin-mobile-sidebar-open') ||
                     sidebar.classList.contains('is-mobile-open');
        if(isOpen && sidebar.getAttribute('data-mobile-opened') === '1'){
            window.closeAdminMobileSidebar(e);
        } else {
            document.body.classList.add('admin-mobile-sidebar-open');
            sidebar.classList.remove('sidebar-collapsed');
            sidebar.classList.add('is-mobile-open');
            sidebar.setAttribute('aria-hidden', 'false');
            sidebar.setAttribute('data-mobile-opened', '1');
            backdrop.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
        return false;
    };

    window.closeAdminMobileSidebar = function(e){
        if(e){
            if(typeof e.preventDefault === 'function') e.preventDefault();
            if(typeof e.stopPropagation === 'function') e.stopPropagation();
            if(typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();
        }
        var sidebar = document.getElementById('sidebar-main');
        var backdrop = document.getElementById('admin-mobile-backdrop');
        document.body.classList.remove('admin-mobile-sidebar-open');
        if(sidebar){
            sidebar.classList.remove('is-mobile-open');
            sidebar.setAttribute('aria-hidden', 'true');
            sidebar.removeAttribute('data-mobile-opened');
        }
        if(backdrop){
            backdrop.style.display = 'none';
        }
        document.body.style.overflow = '';
        return false;
    };
})();
</script>
